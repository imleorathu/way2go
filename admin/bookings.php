<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('UPDATE bookings SET status = ? WHERE id = ?');
    $stmt->execute([$_POST['status'], (int) $_POST['booking_id']]);
    flash('success', 'Booking status updated.');
    redirect('admin/bookings.php');
}

$bookings = db()->query('SELECT b.*, u.name, u.email, p.title, p.destination FROM bookings b JOIN users u ON u.id = b.user_id JOIN packages p ON p.id = b.package_id ORDER BY b.created_at DESC')->fetchAll();
render_header('Manage bookings');
?>
<section class="admin-head">
    <div>
        <p class="eyebrow">Reservations</p>
        <h1>Booking requests</h1>
        <p class="muted">Review customer reservations and keep each request status up to date.</p>
    </div>
</section>
<section class="panel">
    <div class="table-wrap">
        <table>
            <thead><tr><th>Customer</th><th>Package</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= e($booking['name']) ?><br><span class="muted"><?= e($booking['email']) ?></span></td>
                    <td><?= e($booking['title']) ?><br><span class="muted"><?= e($booking['destination']) ?> - <?= (int) $booking['travelers'] ?> travelers</span></td>
                    <td><?= e($booking['travel_date']) ?></td>
                    <td><?= money($booking['total_cost']) ?></td>
                    <td><span class="status <?= package_status_badge($booking['status']) ?>"><?= e($booking['status']) ?></span></td>
                    <td>
                        <form method="post" class="inline">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                            <select name="status">
                                <?php foreach (['pending','confirmed','cancelled'] as $status): ?>
                                    <option value="<?= $status ?>" <?= $booking['status'] === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button class="button small" type="submit">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (!$bookings): ?><p class="empty">No booking requests yet.</p><?php endif; ?>
</section>
<?php render_footer(); ?>
