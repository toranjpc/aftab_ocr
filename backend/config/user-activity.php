<?php

return [
    'activated'        => true, // active/inactive all logging
    'middleware'       => ['web'],
    'route_path'       => 'user-activity',
    'admin_panel_path' => 'dashboard',
    'delete_limit'     => 90, // default 7 days

    'model' => [
        'user' => "Modules\Auth\Models\User"
    ],

    'log_events' => [
        'on_create'     => true,
        'on_edit'       => true,
        'on_delete'     => true,
        'on_login'      => true,
        'on_lockout'    => true
    ]
];
