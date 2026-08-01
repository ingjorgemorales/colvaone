<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

    #[Fillable([
        'name',
        'last_name',
        'document_type',
        'document_number',
        'email',
        'email_verified_at',
        'phone',
        'position',
        'area',
        'department',
        'role',
        'photo_path',
        'password',
        'is_active',
        'must_change_password',
        'last_login_at',
        'created_by',
        'updated_by',
    ])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'must_change_password' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function getRoleLabelAttribute(): string
    {
        return match ($this->role) {
            'admin' => 'Administrador',
            'user' => 'Usuario',
            'viewer' => 'Visualizador',
            default => 'Sin rol',
        };
    }

    public function getInitialsAttribute(): string
    {
        $first = mb_substr($this->name, 0, 1);
        $last = mb_substr($this->last_name ?? '', 0, 1);
        return strtoupper($first . $last);
    }
}
