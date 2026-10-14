<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThDispositivo extends Model
{
    protected $table = 'th_dispositivos';

    protected $fillable = [
        'empresa_id',
        'nombre',
        'ip_lan',
        'ip',
        'ip_local',
        'puerto',
        'puerto_local',
        'usuario',
        'password',
        'usuario_api',
        'password_api',
        'modelo',
        'serial_number',
        'ubicacion',
        'estado',
        'activo',
        'ultimo_ping',
    ];

    protected $casts = [
        'puerto' => 'integer',
        'puerto_local' => 'integer',
        'activo' => 'boolean',
        'ultimo_ping' => 'datetime',
    ];

    // Accessor para compatibilidad con código de QuitoBici y Blade
    public function getIpAttribute($value)
    {
        return $value ?: $this->ip_lan;
    }

    public function getIpLocalAttribute($value)
    {
        return $value ?: $this->ip_lan;
    }

    public function getUsuarioApiAttribute($value)
    {
        return $value ?: ($this->usuario ?? 'admin');
    }

    public function timbradas()
    {
        return $this->hasMany(ThTimbrada::class, 'dispositivo_id');
    }
}
