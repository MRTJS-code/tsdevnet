<?php
use App\Support\Util;
$content = $module['content'] ?? [];
$media = $module['media'] ?? null;
$mediaFirst = ($content['media_position'] ?? 'right') === 'left';
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="media_text">
    <div class="hero__layout<?= $mediaFirst ? ' hero__layout--reverse' : '' ?>">
        <?php if ($mediaFirst): ?>
            <div class="hero-card">
                <div class="hero-card__media<?= empty($media['public_url']) ? ' hero-card__media--placeholder' : '' ?>">
                    <?php if (!empty($media['public_url'])): ?><img src="<?= Util::e($media['public_url']) ?>" alt="<?= Util::e($media['title'] ?? $module['title']) ?>"><?php else: ?><span>Media ready</span><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <article class="section-wrapper">
            <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
            <h2><?= Util::e($module['title']) ?></h2>
            <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
            <?php if (!empty($content['body_text'])): ?><p><?= Util::e($content['body_text']) ?></p><?php endif; ?>
            <div class="actions">
                <?php if (!empty($content['primary_cta']['url'])): ?><a class="btn primary" href="<?= Util::e($content['primary_cta']['url']) ?>"><?= Util::e($content['primary_cta']['label'] ?: 'Open') ?></a><?php endif; ?>
                <?php if (!empty($content['secondary_cta']['url'])): ?><a class="btn ghost" href="<?= Util::e($content['secondary_cta']['url']) ?>"><?= Util::e($content['secondary_cta']['label'] ?: 'Learn more') ?></a><?php endif; ?>
            </div>
        </article>
        <?php if (!$mediaFirst): ?>
            <div class="hero-card">
                <div class="hero-card__media<?= empty($media['public_url']) ? ' hero-card__media--placeholder' : '' ?>">
                    <?php if (!empty($media['public_url'])): ?><img src="<?= Util::e($media['public_url']) ?>" alt="<?= Util::e($media['title'] ?? $module['title']) ?>"><?php else: ?><span>Media ready</span><?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>
