<?php

declare(strict_types=1);

return [
    'already_assigned' => 'Order #:id is no longer assignable (current status: :status).',
    'no_available_driver' => 'No available driver could be assigned to order #:id.',

    'statuses' => [
        'pending' => 'pending',
        'assigned' => 'assigned',
        'in_progress' => 'in progress',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
    ],
];
