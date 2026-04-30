<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/helpers.php';

session_destroy();
session_start();
flash('success', 'You have been logged out.');
redirect('');
