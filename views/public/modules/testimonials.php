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
    <div class="content-grid">
        <?php foreach (($module['items'] ?? []) as $item): ?>
            <article class="card testimonial-card">
                <p class="testimonial-card__quote">&ldquo;<?= Util::e($item['quote_text']) ?>&rdquo;</p>
                <p class="testimonial-card__byline"><?= Util::e($item['person_name']) ?></p>
                <p><?= Util::e(implode(' | ', array_filter([$item['person_title'] ?? '', $item['organisation'] ?? '']))) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
