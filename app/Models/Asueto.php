<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Asueto extends Model
{
    protected $primaryKey = 'id_asueto';
    protected $fillable = ['nombre', 'fecha_inicio', 'fecha_fin', 'id_sucursal'];
}