<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_type',
        'email',
        'password',
        'name',
        'surname',
        'cf',
        'company_society',
        'company_name',
        'company_surname',
        'company_cf',
        'company_pi',
        'company_sdi',
        'company_pec',
        'phone',
        'prescriber_id'
    ];

    // Cripta automaticamente i dati sensibili
    protected $casts = [
        'cf' => 'encrypted',
        'company_cf' => 'encrypted',
        'company_pi' => 'encrypted',
        'phone' => 'encrypted',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    use SoftDeletes;

    // Relazione 1:N con addresses
    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    // Solo indirizzi di fatturazione
    public function billingAddresses()
    {
        return $this->hasOne(Address::class)->where('type', 'billing');
    }

    // Solo indirizzi di spedizione
    public function shippingAddresses()
    {
        return $this->hasOne(Address::class)->where('type', 'shipping');
    }

    // Solo indirizzi di fatturazione
    public function billingAddressesCity()
    {
        return $this->hasOne(Address::class)->where('type', 'billing')->join('cities','cities.id','addresses.city_id')->select('cities.*')->first();
    }

    // Solo indirizzi di spedizione
    public function shippingAddressesCity()
    {
        return $this->hasOne(Address::class)->where('type', 'shipping')->join('cities','cities.id','addresses.city_id')->select('cities.*')->first();
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function cartItems() {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
