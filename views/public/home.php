<?php
use App\Support\Util;

$hero = $homepage['hero'];
$summaryCards = $homepage['summary_cards'];
$about = $homepage['about'];
$achievements = $homepage['achievements'];
$technologyTags = $homepage['technology_tags'];
$operatingApproach = $homepage['operating_approach'];
$contact = $homepage['contact_cta'];
?>
<main class="site-shell">
    <section class="hero">
        <div class="hero__content">
            <?php if (!empty($hero['meta']['eyebrow'])): ?>
                <p class="eyebrow"><?= Util::e($hero['meta']['eyebrow']) ?></p>
            <?php endif; ?>
            <h1><?= Util::e($hero['title']) ?></h1>
            <?php if (!empty($hero['subtitle'])): ?>
                <p class="lede"><?= Util::e($hero['subtitle']) ?></p>
            <?php endif; ?>
            <?php if (!empty($hero['body_text'])): ?>
                <p class="lede"><?= Util::e($hero['body_text']) ?></p>
            <?php endif; ?>
            <?php if (!empty($hero['items'])): ?>
                <div class="actions">
                    <?php foreach ($hero['items'] as $item): ?>
                        <?php $style = ($item['meta']['style'] ?? 'ghost') === 'primary' ? 'primary' : 'ghost'; ?>
                        <a class="btn <?= Util::e($style) ?>" href="<?= Util::e($item['link_url'] ?: '#') ?>"><?= Util::e($item['title'] ?: $item['label']) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <?php if (!empty($summaryCards['items'])): ?>
        <section class="section section--grid">
            <?php foreach ($summaryCards['items'] as $item): ?>
                <article class="card stat-card">
                    <?php if (!empty($item['label'])): ?>
                        <p class="stat-card__label"><?= Util::e($item['label']) ?></p>
                    <?php endif; ?>
                    <h2><?= Util::e($item['title']) ?></h2>
                    <p><?= Util::e($item['body_text']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <section class="section section--content">
        <div class="section-heading">
            <?php if (!empty($about['subtitle'])): ?>
                <p class="eyebrow"><?= Util::e($about['subtitle']) ?></p>
            <?php endif; ?>
            <h2><?= Util::e($about['title']) ?></h2>
        </div>
        <?php if (!empty($about['body_text'])): ?>
            <p class="lede"><?= Util::e($about['body_text']) ?></p>
        <?php endif; ?>
        <div class="content-grid">
            <?php foreach ($about['items'] as $item): ?>
                <article class="card">
                    <h3><?= Util::e($item['title']) ?></h3>
                    <p><?= Util::e($item['body_text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section section--split">
        <div>
            <?php if (!empty($achievements['meta']['eyebrow'])): ?>
                <p class="eyebrow"><?= Util::e($achievements['meta']['eyebrow']) ?></p>
            <?php endif; ?>
            <h2><?= Util::e($achievements['title']) ?></h2>
            <?php if (!empty($achievements['body_text'])): ?>
                <p class="lede"><?= Util::e($achievements['body_text']) ?></p>
            <?php endif; ?>
            <ul class="feature-list">
                <?php foreach ($achievements['items'] as $item): ?>
                    <li><?= Util::e($item['body_text'] ?: $item['title']) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="card">
            <?php if (!empty($technologyTags['meta']['eyebrow'])): ?>
                <p class="eyebrow"><?= Util::e($technologyTags['meta']['eyebrow']) ?></p>
            <?php endif; ?>
            <h2><?= Util::e($technologyTags['title']) ?></h2>
            <?php if (!empty($technologyTags['body_text'])): ?>
                <p class="lede"><?= Util::e($technologyTags['body_text']) ?></p>
            <?php endif; ?>
            <div class="tag-list">
                <?php foreach ($technologyTags['items'] as $item): ?>
                    <span><?= Util::e($item['title'] ?: $item['label']) ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section section--content">
        <div class="section-heading">
            <?php if (!empty($operatingApproach['meta']['eyebrow'])): ?>
                <p class="eyebrow"><?= Util::e($operatingApproach['meta']['eyebrow']) ?></p>
            <?php endif; ?>
            <h2><?= Util::e($operatingApproach['title']) ?></h2>
        </div>
        <?php if (!empty($operatingApproach['body_text'])): ?>
            <p class="lede"><?= Util::e($operatingApproach['body_text']) ?></p>
        <?php endif; ?>
        <div class="content-grid">
            <?php foreach ($operatingApproach['items'] as $item): ?>
                <article class="card">
                    <h3><?= Util::e($item['title']) ?></h3>
                    <p><?= Util::e($item['body_text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="<?= Util::e($contact['meta']['anchor'] ?? 'contact') ?>" class="section section--cta">
        <div>
            <?php if (!empty($contact['subtitle'])): ?>
                <p class="eyebrow"><?= Util::e($contact['subtitle']) ?></p>
            <?php endif; ?>
            <h2><?= Util::e($contact['title']) ?></h2>
            <p class="lede"><?= Util::e($contact['body_text']) ?></p>
        </div>
        <?php if (!empty($contact['items'])): ?>
            <div class="cta-actions">
                <?php foreach ($contact['items'] as $item): ?>
                    <?php $style = ($item['meta']['style'] ?? 'ghost') === 'primary' ? 'primary' : 'ghost'; ?>
                    <a class="btn <?= Util::e($style) ?>" href="<?= Util::e($item['link_url'] ?: '#') ?>"><?= Util::e($item['title'] ?: $item['label']) ?></a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
