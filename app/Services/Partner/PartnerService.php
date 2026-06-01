<?php

namespace App\Services\Partner;

use App\Models\Partner;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PartnerService
{
    // ─── List with filters & pagination ────────────────────────────────────────

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Partner::query();

        if (!empty($filters['school_type'])) {
            $query->where('school_type', $filters['school_type']);
        }
        if (!empty($filters['ownership_status'])) {
            $query->where('ownership_status', $filters['ownership_status']);
        }
        if (!empty($filters['district'])) {
            $query->where('district', 'like', "%{$filters['district']}%");
        }
        if (!empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('school_name', 'like', "%{$search}%")
                  ->orWhere('npsn',        'like', "%{$search}%")
                  ->orWhere('district',    'like', "%{$search}%")
                  ->orWhere('city',        'like', "%{$search}%");
            });
        }

        return $query->orderBy('school_name')->paginate($perPage);
    }

    // ─── Find by ID ───────────────────────────────────────────────────────────

    public function findById(string $id): Partner
    {
        return Partner::findOrFail($id);
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function create(array $data): Partner
    {
        return Partner::create($data);
    }

    // ─── Update ──────────────────────────────────────────────────────────────

    public function update(string $id, array $data): Partner
    {
        $partner = $this->findById($id);
        $partner->update($data);
        return $partner->fresh();
    }

    // ─── Delete ──────────────────────────────────────────────────────────────

    public function delete(string $id): void
    {
        $partner = $this->findById($id);
        $partner->delete();
    }

    // ─── Summary Statistics ──────────────────────────────────────────────────

    public function getSummary(): array
    {
        return [
            'total_schools'         => Partner::count(),
            'total_public'          => Partner::where('ownership_status', 'public')->count(),
            'total_private'         => Partner::where('ownership_status', 'private')->count(),
            'total_sma'             => Partner::where('school_type', 'SMA')->count(),
            'total_smk'             => Partner::where('school_type', 'SMK')->count(),
            'total_portion_count'   => (int) Partner::sum('portion_count'),
        ];
    }

    // ─── CSV/Excel Import ────────────────────────────────────────────────────

    /**
     * Import partners from an uploaded CSV file.
     *
     * Single entry point — handles parsing + import in one call.
     * Controller stays thin; all file logic lives here.
     *
     * @param  string  $filePath  Absolute path to the uploaded file
     * @return array   Import result summary
     */
    public function importFromFile(string $filePath): array
    {
        $parseResult = $this->parseCsv($filePath);

        // Header validation failed — return debug-friendly error
        if (!empty($parseResult['error'])) {
            return [
                'success'          => false,
                'created'          => 0,
                'updated'          => 0,
                'skipped'          => 0,
                'errors'           => [$parseResult['error']],
                'total'            => 0,
                'missing_columns'  => $parseResult['missing_columns'] ?? [],
                'detected_columns' => $parseResult['detected_columns'] ?? [],
            ];
        }

        $rows = $parseResult['rows'];

        if (empty($rows)) {
            return [
                'success' => false,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors'  => ['File contains no valid data.'],
                'total'   => 0,
            ];
        }

        return $this->importFromRows($rows);
    }

    /**
     * Import partners from parsed rows.
     *
     * @param  array  $rows  Array of associative arrays with column mappings
     * @return array  Import result summary
     */
    public function importFromRows(array $rows): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 because row 1 is header

                // Validate required fields
                $schoolName = trim($row['school_name'] ?? '');
                if (empty($schoolName)) {
                    $errors[] = "Row {$rowNum}: School name is empty, skipped.";
                    $skipped++;
                    continue;
                }

                $schoolType = trim($row['school_type'] ?? '');
                if (empty($schoolType)) {
                    $errors[] = "Row {$rowNum}: School type is empty, skipped.";
                    $skipped++;
                    continue;
                }

                $npsn = trim($row['npsn'] ?? '');

                // Normalize ownership status from Indonesian input (CSV backward compat)
                $rawStatus = trim($row['ownership_status'] ?? 'public');
                $ownershipStatus = match (strtolower($rawStatus)) {
                    'negeri' => 'public',
                    'swasta' => 'private',
                    default  => in_array(strtolower($rawStatus), ['public', 'private'])
                                    ? strtolower($rawStatus)
                                    : 'public',
                };

                $rowData = [
                    'school_name'      => $schoolName,
                    'school_type'      => $schoolType,
                    'ownership_status' => $ownershipStatus,
                    'address'          => trim($row['address'] ?? ''),
                    'district'         => trim($row['district'] ?? ''),
                    'city'             => trim($row['city'] ?? ''),
                    'latitude'         => $this->parseCoordinate($row['latitude'] ?? null),
                    'longitude'        => $this->parseCoordinate($row['longitude'] ?? null),
                    'portion_count'    => (int) ($row['portion_count'] ?? 0),
                ];

                // Upsert by NPSN if available, otherwise create new
                if (!empty($npsn)) {
                    $existing = Partner::where('npsn', $npsn)->first();

                    if ($existing) {
                        $existing->update($rowData);
                        $updated++;
                        continue;
                    }
                }

                Partner::create(array_merge($rowData, ['npsn' => $npsn ?: null]));
                $created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
            'total'   => count($rows),
        ];
    }

    // ─── CSV Parser (private) ──────────────────────────────────────────────────

    /**
     * Header alias map: normalized alias → internal column name.
     *
     * Accepts both English and Indonesian headers for backward compatibility
     * with existing CSV templates users may already have.
     */
    private const HEADER_ALIASES = [
        // school_name (supports legacy Indonesian headers)
        'school name'     => 'school_name',
        'school_name'     => 'school_name',
        'nama sekolah'    => 'school_name',
        'nama_sekolah'    => 'school_name',
        // npsn
        'npsn'            => 'npsn',
        // school_type
        'school type'     => 'school_type',
        'school_type'     => 'school_type',
        'bentuk'          => 'school_type',
        'jenis'           => 'school_type',
        // ownership_status
        'ownership status'=> 'ownership_status',
        'ownership_status'=> 'ownership_status',
        'status'          => 'ownership_status',
        // address
        'address'         => 'address',
        'alamat'          => 'address',
        // district
        'district'        => 'district',
        'kecamatan'       => 'district',
        // city
        'city'            => 'city',
        'kabupaten/kota'  => 'city',
        'kabupaten kota'  => 'city',
        'kabupaten_kota'  => 'city',
        'kota'            => 'city',
        // latitude
        'latitude'        => 'latitude',
        'lat'             => 'latitude',
        'lintang'         => 'latitude',
        // longitude
        'longitude'       => 'longitude',
        'lng'             => 'longitude',
        'long'            => 'longitude',
        'bujur'           => 'longitude',
        // portion_count
        'portion count'   => 'portion_count',
        'portion_count'   => 'portion_count',
        'portions'        => 'portion_count',
        'jumlah porsi'    => 'portion_count',
        'jumlah_porsi'    => 'portion_count',
        'porsi'           => 'portion_count',
        'total porsi'     => 'portion_count',
        // skip-only
        'no'              => null,
    ];

    /**
     * Internal keys that MUST be covered by at least one CSV header alias.
     */
    private const REQUIRED_KEYS = ['school_name', 'school_type', 'ownership_status', 'portion_count'];

    /**
     * Normalize a raw CSV header string.
     */
    private function normalizeHeader(string $raw): string
    {
        // Remove UTF-8 BOM (EF BB BF)
        $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

        $raw = mb_strtolower(trim($raw));
        $raw = str_replace('_', ' ', $raw);
        $raw = preg_replace('/\s+/', ' ', $raw);

        return $raw;
    }

    /**
     * Parse a CSV file with robust header normalization and alias mapping.
     *
     * Returns:
     *   - On success: ['rows' => [...]]
     *   - On failure: ['error' => '...', 'missing_columns' => [...], 'detected_columns' => [...]]
     */
    private function parseCsv(string $path): array
    {
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return ['error' => 'File is empty or cannot be read.', 'rows' => []];
        }

        // Strip UTF-8 BOM
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Detect delimiter: semicolon (European Excel), tab, or comma
        $firstLine = strtok($content, "\n");
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        // Read header row
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (!$rawHeader) {
            fclose($handle);
            return ['error' => 'CSV header row could not be read.', 'rows' => []];
        }

        // Normalize headers → map to internal keys
        $detectedColumns = [];
        $mappedHeaders   = [];

        foreach ($rawHeader as $col) {
            $normalized  = $this->normalizeHeader((string) $col);
            $internalKey = self::HEADER_ALIASES[$normalized] ?? null;

            // Also try raw-normalized form directly (handles "kabupaten/kota")
            if ($internalKey === null) {
                $withoutUnderscore = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $col)));
                $internalKey = self::HEADER_ALIASES[$withoutUnderscore] ?? null;
            }

            $mappedHeaders[]   = $internalKey;
            $detectedColumns[] = trim((string) $col);
        }

        // Validate required columns
        $coveredKeys    = array_filter(array_unique($mappedHeaders));
        $missingColumns = [];

        foreach (self::REQUIRED_KEYS as $requiredKey) {
            if (!in_array($requiredKey, $coveredKeys, true)) {
                $missingColumns[] = $requiredKey;
            }
        }

        if (!empty($missingColumns)) {
            fclose($handle);

            $labels = array_map(fn ($k) => str_replace('_', ' ', $k), $missingColumns);

            return [
                'error'            => 'Required columns not found: ' . implode(', ', $labels) . '.',
                'missing_columns'  => $missingColumns,
                'detected_columns' => $detectedColumns,
                'rows'             => [],
            ];
        }

        // Parse data rows
        $rows = [];
        while (($line = fgetcsv($handle, 0, $delimiter)) !== false) {
            $row = [];
            foreach ($line as $i => $value) {
                $key = $mappedHeaders[$i] ?? null;
                if ($key !== null) {
                    $row[$key] = $value;
                }
            }

            if (!empty(trim($row['school_name'] ?? ''))) {
                $rows[] = $row;
            }
        }

        fclose($handle);

        return ['rows' => $rows];
    }

    /**
     * Parse a coordinate value from a CSV cell.
     * Returns null for empty/non-numeric values.
     */
    private function parseCoordinate(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);

        return $float !== false ? (float) $float : null;
    }
}
