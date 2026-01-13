<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string $table_number
 * @property string $qr_uuid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Table extends Model
{
    protected $fillable = [
        'table_number',
        'qr_uuid'
    ];
}
