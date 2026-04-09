<?php
use App\Support\Util;
$content = $module['content'] ?? [];
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="cta_banner">
    <article class="section-wrapper section-wrapper--bottom">
        <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
        <?php if (!empty($content['body_text'])): ?><p><?= Util::e($content['body_text']) ?></p><?php endif; ?>
        <div class="actions">
            <?php if (!empty($content['primary_cta']['url'])): ?><a class="btn primary" href="<?= Util::e($content['primary_cta']['url']) ?>"><?= Util::e($content['primary_cta']['label'] ?: 'Open') ?></a><?php endif; ?>
            <?php if (!empty($content['secondary_cta']['url'])): ?><a class="btn ghost" href="<?= Util::e($content['secondary_cta']['url']) ?>"><?= Util::e($content['secondary_cta']['label'] ?: 'Learn more') ?></a><?php endif; ?>
        </div>
    </article>
</section>
