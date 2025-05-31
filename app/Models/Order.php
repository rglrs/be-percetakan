<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $fillable = [
        'customer_name',
        'phone_number',
        'file_url',
        'file_name',
        'product_type',
        'quantity',
        'paper_type',
        'size',
        'status',
        'deadline',
        'notes',
        'process_note',
    ];
    
    public function getFileUrlAttribute($value)
    {
        // Jika $value (nilai dari kolom file_url di DB) ada,
        // ubah menjadi URL lengkap. Jika tidak, kembalikan null.
        return $value ? URL::asset($value) : null;
    }

    protected $casts = [
        'deadline' => 'date',
        'quantity' => 'integer',
    ];
}
