<?php use App\Support\Util; ?>
<section class="section"<?= !empty($module['anchor_id']) ? ' id="' . Util::e($module['anchor_id']) . '"' : '' ?>>
    <article class="section-wrapper section-wrapper--top">
        <?php if (!empty($module['eyebrow'])): ?>
            <p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p>
        <?php endif; ?>
        <div class="section-heading">
            <h2><?= Util::e($module['title']) ?></h2>
            <?php if (!empty($module['intro_text'])): ?>
                <p class="lede"><?= Util::e($module['intro_text']) ?></p>
            <?php endif; ?>
        </div>
        <?php if (!empty($module['content']['body_text'])): ?>
            <p class="lede"><?= Util::e($module['content']['body_text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($module['content']['cta']['label']) && !empty($module['content']['cta']['url'])): ?>
            <div class="actions">
                <a class="btn primary" href="<?= Util::e($module['content']['cta']['url']) ?>"><?= Util::e($module['content']['cta']['label']) ?></a>
                <?php if (!empty($module['content']['secondary_cta']['label']) && !empty($module['content']['secondary_cta']['url'])): ?>
                    <a class="btn ghost" href="<?= Util::e($module['content']['secondary_cta']['url']) ?>"><?= Util::e($module['content']['secondary_cta']['label']) ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </article>
</section>
