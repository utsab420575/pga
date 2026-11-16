<?php

/*
|--------------------------------------------------------------------------
| PGA - Shared gateway Pull / Push settings
|--------------------------------------------------------------------------
|
| Copy to: config/payment_gateways.php
| Used by:  App\Http\Controllers\GatewayPaymentController
|
| The gateway is chosen by the route (->defaults('gateway', 'upay')),
| never by a value sent in the request.
|
| To add a new gateway later: add one entry under 'gateways', two routes
| in routes/web.php, and the two URLs in the CSRF except list in
| bootstrap/app.php.
|
*/

return [

    // md5 of the authkey the CENTRAL payment system sends to PGA.
    // Default = same hash used by the existing PgaPaymentApiController,
    // so central's CHILD_PGA_KEY works without change.
    // To change: set CENTRAL_AUTH_HASH in .env
    'central_auth_hash' => env('CENTRAL_AUTH_HASH', 'ea737681fab0183ca42dca52400f20bf'),

    'gateways' => [

        'upay' => [
            'bankname'   => 'Upay',     // saved in payments.bankname
            'log'        => 'Upay',     // prefix in log lines
            'test_rolls' => [],         // see below
        ],
        'bkash' => [
            'bankname'   => 'Bkash',
            'log'        => 'Bkash',
            'test_rolls' => [],         // old bkashPull/bkashPush had test mode commented out
        ],
        'agrani' => [
            'bankname'   => 'Agrani',
            'log'        => 'Agrani',
            // Old agraniPull/agraniPush had test mode ON for these rolls:
            // Pull returns amount "5" as "Test Applicant", Push accepts only amount 5
            // (and still saves a real payment). Empty this list to turn it off.
           // 'test_rolls' => ['100001', '200001'],
        ],

    ],

];
