<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Doctor extends Model
{
    use HasFactory;

    protected $table = 'doctors';

    protected $fillable = ['name', 'surname', 'token', 'percentage', 'commission_all_products'];

    protected $casts = [
        'commission_all_products' => 'boolean',
        'percentage' => 'float',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withTimestamps();
    }

    private function orderQuery(string $type = 'tot')
    {
        $query = Order::query()
            ->with(['items.product.doctors'])
            ->where('prescriber_id', $this->token)
            ->where('status', '!=', 'cancelled');

        if (!$this->commission_all_products) {
            $query->whereHas('items.product.doctors', function ($q) {
                $q->where('doctors.id', $this->id);
            });
        }

        return $query
            ->when($type === 'day', fn ($q) => $q->whereDate('created_at', Carbon::today()))
            ->when($type === 'week', fn ($q) => $q->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]))
            ->when($type === 'month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->when($type === 'year', fn ($q) => $q->whereYear('created_at', now()->year));
    }

    public function commissionDataForItem($item): ?array
    {
        if (!$item->product) {
            return null;
        }

        if ($this->commission_all_products) {
            return ['percentage' => (float) $this->percentage];
        }

        $doctorOnProduct = $item->product->doctors->firstWhere('id', $this->id);
        if (!$doctorOnProduct) {
            return null;
        }

        return ['percentage' => (float) ($doctorOnProduct->pivot?->percentage ?? $doctorOnProduct->percentage ?? 0)];
    }

    public function validProductsForOrder($order): Collection
    {
        return $order->items->map(function ($item) use ($order) {
            $commission = $this->commissionDataForItem($item);
            if ($commission === null) {
                return null;
            }

            $percentage = $commission['percentage'];
            return [
                'order_id' => $order->id,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'percentage' => $percentage,
                'total_gain' => (float) $item->price * (int) $item->quantity * $percentage / 100,
            ];
        })->filter()->values();
    }

    public function prescriberOrders(int $perPage = 10, string $type = 'tot'): LengthAwarePaginator
    {
        return $this->orderQuery($type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->through(function ($order) {
                $order->validProducts = $this->validProductsForOrder($order);
                return $order;
            });
    }

    public function prescriberOrdersNoPage(string $type = 'tot')
    {
        return $this->orderQuery($type)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                $order->validProducts = $this->validProductsForOrder($order);
                return $order;
            });
    }

    public function totalCommission(string $type = 'tot'): float
    {
        $total = 0;
        foreach ($this->orderQuery($type)->get() as $order) {
            $total += $this->validProductsForOrder($order)->sum('total_gain');
        }
        return (float) $total;
    }
}
