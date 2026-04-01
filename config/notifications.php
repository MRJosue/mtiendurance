<?php

return [
    'broadcast_enabled' => filter_var(
        env('NOTIFICATIONS_BROADCAST_ENABLED', env('VITE_ENABLE_REVERB', false)),
        FILTER_VALIDATE_BOOL
    ),
];
