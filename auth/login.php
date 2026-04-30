<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = (int) $user['id'];
        flash('success', 'Signed in successfully.');
        redirect($user['role'] === 'admin' ? 'admin/index.php' : '');
    }
    flash('danger', 'Invalid email or password.');
}

render_header('Login');
?>
<section class="panel narrow auth-panel">
    <h1>Login</h1>
    <p class="muted">Admin demo: admin@way2go.test / admin123</p>
    <form method="post" class="stack">
        <?php csrf_field(); ?>
        <label>Email <input name="email" type="email" required></label>
        <label>Password <input name="password" type="password" required></label>
        <button class="button" type="submit">Login</button>
    </form>
</section>
<?php render_footer(); ?>
