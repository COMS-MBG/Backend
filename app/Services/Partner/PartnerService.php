<?php

namespace App\Services\Partner;

use App\Models\Partner;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartnerService
{
    // ─── List with filters & pagination ────────────────────────────────────────

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Partner::query();

        if (!empty($filters['bentuk'])) {
            $query->where('bentuk', $filters['bentuk']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['kecamatan'])) {
            $query->where('kecamatan', 'like', "%{$filters['kecamatan']}%");
        }
        if (!empty($filters['kabupaten_kota'])) {
            $query->where('kabupaten_kota', 'like', "%{$filters['kabupaten_kota']}%");
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_sekolah', 'like', "%{$search}%")
                  ->orWhere('npsn', 'like', "%{$search}%")
                  ->orWhere('kecamatan', 'like', "%{$search}%")
                  ->orWhere('kabupaten_kota', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('nama_sekolah')->paginate($perPage);
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
            'total_schools'  => Partner::count(),
            'total_negeri'   => Partner::where('status', 'Negeri')->count(),
            'total_swasta'   => Partner::where('status', 'Swasta')->count(),
            'total_sma'      => Partner::where('bentuk', 'SMA')->count(),
            'total_smk'      => Partner::where('bentuk', 'SMK')->count(),
            'total_porsi'    => (int) Partner::sum('jumlah_porsi'),
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
                'errors'  => ['File tidak berisi data yang valid.'],
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
        $created  = 0;
        $updated  = 0;
        $skipped  = 0;
        $errors   = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNum = $index + 2; // +2 because row 1 is header

                // Validate required fields
                $namaSekolah = trim($row['nama_sekolah'] ?? '');
                if (empty($namaSekolah)) {
                    $errors[] = "Baris {$rowNum}: Nama Sekolah kosong, dilewati.";
                    $skipped++;
                    continue;
                }

                $bentuk = trim($row['bentuk'] ?? '');
                if (empty($bentuk)) {
                    $errors[] = "Baris {$rowNum}: Bentuk kosong, dilewati.";
                    $skipped++;
                    continue;
                }

                $npsn = trim($row['npsn'] ?? '');

                // Upsert by NPSN if available, otherwise create new
                if (!empty($npsn)) {
                    $existing = Partner::where('npsn', $npsn)->first();

                    if ($existing) {
                        $existing->update([
                            'nama_sekolah'   => $namaSekolah,
                            'bentuk'         => $bentuk,
                            'status'         => trim($row['status'] ?? 'Negeri'),
                            'alamat'         => trim($row['alamat'] ?? ''),
                            'kecamatan'      => trim($row['kecamatan'] ?? ''),
                            'kabupaten_kota' => trim($row['kabupaten_kota'] ?? ''),
                            'latitude'       => $this->parseCoordinate($row['latitude'] ?? null),
                            'longitude'      => $this->parseCoordinate($row['longitude'] ?? null),
                            'jumlah_porsi'   => (int) ($row['jumlah_porsi'] ?? 0),
                        ]);
                        $updated++;
                        continue;
                    }
                }

                Partner::create([
                    'nama_sekolah'   => $namaSekolah,
                    'npsn'           => $npsn ?: null,
                    'bentuk'         => $bentuk,
                    'status'         => trim($row['status'] ?? 'Negeri'),
                    'alamat'         => trim($row['alamat'] ?? ''),
                    'kecamatan'      => trim($row['kecamatan'] ?? ''),
                    'kabupaten_kota' => trim($row['kabupaten_kota'] ?? ''),
                    'latitude'       => $this->parseCoordinate($row['latitude'] ?? null),
                    'longitude'      => $this->parseCoordinate($row['longitude'] ?? null),
                    'jumlah_porsi'   => (int) ($row['jumlah_porsi'] ?? 0),
                ]);
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
     * Normalization applied to aliases: lowercase, trimmed, underscores→spaces.
     * Aliases are checked AFTER the same normalization on CSV headers.
     */
    private const HEADER_ALIASES = [
        // nama_sekolah
        'nama sekolah'    => 'nama_sekolah',
        'nama_sekolah'    => 'nama_sekolah',
        'school name'     => 'nama_sekolah',
        // npsn
        'npsn'            => 'npsn',
        // bentuk
        'bentuk'          => 'bentuk',
        'jenis'           => 'bentuk',
        // status
        'status'          => 'status',
        // alamat
        'alamat'          => 'alamat',
        'address'         => 'alamat',
        // kecamatan
        'kecamatan'       => 'kecamatan',
        // kabupaten_kota
        'kabupaten/kota'  => 'kabupaten_kota',
        'kabupaten kota'  => 'kabupaten_kota',
        'kabupaten_kota'  => 'kabupaten_kota',
        'kota'            => 'kabupaten_kota',
        // latitude
        'latitude'        => 'latitude',
        'lat'             => 'latitude',
        'lintang'         => 'latitude',
        // longitude
        'longitude'       => 'longitude',
        'lng'             => 'longitude',
        'long'            => 'longitude',
        'bujur'           => 'longitude',
        // jumlah_porsi
        'jumlah porsi'    => 'jumlah_porsi',
        'jumlah_porsi'    => 'jumlah_porsi',
        'porsi'           => 'jumlah_porsi',
        'total porsi'     => 'jumlah_porsi',
        // skip-only
        'no'              => null,
    ];

    /**
     * Internal keys that MUST be covered by at least one CSV header alias.
     */
    private const REQUIRED_KEYS = ['nama_sekolah', 'bentuk', 'status', 'jumlah_porsi'];

    /**
     * Normalize a raw CSV header string.
     *
     * - Strip UTF-8 BOM
     * - Trim whitespace
     * - Lowercase
     * - Replace underscores with spaces (so "nama_sekolah" → "nama sekolah")
     * - Collapse multiple spaces
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
        // Read file content and strip BOM from the entire file first
        $content = file_get_contents($path);
        if ($content === false || trim($content) === '') {
            return ['error' => 'File kosong atau tidak dapat dibaca.', 'rows' => []];
        }

        // Strip UTF-8 BOM from file start
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);

        // Detect delimiter: semicolon (European Excel), tab, or comma
        $firstLine = strtok($content, "\n");
        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        // Parse via temp stream so fgetcsv works with detected delimiter
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);

        // Read header row
        $rawHeader = fgetcsv($handle, 0, $delimiter);
        if (!$rawHeader) {
            fclose($handle);
            return ['error' => 'Header CSV tidak dapat dibaca.', 'rows' => []];
        }

        // Normalize headers → map to internal keys
        $detectedColumns = [];
        $mappedHeaders   = [];

        foreach ($rawHeader as $col) {
            $normalized  = $this->normalizeHeader((string) $col);
            $internalKey = self::HEADER_ALIASES[$normalized] ?? null;

            // Also try the raw-normalized form directly in the alias map
            // (handles "kabupaten/kota" which doesn't get underscore-replaced)
            if ($internalKey === null) {
                $withoutUnderscore = mb_strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $col)));
                $internalKey = self::HEADER_ALIASES[$withoutUnderscore] ?? null;
            }

            $mappedHeaders[]   = $internalKey; // null = skip this column
            $detectedColumns[] = trim((string) $col);
        }

        // Validate: check that all required internal keys are covered
        $coveredKeys    = array_filter(array_unique($mappedHeaders));
        $missingColumns = [];

        foreach (self::REQUIRED_KEYS as $requiredKey) {
            if (!in_array($requiredKey, $coveredKeys, true)) {
                $missingColumns[] = $requiredKey;
            }
        }

        if (!empty($missingColumns)) {
            fclose($handle);

            // Human-readable label for error message
            $labels = array_map(fn ($k) => str_replace('_', ' ', $k), $missingColumns);

            return [
                'error'            => 'Kolom wajib tidak ditemukan: ' . implode(', ', $labels) . '.',
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

            if (!empty(trim($row['nama_sekolah'] ?? ''))) {
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
