<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'prescriber_id', // aggiunto
        'order_number',
        'subtotal',
        'total_vat',
        'shipping_cost',
        'total',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'payment_status',
        'paid_at',
        'status',
        'tracking_number',
        'shipping_address',
        'billing_address',
        'address',
        'cap',
        'city_id',
        'phone',
        'note',
        'recipient_name'
    ];

    protected $casts = [
        'phone' => 'encrypted',
    ];

    protected $dates = [
        'paid_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relazione con l'utente che ha effettuato l'ordine
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relazione con il prescrittore (medico)
     */
    public function prescriber()
    {
        return $this->belongsTo(User::class, 'prescriber_id');
    }

    /**
     * Relazione con gli items dell'ordine
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Controllo se il pagamento è completato
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getStatusStepAttribute()
    {
        return match($this->status) {
            'pending' => 1,
            'processing' => 2,
            'shipped' => 3,
            'delivered' => 4,
            default => 0,
        };
    }

    public function returns()
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function refunds()
    {
        return $this->hasOne(OrderRefund::class);
    }
}