<?php

return [
    'api_per_minute' => (int) env('RATE_LIMIT_API_PER_MINUTE', 60),
    'login_per_minute' => (int) env('RATE_LIMIT_LOGIN_PER_MINUTE', 5),
    'login_per_minute_by_ip' => (int) env('RATE_LIMIT_LOGIN_PER_MINUTE_BY_IP', 20),
    'orders_per_minute' => (int) env('RATE_LIMIT_ORDERS_PER_MINUTE', 10),
];
