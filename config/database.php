<?php
return [
    'host' => 'localhost',
    'database' => 'bicaranta_db',
    'username' => 'root',
    'password' => '', 
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
    
    'timeout' => 30,
    'retry_attempts' => 3,
    
    'debug' => true, 
];
?>