<?php
use App\Support\Util;
?>
<section class="section" id="<?= Util::e($module['anchor_id'] ?: ('module-' . $module['id'])) ?>" data-module-key="<?= Util::e($module['module_key']) ?>" data-module-type="timeline">
    <div class="section-heading">
        <?php if (!empty($module['eyebrow'])): ?><p class="eyebrow"><?= Util::e($module['eyebrow']) ?></p><?php endif; ?>
        <h2><?= Util::e($module['title']) ?></h2>
        <?php if (!empty($module['intro_text'])): ?><p class="lede"><?= Util::e($module['intro_text']) ?></p><?php endif; ?>
    </div>
    <div class="card-rail">
        <?php foreach (($module['items'] ?? []) as $index => $item): ?>
            <details class="rail-card rail-card--interactive" <?= $index === 0 ? 'open' : '' ?> data-testid="timeline-item">
                <summary>
                    <?php if (!empty($item['meta'])): ?><p class="eyebrow"><?= Util::e($item['meta']) ?></p><?php endif; ?>
                    <h3><?= Util::e($item['title']) ?></h3>
                    <?php if (!empty($item['subtitle'])): ?><p><?= Util::e($item['subtitle']) ?></p><?php endif; ?>
                </summary>
                <?php if (!empty($item['summary_text'])): ?><p><?= Util::e($item['summary_text']) ?></p><?php endif; ?>
                <?php if (!empty($item['detail_text'])): ?><p><?= Util::e($item['detail_text']) ?></p><?php endif; ?>
                <?php if (!empty($item['highlights'])): ?>
                    <ul class="feature-list">
                        <?php foreach ($item['highlights'] as $highlight): ?>
                            <li><?= Util::e($highlight) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
    </div>
</section>
