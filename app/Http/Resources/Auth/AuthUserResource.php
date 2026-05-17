<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
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
            'role_name'       => $this->role_name,
            'sppg'            => $this->sppg ? [
                'id'     => $this->sppg->id,
                'name'   => $this->sppg->name,
                'status' => $this->sppg->status,
            ] : null,
            'permissions'     => $this->resolvePermissions(),
        ];
    }
    private function resolvePermissions(): array
    {
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