<?php
use App\Support\Security;
use App\Support\Util;
?>
<header class="app-header">
    <div>
        <p class="eyebrow">Assistant</p>
        <h1><?= !empty($entry['id']) ? 'Edit knowledge rule' : 'New knowledge rule' ?></h1>
    </div>
    <div class="header-actions">
        <a class="btn ghost" href="/admin/assistant-knowledge.php">Back</a>
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
            <label>Knowledge key
                <input type="text" name="knowledge_key" required value="<?= Util::e($entry['knowledge_key']) ?>">
            </label>
            <label>Trigger type
                <select name="trigger_type" required>
                    <?php foreach (['contains', 'exact'] as $triggerType): ?>
                        <option value="<?= Util::e($triggerType) ?>" <?= $entry['trigger_type'] === $triggerType ? 'selected' : '' ?>><?= Util::e($triggerType) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Trigger value
                <input type="text" name="trigger_value" required value="<?= Util::e($entry['trigger_value']) ?>">
            </label>
            <label>Response text
                <textarea name="response_text" required><?= Util::e($entry['response_text']) ?></textarea>
            </label>
            <label>Minimum access tier
                <select name="minimum_access_tier" required>
                    <?php foreach (['pending', 'approved'] as $tier): ?>
                        <option value="<?= Util::e($tier) ?>" <?= $entry['minimum_access_tier'] === $tier ? 'selected' : '' ?>><?= Util::e($tier) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Priority
                <input type="number" name="priority" value="<?= (int) $entry['priority'] ?>">
            </label>
            <label class="checkbox">
                <input type="checkbox" name="is_active" value="1" <?= !empty($entry['is_active']) ? 'checked' : '' ?>>
                <span>Active</span>
            </label>
            <button class="btn primary" type="submit">Save rule</button>
        </form>
    </section>
</main>
