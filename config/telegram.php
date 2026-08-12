<?php

return [
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),
    'enabled' => filled(env('TELEGRAM_BOT_TOKEN')) && filled(env('TELEGRAM_CHAT_ID')),
];
