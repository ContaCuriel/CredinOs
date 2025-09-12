<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'default_term',
        'description',
        'requires_contract',
    ];
}