<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'address',
        'cap',
        'city_id',
        'phone',
        'note',
        'recipient_name',
    ];

    // Cripta il telefono per sicurezza
    protected $casts = [
        'phone' => 'encrypted',
    ];

    // Relazione inversa con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
