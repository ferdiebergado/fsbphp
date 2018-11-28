<?php

return [
    'headers',
    'content-type',
    'mw_session',
    'mw_router',
    [
        function ($request) {
            $protected = [
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            ];
            return in_array($request->getMethod(), $protected);
        },
        'csrf',
        'sanitize'
    ],
    'requesthandler'
];
