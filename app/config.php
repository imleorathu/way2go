<?php
declare(strict_types=1);

const APP_NAME = 'Way2Go';
const BASE_URL = '/waytogo';

const DB_HOST = '127.0.0.1';
const DB_NAME = 'way2go';
const DB_USER = 'root';
const DB_PASS = '';

date_default_timezone_set('Asia/Colombo');

if (session_status() === PHP_SESSION_NONE) {
    $sessionPath = dirname(__DIR__) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        mkdir($sessionPath, 0775, true);
    }
    session_save_path($sessionPath);
    session_start();
}
