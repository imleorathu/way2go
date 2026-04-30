<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
require_admin();

$users = db()->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
render_header('Users');
?>
<section class="admin-head">
    <div>
        <p class="eyebrow">Accounts</p>
        <h1>Users</h1>
        <p class="muted">View customer and admin accounts registered in the system.</p>
    </div>
</section>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th></tr></thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= e($user['name']) ?></td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="status <?= $user['role'] === 'admin' ? 'warning' : 'success' ?>"><?= e($user['role']) ?></span></td>
                    <td><?= e($user['created_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php render_footer(); ?>
