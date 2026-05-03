<?php
declare(strict_types=1);

require_once __DIR__ . '/app/layout.php';

if (!database_ready()) {
    render_header('Setup required');
    ?>
    <section class="hero">
        <div>
            <p class="eyebrow">Travel Agency Web System</p>
            <h1>Way2Go needs a database before bookings can start.</h1>
            <p>Install the schema, sample packages, and admin user in one step.</p>
            <a class="button" href="<?= url('setup.php') ?>">Run setup</a>
        </div>
    </section>
    <?php
    render_footer();
    exit;
}

$destination = trim($_GET['destination'] ?? '');
$maxPrice = trim($_GET['max_price'] ?? '');
$audience = trim($_GET['audience'] ?? '');

$sql = 'SELECT * FROM packages WHERE is_active = 1';
$params = [];
if ($destination !== '') {
    $sql .= ' AND destination LIKE ?';
    $params[] = '%' . $destination . '%';
}
if ($maxPrice !== '' && is_numeric($maxPrice)) {
    $sql .= ' AND price <= ?';
    $params[] = $maxPrice;
}
if ($audience !== '' && array_key_exists($audience, package_audiences())) {
    $sql .= ' AND audience = ?';
    $params[] = $audience;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = db()->prepare($sql);
$stmt->execute($params);
$packages = $stmt->fetchAll();
$summary = [
    'packages' => db()->query('SELECT COUNT(*) FROM packages WHERE is_active = 1')->fetchColumn(),
    'destinations' => db()->query('SELECT COUNT(DISTINCT destination) FROM packages WHERE is_active = 1')->fetchColumn(),
    'seats' => db()->query('SELECT COALESCE(SUM(seats_available),0) FROM packages WHERE is_active = 1')->fetchColumn(),
];

render_header('Packages');
?>
<section class="hero travel-visual">
    <div>
        <p class="eyebrow">Sri Lanka travel desk</p>
        <h1>Plan memorable trips with a cleaner booking workflow.</h1>
        <p>Explore curated packages, compare trip details, and reserve seats through a secure travel management system.</p>
        <div class="hero-metrics">
            <span><strong><?= (int) $summary['packages'] ?></strong> active packages</span>
            <span><strong><?= (int) $summary['destinations'] ?></strong> destinations</span>
            <span><strong><?= (int) $summary['seats'] ?></strong> seats available</span>
        </div>
    </div>
</section>

<section class="section-head">
    <div>
        <p class="eyebrow">Browse packages</p>
        <h2>Find the right destination faster</h2>
    </div>
    <p>Use destination, package type, and budget filters to narrow the catalog before booking.</p>
</section>

<section class="toolbar">
    <form class="filters" method="get">
        <label>
            Destination
            <input name="destination" value="<?= e($destination) ?>" placeholder="Galle, Yala, Nuwara Eliya">
        </label>
        <label>
            Package type
            <select name="audience">
                <option value="">All types</option>
                <?php foreach (package_audiences() as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= $audience === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>
            Max price
            <input name="max_price" type="number" min="0" step="1000" value="<?= e($maxPrice) ?>" placeholder="75000">
        </label>
        <button class="button" type="submit">Filter packages</button>
        <a class="button ghost" href="<?= url() ?>">Clear</a>
    </form>
</section>

<section class="grid">
    <?php foreach ($packages as $package): ?>
        <article class="package-card <?= e($package['image_theme']) ?>">
            <?php $photoUrl = package_photo_url($package['photo_path'] ?? null); ?>
            <?php if ($photoUrl): ?>
                <img class="package-photo" src="<?= e($photoUrl) ?>" alt="<?= e($package['title']) ?>">
            <?php else: ?>
                <div class="package-art" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="package-body">
                <div class="pill-row">
                    <span class="pill"><?= e($package['destination']) ?></span>
                    <span class="pill soft"><?= e(package_audience_label($package['audience'] ?? null)) ?></span>
                </div>
                <h2><?= e($package['title']) ?></h2>
                <p><?= e($package['description']) ?></p>
                <div class="meta package-meta">
                    <span><?= (int) $package['duration_days'] ?> days</span>
                    <span><?= (int) $package['seats_available'] ?> seats</span>
                    <strong><?= money($package['price']) ?></strong>
                </div>
                <a class="button full" href="<?= url('customer/book.php?id=' . $package['id']) ?>">Book package</a>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if (!$packages): ?>
        <p class="empty">No packages matched your filters.</p>
    <?php endif; ?>
</section>
<?php render_footer(); ?>
