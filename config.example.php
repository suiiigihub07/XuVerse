<?php
// Copy to config.local.php on the host. Never commit the filled-in copy.
// Prefer a file outside the document root, selected by XUVERSE_CONFIG_FILE.
return [
    'PRODUCTION' => true,
    'BASE_URL' => 'https://portfolio.example.com',
    'APP_PATH' => '/',
    'DB_HOST' => 'localhost',
    'DB_NAME' => 'replace_database_name',
    'DB_USER' => 'replace_database_user',
    'DB_PASSWORD' => 'replace_database_password',
    'CONTACT_TO' => '',
    'CONTACT_FROM' => '',
];
