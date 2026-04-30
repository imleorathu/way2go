<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Security check failed. Please go back and try again.');
    }
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $user = null;
    if ($user !== null) {
        return $user;
    }

    $stmt = db()->prepare('SELECT id, name, email, role, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('warning', 'Please sign in to continue.');
        redirect('auth/login.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Admins only.');
    }
    return $user;
}

function money(float|int|string $amount): string
{
    return 'LKR ' . number_format((float) $amount, 2);
}

function package_status_badge(string $status): string
{
    return match ($status) {
        'confirmed' => 'success',
        'cancelled' => 'danger',
        default => 'warning',
    };
}

function package_audiences(): array
{
    return [
        'individual' => 'Individuals',
        'couple' => 'Couples',
        'group' => 'Groups',
    ];
}

function package_audience_label(?string $audience): string
{
    return package_audiences()[$audience ?? ''] ?? 'Individuals';
}
