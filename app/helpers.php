<?php
declare(strict_types=1);

require_once __DIR__ . '/database.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function public_path(string $path): string
{
    return dirname(__DIR__) . '/public/' . ltrim($path, '/');
}

function asset_url(string $path): string
{
    $url = url('public/' . ltrim($path, '/'));
    $file = public_path($path);

    if (is_file($file)) {
        $url .= '?v=' . filemtime($file);
    }

    return $url;
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

function package_photo_url(?string $photoPath): ?string
{
    if (!$photoPath) {
        return null;
    }

    $file = dirname(__DIR__) . '/' . ltrim($photoPath, '/');
    if (!is_file($file)) {
        return null;
    }

    return url($photoPath);
}

function save_package_photo(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The package photo could not be uploaded.');
    }

    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Package photos must be 2MB or smaller.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmpName);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Upload a JPG, PNG, or WebP package photo.');
    }

    $uploadDir = dirname(__DIR__) . '/public/uploads/packages';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $destination = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $destination)) {
        throw new RuntimeException('The package photo could not be saved.');
    }

    return 'public/uploads/packages/' . $filename;
}

function delete_package_photo(?string $photoPath): void
{
    if (!$photoPath || !str_starts_with($photoPath, 'public/uploads/packages/')) {
        return;
    }

    $path = dirname(__DIR__) . '/' . $photoPath;
    if (is_file($path)) {
        unlink($path);
    }
}
