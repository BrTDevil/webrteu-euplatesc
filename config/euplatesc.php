<?php

declare(strict_types=1);

return [

    'merchant_id' => env('EUPLATESC_MERCHANT_ID'),

    /*
    | Cheia secretă hex primită de la EuPlatesc (folosită ca cheie HMAC după hex2bin).
    */
    'secret_key' => env('EUPLATESC_SECRET_KEY'),

    'url' => env('EUPLATESC_URL', 'https://secure.euplatesc.ro/tdsprocess/tranzactd.php'),
    'currency' => env('EUPLATESC_CURRENCY', 'RON'),
    'lang' => env('EUPLATESC_LANG', 'ro'),

    /*
    | Trimite URL-urile de return / Silent URL per tranzacție (ExtraData[...]). ⚠️ de confirmat cu EuPlatesc;
    | altfel se configurează fix din panou.
    */
    'send_urls' => (bool) env('EUPLATESC_SEND_URLS', false),

];
