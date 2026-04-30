<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
require_admin();

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM packages WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (($_POST['action'] ?? '') === 'deactivate') {
        $stmt = db()->prepare('UPDATE packages SET is_active = 0 WHERE id = ?');
        $stmt->execute([(int) $_POST['id']]);
        flash('success', 'Package deactivated.');
        redirect('admin/packages.php');
    }

    $data = [
        trim($_POST['title'] ?? ''),
        trim($_POST['destination'] ?? ''),
        trim($_POST['description'] ?? ''),
        array_key_exists($_POST['audience'] ?? '', package_audiences()) ? $_POST['audience'] : 'individual',
        max(1, (int) ($_POST['duration_days'] ?? 1)),
        max(0, (float) ($_POST['price'] ?? 0)),
        max(0, (int) ($_POST['seats_available'] ?? 0)),
        $_POST['image_theme'] ?? 'coast',
        isset($_POST['is_active']) ? 1 : 0,
    ];

    if ($data[0] === '' || $data[1] === '' || $data[2] === '') {
        flash('danger', 'Title, destination, and description are required.');
    } elseif (!empty($_POST['id'])) {
        $stmt = db()->prepare('UPDATE packages SET title=?, destination=?, description=?, audience=?, duration_days=?, price=?, seats_available=?, image_theme=?, is_active=? WHERE id=?');
        $stmt->execute([...$data, (int) $_POST['id']]);
        flash('success', 'Package updated.');
        redirect('admin/packages.php');
    } else {
        $stmt = db()->prepare('INSERT INTO packages (title, destination, description, audience, duration_days, price, seats_available, image_theme, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute($data);
        flash('success', 'Package added.');
        redirect('admin/packages.php');
    }
}

$packages = db()->query('SELECT * FROM packages ORDER BY created_at DESC')->fetchAll();
render_header('Manage packages');
?>
<section class="admin-head">
    <div>
        <p class="eyebrow">Package catalog</p>
        <h1>Manage travel packages</h1>
        <p class="muted">Create, update, and deactivate packages shown to customers.</p>
    </div>
</section>
<section class="split">
    <section class="panel">
        <h1><?= $editing ? 'Edit package' : 'Add package' ?></h1>
        <form method="post" class="stack">
            <?php csrf_field(); ?>
            <input type="hidden" name="id" value="<?= e($editing['id'] ?? '') ?>">
            <label>Title <input name="title" value="<?= e($editing['title'] ?? '') ?>" required></label>
            <label>Destination <input name="destination" value="<?= e($editing['destination'] ?? '') ?>" required></label>
            <label>Package type
                <select name="audience">
                    <?php foreach (package_audiences() as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= ($editing['audience'] ?? 'individual') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Description <textarea name="description" rows="4" required><?= e($editing['description'] ?? '') ?></textarea></label>
            <div class="two">
                <label>Days <input name="duration_days" type="number" min="1" value="<?= e($editing['duration_days'] ?? '3') ?>" required></label>
                <label>Seats <input name="seats_available" type="number" min="0" value="<?= e($editing['seats_available'] ?? '10') ?>" required></label>
            </div>
            <label>Price <input name="price" type="number" min="0" step="0.01" value="<?= e($editing['price'] ?? '') ?>" required></label>
            <label>Visual theme
                <select name="image_theme">
                    <?php foreach (['coast','hills','heritage','wild'] as $theme): ?>
                        <option value="<?= $theme ?>" <?= ($editing['image_theme'] ?? '') === $theme ? 'selected' : '' ?>><?= ucfirst($theme) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="check"><input type="checkbox" name="is_active" <?= (int) ($editing['is_active'] ?? 1) === 1 ? 'checked' : '' ?>> Active</label>
            <button class="button" type="submit"><?= $editing ? 'Save changes' : 'Add package' ?></button>
        </form>
    </section>
    <section class="panel">
        <h2>Packages</h2>
        <?php foreach ($packages as $package): ?>
            <div class="list-item">
                <div>
                    <strong><?= e($package['title']) ?></strong>
                    <span class="muted"><?= e($package['destination']) ?> - <?= e(package_audience_label($package['audience'] ?? null)) ?> - <?= money($package['price']) ?></span>
                </div>
                <div class="row-actions">
                    <a href="<?= url('admin/packages.php?edit=' . $package['id']) ?>">Edit</a>
                    <form method="post" class="inline">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="deactivate">
                        <input type="hidden" name="id" value="<?= (int) $package['id'] ?>">
                        <button class="link-button" type="submit" data-confirm="Deactivate this package?">Deactivate</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</section>
<?php render_footer(); ?>
