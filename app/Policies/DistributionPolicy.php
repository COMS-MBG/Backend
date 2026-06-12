<?php

namespace App\Policies;

use App\Models\DeliverySchedule;
use App\Models\User;

/**
 * DistributionPolicy – mengontrol akses ke DeliverySchedule.
 *
 * BUG FIX: sebelumnya referensikan App\Models\Distribution yang tidak ada.
 * Sekarang menggunakan DeliverySchedule (model yang benar-benar exist).
 *
 * Catatan: sebagian besar authorization dilakukan via permission middleware
 * ('permission:distribution.*') dan abort_unless() di controller.
 * Policy ini berfungsi sebagai lapisan tambahan jika digunakan via Gate.
 */
class DistributionPolicy
{
    /**
     * Super admin bypass semua policy check.
     */
    public function before(User $user): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null; // lanjut ke method policy berikut
    }

    /**
     * Boleh lihat daftar jadwal pengiriman.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('distribution.read');
    }

    /**
     * Boleh lihat detail satu jadwal.
     * Kurir hanya boleh lihat jadwal yang ditugaskan kepadanya.
     */
    public function view(User $user, DeliverySchedule $schedule): bool
    {
        if ($user->hasAnyRole(['courier'])) {
            return $user->employee?->id === $schedule->courier_id;
        }

        return $user->hasPermission('distribution.read');
    }

    /**
     * Boleh membuat jadwal pengiriman (Admin Logistik only).
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('distribution.create');
    }

    /**
     * Boleh update jadwal (Admin Logistik, hanya status in_order/rejected).
     */
    public function update(User $user, DeliverySchedule $schedule): bool
    {
        if (!$user->hasPermission('distribution.update')) {
            return false;
        }

        return $schedule->isEditable();
    }

    /**
     * Boleh hapus jadwal (Admin Logistik, hanya status in_order).
     */
    public function delete(User $user, DeliverySchedule $schedule): bool
    {
        return $user->hasPermission('distribution.delete')
            && $schedule->status === DeliverySchedule::STATUS_IN_ORDER;
    }

    public function restore(User $user, DeliverySchedule $schedule): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, DeliverySchedule $schedule): bool
    {
        return $user->isSuperAdmin();
    }
}
