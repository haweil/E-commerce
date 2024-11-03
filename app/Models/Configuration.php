<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'google_client_id',
        'google_client_secret',
        'google_redirect_uri',

        'paypal_client_id',
        'paypal_client_secret',
        'paypal_mode',

        'stripe_public_key',
        'stripe_secret_key',
    ];
}
