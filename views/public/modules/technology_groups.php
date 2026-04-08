<?php use App\Support\Util; ?>
<section class="section"<?= !empty($module['anchor_id']) ? ' id="' . Util::e($module['anchor_id']) . '"' : '' ?>>
    <article class="section-wrapper section-wrapper--middle">
        <?php if (!empty($module['eyebrow'])): ?>
            <p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p>
        <?php endif; ?>
        <div class="section-heading">
            <h2><?= Util::e($module['title']) ?></h2>
            <?php if (!empty($module['intro_text'])): ?>
                <p class="lede"><?= Util::e($module['intro_text']) ?></p>
            <?php endif; ?>
        </div>
        <div class="card-rail">
            <?php foreach (($module['items'] ?? []) as $group): ?>
                <button class="rail-card rail-card--interactive" type="button" data-dialog-target="capability-<?= Util::e($group['group_key']) ?>">
                    <p class="rail-card__eyebrow">Capability</p>
                    <h3><?= Util::e($group['title']) ?></h3>
                    <?php if (!empty($group['intro_text'])): ?>
                        <p><?= Util::e($group['intro_text']) ?></p>
                    <?php endif; ?>
                    <ul class="feature-list feature-list--compact">
                        <?php foreach (array_slice($group['items'] ?? [], 0, 4) as $preview): ?>
                            <li><?= Util::e($preview['label']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </button>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<?php foreach (($module['items'] ?? []) as $group): ?>
    <dialog class="modal-card" id="capability-<?= Util::e($group['group_key']) ?>">
        <div class="modal-card__header">
            <div>
                <p class="eyebrow">Capability</p>
                <h2><?= Util::e($group['title']) ?></h2>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($group['intro_text'])): ?>
            <p class="lede"><?= Util::e($group['intro_text']) ?></p>
        <?php endif; ?>
        <ul class="feature-list">
            <?php foreach (($group['items'] ?? []) as $detail): ?>
                <li>
                    <?= Util::e($detail['label'] ?? '') ?>
                    <?php if (!empty($detail['detail_text'])): ?>
                        <span class="modal-inline-note"> - <?= Util::e($detail['detail_text']) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </dialog>
<?php endforeach; ?>
