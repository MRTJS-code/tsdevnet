<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Homepage CMS</p>
        <h1><?= !empty($entry['id']) ? 'Edit testimonial' : 'New testimonial' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/homepage-testimonials.php">Back</a>
    </div>
</header>

<main class="admin-shell">
    <?php if (!empty($errors)): ?>
        <div class="notice error">
            <?php foreach ($errors as $error): ?>
                <p><?= Util::e($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <section class="card">
        <form method="post" class="form">
            <input type="hidden" name="csrf_token" value="<?= Util::e(Security::csrfToken()) ?>">
            <label>Quote text
                <textarea name="quote_text"><?= Util::e($entry['quote_text']) ?></textarea>
            </label>
            <label>Person name
                <input type="text" name="person_name" value="<?= Util::e($entry['person_name']) ?>" required>
            </label>
            <label>Person title
                <input type="text" name="person_title" value="<?= Util::e($entry['person_title']) ?>">
            </label>
            <label>Organisation
                <input type="text" name="organisation" value="<?= Util::e($entry['organisation']) ?>">
            </label>
            <label>Sort order
                <input type="number" name="sort_order" value="<?= (int) $entry['sort_order'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save testimonial</button>
        </form>
    </section>
</main>
