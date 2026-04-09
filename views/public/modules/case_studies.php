<?php
use App\Support\Util;
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="case_studies">
    <div class="section-heading">
        <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
    </div>
    <div class="card-rail">
        <?php foreach (($module['items'] ?? []) as $item): ?>
            <article class="rail-card">
                <?php if (!empty($item['category_text'])): ?><p class="eyebrow"><?= Util::e($item['category_text']) ?></p><?php endif; ?>
                <h3><?= Util::e($item['title']) ?></h3>
                <?php if (!empty($item['summary_text'])): ?><p><?= Util::e($item['summary_text']) ?></p><?php endif; ?>
                <?php if (!empty($item['outcome_text'])): ?><p class="rail-card__accent"><?= Util::e($item['outcome_text']) ?></p><?php endif; ?>
                <?php if (!empty($item['detail_text'])): ?><p><?= Util::e($item['detail_text']) ?></p><?php endif; ?>
                <?php if (!empty($item['link_url'])): ?><a class="btn ghost" href="<?= Util::e($item['link_url']) ?>"><?= Util::e($item['link_label'] ?: 'View case study') ?></a><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
