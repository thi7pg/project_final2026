<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderReady
{
    use Dispatchable;

    public function __construct(public Order $order) {}
}
