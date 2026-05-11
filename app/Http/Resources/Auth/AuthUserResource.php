<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the user model into the API response.
     * Eager-loads roles + permissions so the frontend
     * can render role-based UI immediately.
     */
    public function toArray(Request $request): array
    {
        $this->resource->loadMissing('roles:id,name', 'roles.permissions:id,name');

        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'profile_picture'   => $this->profile_picture,
            'is_active'         => $this->is_active,
            'sppg_id'           => $this->sppg_id,
            'email_verified_at' => $this->email_verified_at,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'roles'             => $this->roles->map(fn ($role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->map(fn ($p) => [
                    'id'   => $p->id,
                    'name' => $p->name,
                ]),
            ]),
        ];
    }
}
