<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';
    protected $primaryKey = 'id_group';

    protected $fillable = [
        'nombre_grupo',
        'id_sucursal',
        'id_asesor',
        'status',
    ];

    // Relación: Un grupo tiene muchos clientes
    public function clients()
    {
        return $this->belongsToMany(Cliente::class, 'client_group', 'group_id', 'client_id');
    }


public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /**
     * Relación: Un grupo es atendido por un asesor (Usuario).
     */
    public function asesor()
    {
        return $this->belongsTo(User::class, 'id_asesor', 'id');
    }

    public function creditos()
{
    return $this->morphMany(Credito::class, 'loanable');
}


}