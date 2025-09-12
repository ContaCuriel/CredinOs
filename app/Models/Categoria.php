<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nombre',
        'default_requiere_aprobacion',
        'account_id', // <-- 1. AÑADIMOS ESTA LÍNEA
    ];

    /**
     * Define la relación: Una categoría pertenece a una cuenta contable.
     */
    public function account(): BelongsTo // <-- 2. AÑADIMOS TODA ESTA FUNCIÓN
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    /**
     * Define la relación: Una categoría puede tener muchos gastos.
     */
    public function gastos(): HasMany
    {
        return $this->hasMany(Gasto::class);
    }
}
