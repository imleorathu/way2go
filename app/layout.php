<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

function render_header(string $title): void
{
    $user = current_user();
    $path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $isAdmin = str_contains($path, '/admin/');
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | <?= APP_NAME ?></title>
        <link rel="stylesheet" href="<?= url('public/app.css') ?>">
        <script defer src="<?= url('public/app.js') ?>"></script>
    </head>
    <body>
    <header class="topbar">
        <a class="brand" href="<?= url() ?>">
            <span class="brand-mark">W</span>
            <span><?= APP_NAME ?></span>
        </a>
        <nav class="nav">
            <a class="<?= $path === BASE_URL . '/index.php' || $path === BASE_URL . '/' ? 'active' : '' ?>" href="<?= url() ?>">Packages</a>
            <?php if ($user): ?>
                <a class="<?= str_contains($path, '/customer/bookings.php') ? 'active' : '' ?>" href="<?= url('customer/bookings.php') ?>">My bookings</a>
                <?php if ($user['role'] === 'admin'): ?>
                    <a class="<?= $isAdmin ? 'active' : '' ?>" href="<?= url('admin/index.php') ?>">Admin</a>
                    <a href="<?= url('admin/packages.php') ?>">Packages</a>
                    <a href="<?= url('admin/bookings.php') ?>">Bookings</a>
                    <a href="<?= url('admin/users.php') ?>">Users</a>
                <?php endif; ?>
                <span class="user-chip"><?= e($user['name']) ?></span>
                <a href="<?= url('auth/logout.php') ?>">Logout</a>
            <?php else: ?>
                <a href="<?= url('auth/login.php') ?>">Login</a>
                <a class="button small" href="<?= url('auth/register.php') ?>">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
        <?php foreach (flashes() as $message): ?>
            <div class="flash <?= e($message['type']) ?>"><?= e($message['message']) ?></div>
        <?php endforeach; ?>
    <?php
}

function render_footer(): void
{
    ?>
    </main>
    <footer class="footer">
        <span><?= APP_NAME ?> travel operations platform</span>
        <span>Packages, reservations, customers, and reports</span>
    </footer>
    </body>
    </html>
    <?php
}

function csrf_field(): void
{
    ?>
    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
    <?php
}
