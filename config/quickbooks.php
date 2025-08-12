<?php

return [
    'auth_mode'     => env('QB_AUTH_MODE', 'oauth2'),
    'ClientID'      => env('QB_CLIENT_ID'),
    'ClientSecret'  => env('QB_CLIENT_SECRET'),
    'RedirectURI'   => env('QB_REDIRECT_URI'),
    'scope'         => env('QB_SCOPE', 'com.intuit.quickbooks.accounting'),
    'baseUrl'       => env('QB_BASE_URL', 'Production'),
];
