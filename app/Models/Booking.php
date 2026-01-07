<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quantity',
        'service_id',
        'address', 
        'description', 
        'booking_date', 
        'price',
        'discount',
        'sub_total',
        'tax',
        'total_amount',
        'payment_through',
        'handyman_id',
        'transaction_id',
        'redirect_url',
    
    ];
}

