<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ThEmpleado extends Model
{
    protected $table = 'th_empleados';
    protected $primaryKey = 'identificacion';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'identificacion',
        'empresa_id',
        'sec_user_login',
        'codigo_empleado',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'fecha_ingreso',
        'fecha_salida',
        'email',
        'telefono',
        'direccion',
        'cargo_id',
        'organigrama_id',
        'horario_predeterminado_id',
        'foto_url',
        'biometria_rostro',
        'biometria_huella',
        'biometria_tarjeta',
        'saldo_vacaciones_dias',
        'activo',
    ];

    protected $casts = [
        'fecha_ingreso' => 'date',
        'fecha_salida' => 'date',
        'fecha_nacimiento' => 'date',
        'biometria_rostro' => 'boolean',
        'biometria_huella' => 'boolean',
        'activo' => 'boolean',
        'saldo_vacaciones_dias' => 'decimal:2',
    ];

    public function horario()
    {
        return $this->belongsTo(ThHorario::class, 'horario_predeterminado_id');
    }

    public function timbradas()
    {
        return $this->hasMany(ThTimbrada::class, 'empleado_id', 'identificacion');
    }

    public function asistencias()
    {
        return $this->hasMany(ThAsistenciaDiaria::class, 'empleado_id', 'identificacion');
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombres} {$this->apellidos}");
    }
}
