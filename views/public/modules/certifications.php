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
        <?php foreach (($module['items'] ?? []) as $entry): ?>
            <article class="card testimonial-card">
                <h3><?= Util::e($entry['certification_name']) ?></h3>
                <p><?= Util::e(trim(implode(' | ', array_filter([$entry['issuer'] ?? '', $entry['issued_label'] ?? ''])))) ?></p>
                <?php if (!empty($entry['credential_url'])): ?>
                    <p><a href="<?= Util::e($entry['credential_url']) ?>" target="_blank" rel="noreferrer">View credential</a></p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
