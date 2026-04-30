<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
$user = require_login();

$stmt = db()->prepare('SELECT b.*, p.title, p.destination FROM bookings b JOIN packages p ON p.id = b.package_id WHERE b.user_id = ? ORDER BY b.created_at DESC');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();

render_header('My bookings');
?>
<section class="admin-head">
    <div>
        <p class="eyebrow">Customer area</p>
        <h1>My bookings</h1>
        <p class="muted">Track your submitted travel requests and confirmation status.</p>
    </div>
    <a class="button ghost" href="<?= url() ?>">Browse packages</a>
</section>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Package</th><th>Date</th><th>Travelers</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= e($booking['title']) ?><br><span class="muted"><?= e($booking['destination']) ?></span></td>
                    <td><?= e($booking['travel_date']) ?></td>
                    <td><?= (int) $booking['travelers'] ?></td>
                    <td><?= money($booking['total_cost']) ?></td>
                    <td><span class="status <?= package_status_badge($booking['status']) ?>"><?= e($booking['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$bookings): ?><p class="empty">You have not made a booking yet.</p><?php endif; ?>
</section>
<?php render_footer(); ?>
