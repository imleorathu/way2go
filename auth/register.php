<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        flash('danger', 'Enter a valid name, email, and a password with at least 6 characters.');
    } else {
        try {
            $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, "customer")');
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int) db()->lastInsertId();
            flash('success', 'Welcome to Way2Go.');
            redirect('');
        } catch (PDOException $e) {
            flash('danger', 'That email is already registered.');
        }
    }
}

render_header('Register');
?>
<section class="panel narrow auth-panel">
    <h1>Create account</h1>
    <p class="muted">Register as a customer to reserve packages and track booking status.</p>
    <form method="post" class="stack">
        <?php csrf_field(); ?>
        <label>Name <input name="name" required></label>
        <label>Email <input name="email" type="email" required></label>
        <label>Password <input name="password" type="password" minlength="6" required></label>
        <button class="button" type="submit">Register</button>
    </form>
</section>
<?php render_footer(); ?>
