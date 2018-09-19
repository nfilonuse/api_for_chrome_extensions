<?php

namespace App\Models;

use Laravel\Cashier\Subscription;

class UserSubscriptions extends Subscription
{
    protected $table = 'user_subscriptions';
    public $timestamps = true;
}
