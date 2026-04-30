<?php
declare(strict_types=1);

require_once __DIR__ . '/app/layout.php';

$installed = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    try {
        $pdo = db(false);
        $sql = file_get_contents(__DIR__ . '/database/schema.sql');
        foreach (array_filter(array_map('trim', explode(';', (string) $sql))) as $statement) {
            $pdo->exec($statement);
        }
        $installed = true;
        flash('success', 'Database installed. Admin login: admin@way2go.test / admin123');
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

render_header('Setup');
?>
<section class="panel narrow auth-panel">
    <h1>Install <?= APP_NAME ?></h1>
    <p class="muted">This creates the MySQL database, normalized tables, sample packages, and the first admin account.</p>
    <?php if ($error): ?>
        <div class="flash danger"><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($installed): ?>
        <a class="button" href="<?= url('auth/login.php') ?>">Go to login</a>
    <?php else: ?>
        <form method="post">
            <?php csrf_field(); ?>
            <button class="button" type="submit">Install database</button>
        </form>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
