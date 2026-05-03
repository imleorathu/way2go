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
        <meta name="application-name" content="<?= APP_NAME ?>">
        <meta name="apple-mobile-web-app-title" content="<?= APP_NAME ?>">
        <title><?= e($title) ?> | <?= APP_NAME ?></title>
        <link rel="icon" type="image/png" href="<?= asset_url('way2go-icon.png') ?>">
        <link rel="apple-touch-icon" href="<?= asset_url('way2go-icon.png') ?>">
        <link rel="stylesheet" href="<?= asset_url('app.css') ?>">
        <script defer src="<?= asset_url('app.js') ?>"></script>
    </head>
    <body>
    <header class="topbar">
        <a class="brand" href="<?= url() ?>">
            <img class="brand-logo" src="<?= asset_url('way2go-icon.png') ?>" alt="<?= APP_NAME ?> icon">
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
        <div class="footer-brand">
            <a class="brand" href="<?= url() ?>">
                <img class="brand-logo" src="<?= asset_url('way2go-icon.png') ?>" alt="<?= APP_NAME ?> icon">
                <span><?= APP_NAME ?></span>
            </a>
            <p>Curated Sri Lanka travel packages, reservations, and customer support in one simple booking platform.</p>
        </div>
        <div class="footer-links">
            <h2>Follow us</h2>
            <div class="social-links" aria-label="Social media links">
                <a href="https://instagram.com" target="_blank" rel="noopener">Instagram</a>
                <a href="https://facebook.com" target="_blank" rel="noopener">Facebook</a>
                <a href="https://tiktok.com" target="_blank" rel="noopener">TikTok</a>
                <a href="https://youtube.com" target="_blank" rel="noopener">YouTube</a>
            </div>
        </div>
        <div class="footer-links">
            <h2>Quick links</h2>
            <a href="<?= url() ?>">Packages</a>
            <a href="<?= url('auth/login.php') ?>">Login</a>
            <a href="<?= url('auth/register.php') ?>">Register</a>
        </div>
        <div class="footer-links">
            <h2>Contact</h2>
            <span>Colombo, Sri Lanka</span>
            <a href="mailto:hello@way2go.test">hello@way2go.test</a>
            <a href="tel:+94778164677">+94 77 816 4677</a>
        </div>
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
