<?php

return [
    'already_assigned' => 'الطلب رقم :id لم يعد قابلاً للإسناد (الحالة الحالية: :status).',
    'no_available_driver' => 'لا يوجد سائق متاح يمكن إسناده للطلب رقم :id.',
    'not_found' => 'الطلب رقم :id غير موجود.',

    'statuses' => [
        'pending' => 'قيد الانتظار',
        'assigned' => 'مُسنَد',
        'in_progress' => 'قيد التنفيذ',
        'completed' => 'مكتمل',
        'cancelled' => 'ملغى',
    ],
];
