<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class user_details extends Model
{
    use HasFactory;
    protected $table = 'user_details';
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'image',
		'password',
		'role_id',
        'address',
		'username',
        'country',
        'state',
		'designation',
		'status',
		'fcm_tokens',
		'faqs',
		'zone_management',
		'select_commission',
        'provideo_id',
    ];
    // protected $hidden = [
    //     // 'password',
    //     // 'remember_token',
    // ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
