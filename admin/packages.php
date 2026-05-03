<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/layout.php';
require_admin();

$editing = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM packages WHERE id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch();
    if (!$editing) {
        flash('danger', 'Package not found.');
        redirect('admin/packages.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (($_POST['action'] ?? '') === 'set_status') {
        $isActive = (int) ($_POST['is_active'] ?? 0) === 1 ? 1 : 0;
        $stmt = db()->prepare('UPDATE packages SET is_active = ? WHERE id = ?');
        $stmt->execute([$isActive, (int) $_POST['id']]);
        flash('success', $isActive === 1 ? 'Package activated.' : 'Package deactivated.');
        redirect('admin/packages.php');
    }

    $id = (int) ($_POST['id'] ?? 0);
    $existing = null;
    if ($id > 0) {
        $stmt = db()->prepare('SELECT * FROM packages WHERE id = ?');
        $stmt->execute([$id]);
        $existing = $stmt->fetch();
        if (!$existing) {
            flash('danger', 'Package not found.');
            redirect('admin/packages.php');
        }
    }

    $photoPath = null;
    try {
        $photoPath = save_package_photo($_FILES['photo'] ?? []);
    } catch (RuntimeException $e) {
        flash('danger', $e->getMessage());
        redirect('admin/packages.php' . ($id > 0 ? '?edit=' . $id : ''));
    }

    $themes = ['coast','hills','heritage','wild'];
    $data = [
        trim($_POST['title'] ?? ''),
        trim($_POST['destination'] ?? ''),
        trim($_POST['description'] ?? ''),
        array_key_exists($_POST['audience'] ?? '', package_audiences()) ? $_POST['audience'] : 'individual',
        max(1, (int) ($_POST['duration_days'] ?? 1)),
        max(0, (float) ($_POST['price'] ?? 0)),
        max(0, (int) ($_POST['seats_available'] ?? 0)),
        in_array($_POST['image_theme'] ?? '', $themes, true) ? $_POST['image_theme'] : 'coast',
        isset($_POST['is_active']) ? 1 : 0,
    ];

    if ($data[0] === '' || $data[1] === '' || $data[2] === '') {
        flash('danger', 'Title, destination, and description are required.');
        if ($id > 0) {
            $editing = array_merge($existing, [
                'title' => $data[0],
                'destination' => $data[1],
                'description' => $data[2],
                'audience' => $data[3],
                'duration_days' => $data[4],
                'price' => $data[5],
                'seats_available' => $data[6],
                'image_theme' => $data[7],
                'is_active' => $data[8],
            ]);
        }
    } elseif ($id > 0) {
        $newPhotoPath = $existing['photo_path'] ?? null;
        if ($photoPath !== null) {
            delete_package_photo($newPhotoPath);
            $newPhotoPath = $photoPath;
        } elseif (isset($_POST['remove_photo'])) {
            delete_package_photo($newPhotoPath);
            $newPhotoPath = null;
        }

        if ($newPhotoPath !== ($existing['photo_path'] ?? null)) {
            $stmt = db()->prepare('UPDATE packages SET title=?, destination=?, description=?, audience=?, duration_days=?, price=?, seats_available=?, image_theme=?, is_active=?, photo_path=? WHERE id=?');
            $stmt->execute([...$data, $newPhotoPath, $id]);
        } else {
            $stmt = db()->prepare('UPDATE packages SET title=?, destination=?, description=?, audience=?, duration_days=?, price=?, seats_available=?, image_theme=?, is_active=? WHERE id=?');
            $stmt->execute([...$data, $id]);
        }
        flash('success', 'Package updated.');
        redirect('admin/packages.php');
    } else {
        $stmt = db()->prepare('INSERT INTO packages (title, destination, description, audience, duration_days, price, seats_available, image_theme, is_active, photo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([...$data, $photoPath]);
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
        <?php if ($editing): ?>
            <p class="edit-notice">Editing existing package: <strong><?= e($editing['title']) ?></strong></p>
        <?php endif; ?>
        <form method="post" class="stack" enctype="multipart/form-data" action="<?= url('admin/packages.php' . ($editing ? '?edit=' . $editing['id'] : '')) ?>">
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
            <label>Package photo
                <input name="photo" type="file" accept="image/jpeg,image/png,image/webp">
            </label>
            <?php $editingPhotoUrl = package_photo_url($editing['photo_path'] ?? null); ?>
            <?php if ($editingPhotoUrl): ?>
                <img class="photo-preview" src="<?= e($editingPhotoUrl) ?>" alt="<?= e($editing['title'] ?? 'Package photo') ?>">
                <label class="check"><input type="checkbox" name="remove_photo"> Remove current photo</label>
            <?php endif; ?>
            <label class="check"><input type="checkbox" name="is_active" <?= (int) ($editing['is_active'] ?? 1) === 1 ? 'checked' : '' ?>> Active</label>
            <div class="actions">
                <button class="button" type="submit"><?= $editing ? 'Update package' : 'Add package' ?></button>
                <?php if ($editing): ?>
                    <a class="button ghost" href="<?= url('admin/packages.php') ?>">Cancel</a>
                <?php endif; ?>
            </div>
        </form>
    </section>
    <section class="panel">
        <h2>Already added packages</h2>
        <?php foreach ($packages as $package): ?>
            <div class="package-admin-card <?= $editing && (int) $editing['id'] === (int) $package['id'] ? 'selected-item' : '' ?>">
                <div>
                    <strong><?= e($package['title']) ?></strong>
                    <span class="status <?= (int) $package['is_active'] === 1 ? 'success' : 'danger' ?>"><?= (int) $package['is_active'] === 1 ? 'Active' : 'Inactive' ?></span>
                </div>
                <div class="row-actions">
                    <a class="button small ghost" href="<?= url('admin/packages.php?edit=' . $package['id']) ?>">Edit</a>
                    <form method="post" class="inline">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="set_status">
                        <input type="hidden" name="id" value="<?= (int) $package['id'] ?>">
                        <?php if ((int) $package['is_active'] === 1): ?>
                            <input type="hidden" name="is_active" value="0">
                            <button class="link-button danger-text" type="submit" data-confirm="Deactivate this package?">Deactivate</button>
                        <?php else: ?>
                            <input type="hidden" name="is_active" value="1">
                            <button class="link-button success-text" type="submit">Activate</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </section>
</section>
<?php render_footer(); ?>
