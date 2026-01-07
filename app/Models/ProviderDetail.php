<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderDetail extends Model
{
    use HasFactory;

    protected $table = 'providers_details';

    protected $fillable = [
        'full_name',
        'email',
        'phone_number',
        'designation',
        'role',
        'select_commission',
        'selected_provider',
        'provider_id',
        'image',
        'address',
        'country',
        'state',
        'city',
        'languages',
        'reason',
        'skills',
        'why_choose_me',
        'about_you',
        'status',
        'verification_status',
    ];
}
