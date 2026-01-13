<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $table_id
 * @property string $customer_name
 * @property float $total_price
 * @property string $payment_status
 * @property string $snap_token
 * @property string $midtrans_order_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Order extends Model
{
    protected $guarded = ['id'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
    }
}
