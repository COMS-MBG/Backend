<?php

namespace App\Constants;

/**
 * Centralized role slug constants for route middleware.
 *
 * Usage in routes:
 *   ->middleware('role:' . RoleConstant::SPPG_MANAGEMENT_ROLES)
 */
class RoleConstant
{
    /** Roles with access to SPPG management features (employees, schools, partners, roles). */
    public const SPPG_MANAGEMENT_ROLES = 'pemilik|manajer|admin-sppg';
}
