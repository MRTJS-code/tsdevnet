<?php
use App\Support\Util;
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="quote_cards">
    <div class="section-heading">
        <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
    </div>
    <div class="card-rail">
        <?php foreach (($module['items'] ?? []) as $item): ?>
            <article class="rail-card testimonial-card">
                <p class="testimonial-card__quote">&ldquo;<?= Util::e($item['quote_text']) ?>&rdquo;</p>
                <p class="testimonial-card__byline"><?= Util::e($item['attribution_name']) ?></p>
                <p><?= Util::e(implode(' | ', array_filter([$item['attribution_role'] ?? '', $item['attribution_context'] ?? '']))) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
