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
        <?php foreach (($module['items'] ?? []) as $item): ?>
            <button class="rail-card rail-card--interactive" type="button" data-dialog-target="portfolio-<?= (int) $item['id'] ?>">
                <?php if (!empty($item['category'])): ?>
                    <p class="rail-card__eyebrow"><?= Util::e($item['category']) ?></p>
                <?php endif; ?>
                <h3><?= Util::e($item['title']) ?></h3>
                <?php if (!empty($item['summary'])): ?>
                    <p><?= Util::e($item['summary']) ?></p>
                <?php endif; ?>
                <?php if (!empty($item['outcome'])): ?>
                    <p class="rail-card__accent"><?= Util::e($item['outcome']) ?></p>
                <?php endif; ?>
            </button>
        <?php endforeach; ?>
    </div>
</section>

<?php foreach (($module['items'] ?? []) as $item): ?>
    <dialog class="modal-card" id="portfolio-<?= (int) $item['id'] ?>">
        <div class="modal-card__header">
            <div>
                <?php if (!empty($item['category'])): ?>
                    <p class="eyebrow"><?= Util::e($item['category']) ?></p>
                <?php endif; ?>
                <h2><?= Util::e($item['title']) ?></h2>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($item['summary'])): ?>
            <p class="lede"><?= Util::e($item['summary']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['problem_text'])): ?>
            <p><strong>Problem:</strong> <?= Util::e($item['problem_text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['approach_text'])): ?>
            <p><strong>Approach:</strong> <?= Util::e($item['approach_text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['outcome'])): ?>
            <p><strong>Outcome:</strong> <?= Util::e($item['outcome']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['tech_text'])): ?>
            <p><strong>Technology:</strong> <?= Util::e($item['tech_text']) ?></p>
        <?php endif; ?>
        <div class="actions">
            <?php if (!empty($item['demo_url'])): ?>
                <a class="btn primary" href="<?= Util::e($item['demo_url']) ?>" target="_blank" rel="noreferrer">View project</a>
            <?php endif; ?>
            <?php if (!empty($item['repo_url'])): ?>
                <a class="btn ghost" href="<?= Util::e($item['repo_url']) ?>" target="_blank" rel="noreferrer">View repository</a>
            <?php endif; ?>
            <?php if (empty($item['demo_url']) && !empty($item['link_url'])): ?>
                <a class="btn primary" href="<?= Util::e($item['link_url']) ?>" target="_blank" rel="noreferrer"><?= Util::e($item['link_label'] ?: 'View project') ?></a>
            <?php endif; ?>
        </div>
    </dialog>
<?php endforeach; ?>
