<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', 'Example'),
    ],

    'domains' => [
        'insureship' => [
            'mailer' => 'is_mailer',
            'template' => 'insureship',
            'company_name' => 'InsureShip',
            'email' => 'no_reply@insureship.com',
            'client_id' => 56867,
            'superclient_id' => 1,
            'claims_url' => 'https://claims.insureship.com',
            'claims_phone' => '866-701-3654',
            'claims_email' => 'claims@insureship.com',
            'api_url' => 'https://api.insureship.com',
            'main_url' => 'https://www.insureship.com',
        ],
        'ticketguardian' => [
            'mailer' => 'tg_mailer',
            'template' => 'ticketguardian',
            'company_name' => 'TicketGuardian',
            'email' => 'no_reply@ticketguardian.net',
            'client_id' => 56858,
            'superclient_id' => 7,
            'claims_url' => 'https://claims.ticketguardian.net',
            'claims_phone' => '866-675-4673',
            'claims_email' => 'claims@ticketguardian.net',
            'api_url' => 'https://api.ticketguardian.net',
            'main_url' => 'https://www.ticketguardian.net',
        ],
        'paycertify' => [
            'mailer' => 'pc_mailer',
            'template' => 'paycertify',
            'company_name' => 'PayCertify',
            'email' => 'no_reply@paycertify.com',
            'client_id' => 56856,
            'superclient_id' => 3,
            'claims_url' => 'https://claims.paycertify.com',
            'claims_phone' => '866-584-7008',
            'claims_email' => 'claims@paycertify.com',
            'api_url' => 'https://api.paycertify.com',
            'main_url' => 'https://www.paycertify.com',
        ],
        'cycoverpro' => [
            'mailer' => 'ccp_mailer',
            'template' => 'cycoverpro',
            'company_name' => 'CyCoverPro',
            'email' => 'no_reply@cycoverpro.com',
            'client_id' => 56860,
            'superclient_id' => 1,
            'claims_url' => 'https://claims.cycoverpro.com',
            'claims_phone' => '866-258-4667',
            'claims_email' => 'claims@cycoverpro.com',
            'api_url' => 'https://api.cycoverpro.com',
            'main_url' => 'https://www.cycoverpro.com',
        ],
        'pinpointintel' => [
            'mailer' => 'ppi_mailer',
            'template' => 'pinpointintel',
            'company_name' => 'Pinpoint Intelligence',
            'email' => 'no_reply@pinpointintel.com',
            'client_id' => 56862,
            'superclient_id' => 4,
            'claims_url' => 'https://claims.pinpointintel.com',
            'claims_phone' => '855-270-8452',
            'claims_email' => 'claims@pinpointintel.com',
            'api_url' => 'https://api.pinpointintel.com',
            'main_url' => 'https://www.pinpointintel.com',
        ],
        'fulfilrr' => [
            'mailer' => 'flr_mailer',
            'template' => 'fulfilrr',
            'company_name' => 'fulfillr',
            'email' => 'no_reply@fulfilrr.com',
            'client_id' => 57294,
            'superclient_id' => 4,
            'claims_url' => 'https://claims.fulfilrr.com',
            'claims_phone' => '855-270-8452',
            'claims_email' => 'claims@fulfilrr.com',
            'api_url' => 'https://api.fulfilrr.com',
            'main_url' => 'https://www.fulfilrr.com',
        ],
        'shopguaranteeit' => [
            'mailer' => 'sg_mailer',
            'template' => 'shopguaranteeit',
            'company_name' => 'ShopGuaranteeIt',
            'email' => 'no_reply@shopguaranteeit.com',
            'client_id' => 56864,
            'superclient_id' => 6,
            'claims_url' => 'https://claims.shopguaranteeit.com',
            'claims_phone' => '',
            'claims_email' => 'claims@shopguaranteeit.com',
            'api_url' => 'https://api.shopguaranteeit.com',
            'main_url' => 'https://www.shopguaranteeit.com',
        ],
        'shopguarantee' => [
            'mailer' => 'sg_mailer',
            'template' => 'shopguarantee',
            'company_name' => 'ShopGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 56866,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '888-989-7720',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
    ],

    'clients' => [
        1 => [ // Test Client
            'mailer' => 'sg_mailer',
            'template' => 'testclient',
            'company_name' => 'ShopGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 1,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '888-989-7720',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
        56854 => [ // CyberGuarantee
            'mailer' => 'sg_mailer',
            'template' => 'cyberguarantee',
            'company_name' => 'CyberGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 56854,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '866-439-0260',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
        56855 => [ // ShipGuarantee
            'mailer' => 'sg_mailer',
            'template' => 'shipguarantee',
            'company_name' => 'ShipGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 56855,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '866-675-4656',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
        56856 => [ // PayCertify
            'mailer' => 'pc_mailer',
            'template' => 'paycertify',
            'company_name' => 'PayCertify',
            'email' => 'no_reply@paycertify.com',
            'client_id' => 56856,
            'superclient_id' => 3,
            'claims_url' => 'https://claims.paycertify.com',
            'claims_phone' => '866-584-7008',
            'claims_email' => 'claims@paycertify.com',
            'api_url' => 'https://api.paycertify.com',
            'main_url' => 'https://www.paycertify.com',
        ],
        56858 => [ // TicketGuardian
            'mailer' => 'tg_mailer',
            'template' => 'ticketguardian',
            'company_name' => 'TicketGuardian',
            'email' => 'no_reply@ticketguardian.net',
            'client_id' => 56858,
            'superclient_id' => 7,
            'claims_url' => 'https://claims.ticketguardian.net',
            'claims_phone' => '866-675-4673',
            'claims_email' => 'claims@ticketguardian.net',
            'api_url' => 'https://api.ticketguardian.net',
            'main_url' => 'https://www.ticketguardian.net',
        ],
        56860 => [ // CyCoverPro
            'mailer' => 'ccp_mailer',
            'template' => 'cycoverpro',
            'company_name' => 'CyCoverPro',
            'email' => 'no_reply@cycoverpro.com',
            'client_id' => 56860,
            'superclient_id' => 1,
            'claims_url' => 'https://claims.cycoverpro.com',
            'claims_phone' => '866-258-4667',
            'claims_email' => 'claims@cycoverpro.com',
            'api_url' => 'https://api.cycoverpro.com',
            'main_url' => 'https://www.cycoverpro.com',
        ],
        56862 => [ // PinpointIntel
            'mailer' => 'ppi_mailer',
            'template' => 'pinpointintel',
            'company_name' => 'Pinpoint Intelligence',
            'email' => 'no_reply@pinpointintel.com',
            'client_id' => 56862,
            'superclient_id' => 4,
            'claims_url' => 'https://claims.pinpointintel.com',
            'claims_phone' => '855-270-8452',
            'claims_email' => 'claims@pinpointintel.com',
            'api_url' => 'https://api.pinpointintel.com',
            'main_url' => 'https://www.pinpointintel.com',
        ],
        56863 => [ // FreshGuarantee
            'mailer' => 'sg_mailer',
            'template' => 'freshguarantee',
            'company_name' => 'FreshGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 56863,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '888-989-7721',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
        56864 => [ // ShopGuaranteeIt
            'mailer' => 'sg_mailer',
            'template' => 'shopguaranteeit',
            'company_name' => 'ShopGuaranteeIt',
            'email' => 'no_reply@shopguaranteeit.com',
            'client_id' => 56864,
            'superclient_id' => 6,
            'claims_url' => 'https://claims.shopguaranteeit.com',
            'claims_phone' => '',
            'claims_email' => 'claims@shopguaranteeit.com',
            'api_url' => 'https://api.shopguaranteeit.com',
            'main_url' => 'https://www.shopguaranteeit.com',
        ],
        56866 => [ // Default ShopGuarantee
            'mailer' => 'sg_mailer',
            'template' => 'shopguarantee',
            'company_name' => 'ShopGuarantee',
            'email' => 'no_reply@shopguarantee.com',
            'client_id' => 56866,
            'superclient_id' => 2,
            'claims_url' => 'https://claims.shopguarantee.com',
            'claims_phone' => '888-989-7720',
            'claims_email' => 'claims@shopguarantee.com',
            'api_url' => 'https://api.shopguarantee.com',
            'main_url' => 'https://www.shopguarantee.com',
        ],
        57294 => [ // fulfilrr
            'mailer' => 'flr_mailer',
            'template' => 'fulfilrr',
            'company_name' => 'fulfillr',
            'email' => 'no_reply@fulfilrr.com',
            'client_id' => 57294,
            'superclient_id' => 4,
            'claims_url' => 'https://claims.fulfilrr.com',
            'claims_phone' => '855-270-8452',
            'claims_email' => 'claims@fulfilrr.com',
            'api_url' => 'https://api.fulfilrr.com',
            'main_url' => 'https://www.fulfilrr.com',
        ],
    ],

    'subclients' => [
        56896 => 56863, // FreshGuarantee
        76919 => 56863,
        76924 => 56863,
        76918 => 56863,
        56876 => 56863,
        76923 => 56863,
    ],

    'default' => [
        'mailer' => 'is_mailer',
        'template' => 'insureship',
        'company_name' => 'InsureShip',
        'email' => 'no_reply@insureship.com',
        'client_id' => 56867,
        'superclient_id' => 1,
        'claims_url' => 'https://claims.insureship.com',
        'claims_phone' => '866-701-3654',
        'claims_email' => 'claims@insureship.com',
        'api_url' => 'https://api.insureship.com',
        'main_url' => 'https://www.insureship.com',
    ],
];
