<?php use App\Support\Util; ?>
<section class="section"<?= !empty($module['anchor_id']) ? ' id="' . Util::e($module['anchor_id']) . '"' : '' ?>>
    <div class="section-heading">
        <?php if (!empty($module['eyebrow'])): ?>
            <p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p>
        <?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?>
            <p class="lede"><?= Util::e($module['intro_text']) ?></p>
        <?php endif; ?>
    </div>
    <div class="card-rail">
        <?php foreach (($module['items'] ?? []) as $entry): ?>
            <button class="rail-card rail-card--interactive" type="button" data-dialog-target="experience-<?= (int) $entry['id'] ?>">
                <p class="rail-card__eyebrow"><?= Util::e($entry['period_label']) ?></p>
                <h3><?= Util::e($entry['role_title']) ?></h3>
                <p class="rail-card__meta"><?= Util::e($entry['organisation']) ?></p>
                <?php if (!empty($entry['summary'])): ?>
                    <p><?= Util::e($entry['summary']) ?></p>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
</section>

<?php foreach (($module['items'] ?? []) as $entry): ?>
    <dialog class="modal-card" id="experience-<?= (int) $entry['id'] ?>">
        <div class="modal-card__header">
            <div>
                <p class="eyebrow"><?= Util::e($entry['period_label']) ?></p>
                <h2><?= Util::e($entry['role_title']) ?></h2>
                <p class="rail-card__meta"><?= Util::e($entry['organisation']) ?></p>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($entry['summary'])): ?>
            <p class="lede"><?= Util::e($entry['summary']) ?></p>
        <?php endif; ?>
        <?php if (!empty($entry['highlights'])): ?>
            <ul class="feature-list">
                <?php foreach ($entry['highlights'] as $highlight): ?>
                    <li><?= Util::e($highlight) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </dialog>
<?php endforeach; ?>
