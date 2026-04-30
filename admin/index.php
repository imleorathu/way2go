<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
require_admin();

$stats = [
    'packages' => db()->query('SELECT COUNT(*) c FROM packages')->fetch()['c'],
    'bookings' => db()->query('SELECT COUNT(*) c FROM bookings')->fetch()['c'],
    'pending' => db()->query("SELECT COUNT(*) c FROM bookings WHERE status = 'pending'")->fetch()['c'],
    'customers' => db()->query("SELECT COUNT(*) c FROM users WHERE role = 'customer'")->fetch()['c'],
    'revenue' => db()->query("SELECT COALESCE(SUM(total_cost),0) c FROM bookings WHERE status = 'confirmed'")->fetch()['c'],
];
$popular = db()->query('SELECT p.destination, COUNT(*) bookings FROM bookings b JOIN packages p ON p.id = b.package_id GROUP BY p.destination ORDER BY bookings DESC LIMIT 5')->fetchAll();
$recent = db()->query('SELECT b.*, u.name, p.title FROM bookings b JOIN users u ON u.id = b.user_id JOIN packages p ON p.id = b.package_id ORDER BY b.created_at DESC LIMIT 5')->fetchAll();

render_header('Admin dashboard');
?>
<section class="admin-head">
    <div>
        <p class="eyebrow">Admin module</p>
        <h1>Operations dashboard</h1>
        <p class="muted">Monitor packages, booking requests, customers, and confirmed revenue from one place.</p>
    </div>
    <div class="actions">
        <a class="button" href="<?= url('admin/packages.php') ?>">Manage packages</a>
        <a class="button ghost" href="<?= url('admin/bookings.php') ?>">Review bookings</a>
    </div>
</section>
<section class="stats">
    <article><span>Total packages</span><strong><?= (int) $stats['packages'] ?></strong></article>
    <article><span>Total bookings</span><strong><?= (int) $stats['bookings'] ?></strong></article>
    <article><span>Pending review</span><strong><?= (int) $stats['pending'] ?></strong></article>
    <article><span>Confirmed revenue</span><strong><?= money($stats['revenue']) ?></strong></article>
</section>
<section class="dashboard-grid">
    <section class="panel">
        <h2>Popular destinations</h2>
        <?php foreach ($popular as $row): ?>
            <div class="bar-row"><span><?= e($row['destination']) ?></span><strong><?= (int) $row['bookings'] ?> bookings</strong></div>
        <?php endforeach; ?>
        <?php if (!$popular): ?><p class="empty">Bookings will appear here after customers reserve packages.</p><?php endif; ?>
    </section>
    <section class="panel">
        <h2>Recent bookings</h2>
        <?php foreach ($recent as $booking): ?>
            <div class="list-item compact">
                <div>
                    <strong><?= e($booking['title']) ?></strong>
                    <span class="muted"><?= e($booking['name']) ?> - <?= e($booking['travel_date']) ?></span>
                </div>
                <span class="status <?= package_status_badge($booking['status']) ?>"><?= e($booking['status']) ?></span>
            </div>
        <?php endforeach; ?>
        <?php if (!$recent): ?><p class="empty">New reservations will appear here.</p><?php endif; ?>
    </section>
</section>
<?php render_footer(); ?>
