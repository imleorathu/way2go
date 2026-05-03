<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
$user = require_login();

$id = (int) ($_GET['id'] ?? $_POST['package_id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM packages WHERE id = ? AND is_active = 1');
$stmt->execute([$id]);
$package = $stmt->fetch();
if (!$package) {
    http_response_code(404);
    exit('Package not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $travelDate = $_POST['travel_date'] ?? '';
    $travelers = max(1, (int) ($_POST['travelers'] ?? 1));
    $notes = trim($_POST['notes'] ?? '');

    if (!$travelDate || strtotime($travelDate) < strtotime(date('Y-m-d'))) {
        flash('danger', 'Choose a valid future travel date.');
    } elseif ($travelers > (int) $package['seats_available']) {
        flash('danger', 'Not enough seats are available for this package.');
    } else {
        $total = $travelers * (float) $package['price'];
        $stmt = db()->prepare('INSERT INTO bookings (user_id, package_id, travel_date, travelers, total_cost, notes) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([$user['id'], $package['id'], $travelDate, $travelers, $total, $notes ?: null]);
        flash('success', 'Booking request submitted.');
        redirect('customer/bookings.php');
    }
}

render_header('Book package');
?>
<section class="split">
    <article class="package-card <?= e($package['image_theme']) ?>">
        <?php $photoUrl = package_photo_url($package['photo_path'] ?? null); ?>
        <?php if ($photoUrl): ?>
            <img class="package-photo large" src="<?= e($photoUrl) ?>" alt="<?= e($package['title']) ?>">
        <?php else: ?>
            <div class="package-art" aria-hidden="true"></div>
        <?php endif; ?>
        <div class="package-body">
            <div class="pill-row">
                <span class="pill"><?= e($package['destination']) ?></span>
                <span class="pill soft"><?= e(package_audience_label($package['audience'] ?? null)) ?></span>
            </div>
            <h1><?= e($package['title']) ?></h1>
            <p><?= e($package['description']) ?></p>
            <div class="meta package-meta">
                <span><?= (int) $package['duration_days'] ?> days</span>
                <span><?= (int) $package['seats_available'] ?> seats</span>
                <strong><?= money($package['price']) ?> / person</strong>
            </div>
        </div>
    </article>
    <section class="panel booking-panel">
        <h2>Reservation details</h2>
        <p class="muted">Submit your booking request. An admin can confirm or cancel it from the dashboard.</p>
        <form method="post" class="stack" data-booking-form data-price="<?= e($package['price']) ?>">
            <?php csrf_field(); ?>
            <input type="hidden" name="package_id" value="<?= (int) $package['id'] ?>">
            <label>Travel date <input name="travel_date" type="date" min="<?= date('Y-m-d') ?>" required></label>
            <label>Travelers <input name="travelers" type="number" min="1" max="<?= (int) $package['seats_available'] ?>" value="1" required></label>
            <label>Notes <textarea name="notes" rows="4" placeholder="Pickup needs, meal notes, or questions"></textarea></label>
            <div class="total">Estimated total: <strong data-total><?= money($package['price']) ?></strong></div>
            <button class="button" type="submit">Submit booking request</button>
        </form>
    </section>
</section>
<?php render_footer(); ?>
