<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'sec_users';
    protected $primaryKey = 'login';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'login',
        'pswd',
        'name',
        'email',
        'active',
        'activation_code',
        'priv_admin',
        'mfa',
        'picture',
        'role',
        'phone',
        'pswd_last_updated',
        'mfa_last_updated',
    ];

    protected $hidden = [
        'pswd',
        'activation_code',
    ];

    /**
     * Obtiene el password para autenticación de Laravel
     */
    public function getAuthPassword()
    {
        return $this->pswd;
    }

    /**
     * Verifica si el usuario está activo en Scriptcase
     */
    public function isActive(): bool
    {
        return strtoupper($this->active ?? 'N') === 'Y';
    }

    /**
     * Relación con detalles de usuario en Scriptcase
     */
    public function detalles()
    {
        return $this->hasOne(SecUsuarioDetalle::class, 'PK_identificacion', 'login');
    }

    /**
     * Relación con el perfil de colaborador de Talento Humano
     */
    public function empleado()
    {
        return $this->hasOne(ThEmpleado::class, 'sec_user_login', 'login');
    }
}
