<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server Requirements
    |--------------------------------------------------------------------------
    */
    'php_version' => '8.1' ,

    'extensions' => [
        'php' => [
            'BCMath',
            'JSON',
            'Mbstring',
            'OpenSSL',
            'GD',
            'cURL',
            'XML',
            'Ctype','PDO','JSON','DOM','PCRE','Tokenizer'
        ],
        'apache' => [
            'mod_rewrite',
        ],
        'files' => [
            '.env',
            'database.sql',
            'app/Providers/RouteServiceProvider.php',
        ],
    ],

];
