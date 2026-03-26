<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iva extends Model
{
    protected $table = 'iva';

    protected $fillable = [
        'name',
        'percentage',
        'active'
    ];
}
