<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the user model into the API response.
     *
     * RBAC Architecture:
     * - role_type (users)      → gate kasar: super_admin / sppg_user
     * - role_id   (employees)  → gate detail: permissions via role → role_permission → permissions
     *
     * Permission format: flat slugs → "{module}.{action}"
     * Example: ["employee.create", "employee.read", "nutrition.read"]
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing([
            'employee.role.permissions',
            'sppg:id,name,status',
        ]);

        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'email'           => $this->email,
            'phone'           => $this->phone,
            'profile_picture' => $this->profile_picture,
            'is_active'       => $this->is_active,
            'role_type'       => $this->role_type,
            'role_name'       => $this->employee?->role?->name ?? 'Tanpa Akses',
            'sppg'            => $this->sppg ? [
                'id'     => $this->sppg->id,
                'name'   => $this->sppg->name,
                'status' => $this->sppg->status,
            ] : null,
            'permissions'     => $this->resolvePermissions(),
        ];
    }

    /**
     * Resolve permissions berdasarkan role_type.
     *
     * super_admin → skip RBAC tabel, hardcode read-only (untuk next development)
     * sppg_user   → ambil dari employees → role → permissions (dynamic, realtime)
     */
    private function resolvePermissions(): array
    {
        // sppg_user → dynamic via RBAC tabel
        if ($this->role_type === 'sppg_user') {
            return $this->employee?->role?->permissions
                ?->pluck('slug')
                ->toArray() ?? [];
        }

        // super_admin → placeholder untuk next development
        // TODO: implement super_admin permissions
        return [];
    }
}