<?php

namespace App\Contracts\Repositories;

use App\Models\Customer;

interface CustomerRepositoryInterface
{
    public function firstOrCreate(string $name, ?string $phone): Customer;
}
