<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'email',
    'event',
    'successful',
    'ip_address',
    'user_agent',
    'route',
    'request_id',
    'reason',
    'occurred_at',
])]
class AuthEvent extends BaseModel
{
    protected function casts(): array
    {
        return [
            'successful' => 'boolean',
            'occurred_at' => 'datetime',
        ];
    }

    public const EVENT_LABELS = [
        'login' => 'Inicio de sesión',
        'login_success' => 'Inicio de sesión',
        'login_failed' => 'Inicio de sesión fallido',
        'login_rate_limited' => 'Límite de intentos superado',
        'login_blocked_inactive_user' => 'Acceso bloqueado (inactivo)',
        'logout' => 'Cierre de sesión',
        'password_reset' => 'Restablecimiento de clave',
        'password_reset_code_sent' => 'Código de recuperación enviado',
        'password_changed' => 'Contraseña modificada',
        'email_verified' => 'Correo verificado',
        'verification_email_sent' => 'Correo de verificación enviado',

        'profile_updated' => 'Perfil actualizado',

        'user_created' => 'Usuario creado',
        'user_updated' => 'Usuario actualizado',
        'user_toggled' => 'Estado de usuario cambiado',
        'user_deleted' => 'Usuario eliminado',

        'role_created' => 'Rol creado',
        'role_updated' => 'Rol actualizado',
        'role_toggled' => 'Estado de rol cambiado',
        'role_deleted' => 'Rol eliminado',

        'task_created' => 'Tarea creada',
        'task_updated' => 'Tarea actualizada',
        'task_archived' => 'Tarea archivada',
        'task_progress_updated' => 'Progreso de tarea actualizado',
        'task_comment_added' => 'Comentario en tarea agregado',
        'task_status_updated' => 'Estado de tarea actualizado',

        'group_created' => 'Grupo creado',
        'group_updated' => 'Grupo actualizado',
        'group_deleted' => 'Grupo eliminado',
        'group_toggled' => 'Estado de grupo cambiado',
        'group_member_added' => 'Integrante de grupo agregado',
        'group_member_removed' => 'Integrante de grupo removido',

        'indicator_created' => 'Indicador creado',
        'indicator_updated' => 'Indicador actualizado',
        'indicator_toggled' => 'Estado de indicador cambiado',
        'indicator_result_created' => 'Resultado registrado',
        'indicator_result_updated' => 'Resultado actualizado',
        'indicator_result_toggled' => 'Estado de resultado cambiado',

        'process_created' => 'Proceso creado',
        'process_updated' => 'Proceso actualizado',
        'process_toggled' => 'Estado de proceso cambiado',
        'process_moved' => 'Orden de proceso modificado',
        'subprocess_created' => 'Subproceso creado',
        'subprocess_updated' => 'Subproceso actualizado',
        'subprocess_toggled' => 'Estado de subproceso cambiado',
        'subprocess_moved' => 'Orden de subproceso modificado',

        'application_created' => 'Aplicativo creado',
        'application_updated' => 'Aplicativo actualizado',
        'application_toggled' => 'Estado de aplicativo cambiado',

        'committee_created' => 'Comité creado',
        'committee_updated' => 'Comité actualizado',
        'committee_toggled' => 'Estado de comité cambiado',
        'committee_report_added' => 'Relato de comité agregado',
    ];

    public static function labelFor(?string $event): string
    {
        if (! $event) {
            return '-';
        }

        return self::EVENT_LABELS[$event] ?? ucwords(str_replace('_', ' ', $event));
    }

    public function getEventLabelAttribute(): string
    {
        return self::labelFor($this->event);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
