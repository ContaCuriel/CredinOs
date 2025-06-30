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
}