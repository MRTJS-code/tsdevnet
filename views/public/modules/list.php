<?php
use App\Support\Util;
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="list">
    <div class="section-heading">
        <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
    </div>
    <div class="card-rail">
        <?php foreach (($module['items'] ?? []) as $item): ?>
            <article class="rail-card">
                <h3><?= Util::e($item['item_title']) ?></h3>
                <?php if (!empty($item['item_meta'])): ?><p class="eyebrow"><?= Util::e($item['item_meta']) ?></p><?php endif; ?>
                <?php if (!empty($item['item_body'])): ?><p><?= Util::e($item['item_body']) ?></p><?php endif; ?>
                <?php if (!empty($item['link_url'])): ?><a class="btn ghost" href="<?= Util::e($item['link_url']) ?>"><?= Util::e($item['link_label'] ?: 'Open link') ?></a><?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
