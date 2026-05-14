<?php
// Bu dosyayı kopyalayıp config.php olarak adlandır,
// sonra kendi değerlerinle doldur.
// config.php .gitignore'dadır — asla commit'leme.

define('DB_HOST',    'localhost');
define('DB_USER',    'db_kullanici_adi');
define('DB_PASS',    'db_sifren');
define('DB_NAME',    'portfolio_db');
define('DB_CHARSET', 'utf8mb4');

// php -r "echo password_hash('sifren', PASSWORD_BCRYPT);"
define('ADMIN_USER',  'emine');
define('ADMIN_PASS',  'buraya_bcrypt_hash_yaz');

define('NOTIFY_EMAIL', 'emine.kurucu.81@gmail.com'); // form bildirimlerinin gideceği adres
