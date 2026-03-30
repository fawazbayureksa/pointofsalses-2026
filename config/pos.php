<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Inactivity Timeout (minutes)
    |--------------------------------------------------------------------------
    |
    | The number of minutes of inactivity before a POS session is
    | automatically locked and the cashier must re-authenticate.
    |
    */
    'inactivity_timeout' => env('POS_INACTIVITY_TIMEOUT', 15),

];
