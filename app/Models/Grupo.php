<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grupo extends Model
{
    use HasFactory;

    protected $table = 'grupos';

    protected $fillable = [
        'nombre_grupo'
    ];

    // Relación: Un grupo puede tener muchos créditos
    public function creditos()
    {
        return $this->hasMany(Credito::class, 'grupo_id', 'id');
    }
}