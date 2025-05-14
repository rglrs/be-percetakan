<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'phone_number',
        'file_url',
        'product_type',
        'quantity',
        'paper_type',
        'size',
        'status',
        'deadline',
        'notes',
        'process_note',
    ];
}
