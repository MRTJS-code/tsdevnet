<?php
use App\Support\Util;

$siteSettings = $homepage['site_settings'];
$hero = $homepage['hero'];
$experience = $homepage['experience_timeline'];
$certifications = $homepage['certifications'];
$technologyGroups = $homepage['technology_groups'];
$portfolioItems = $homepage['portfolio_items'];
$testimonials = $homepage['testimonials'];
$footer = $homepage['footer_contact'];
$homepageIntro = $siteSettings['flexible_sections']['homepage_intro'] ?? null;
?>
<main class="site-shell site-shell--homepage">
    <section class="hero hero--profile">
        <div class="hero__layout">
            <div class="hero__content">
                <?php if (!empty($hero['eyebrow'])): ?>
                    <p class="eyebrow"><?= Util::e($hero['eyebrow']) ?></p>
                <?php endif; ?>
                <h1><?= Util::e($hero['title']) ?></h1>
                <?php if (!empty($hero['summary'])): ?>
                    <p class="lede"><?= Util::e($hero['summary']) ?></p>
                <?php endif; ?>
                <?php if (!empty($hero['supporting_text'])): ?>
                    <p class="lede"><?= Util::e($hero['supporting_text']) ?></p>
                <?php endif; ?>
                <div class="hero__meta">
                    <span class="pill"><?= Util::e(str_replace('_', ' ', $siteSettings['cta_mode'])) ?></span>
                    <?php if (!empty($siteSettings['chatbot_teaser_label'])): ?>
                        <span class="pill"><?= Util::e($siteSettings['chatbot_teaser_label']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="actions">
                    <?php if (!empty($hero['primary_cta']['label']) && !empty($hero['primary_cta']['url'])): ?>
                        <a class="btn primary" href="<?= Util::e($hero['primary_cta']['url']) ?>"><?= Util::e($hero['primary_cta']['label']) ?></a>
                    <?php endif; ?>
                    <?php if (!empty($hero['secondary_cta']['label']) && !empty($hero['secondary_cta']['url'])): ?>
                        <a class="btn ghost" href="<?= Util::e($hero['secondary_cta']['url']) ?>"><?= Util::e($hero['secondary_cta']['label']) ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="hero-card">
                <div class="hero-card__media<?= empty($hero['headshot']['public_url']) ? ' hero-card__media--placeholder' : '' ?>">
                    <?php if (!empty($hero['headshot']['public_url'])): ?>
                        <img src="<?= Util::e($hero['headshot']['public_url']) ?>" alt="<?= Util::e($hero['profile_card']['name']) ?>">
                    <?php else: ?>
                        <span>Headshot ready</span>
                    <?php endif; ?>
                </div>
                <div class="hero-card__body">
                    <p class="eyebrow">Profile card</p>
                    <h2><?= Util::e($hero['profile_card']['name']) ?></h2>
                    <p class="hero-card__role"><?= Util::e($hero['profile_card']['role']) ?></p>
                    <dl class="profile-facts">
                        <div>
                            <dt>Location</dt>
                            <dd><?= Util::e($hero['profile_card']['location']) ?></dd>
                        </div>
                        <div>
                            <dt>Availability</dt>
                            <dd><?= Util::e($hero['profile_card']['availability']) ?></dd>
                        </div>
                    </dl>
                </div>
            </aside>
        </div>
    </section>

    <?php if (!empty($homepageIntro)): ?>
        <section class="section">
            <article class="card intro-card">
                <?php if (!empty($homepageIntro['subtitle'])): ?>
                    <p class="eyebrow"><?= Util::e($homepageIntro['subtitle']) ?></p>
                <?php endif; ?>
                <h2><?= Util::e($homepageIntro['title']) ?></h2>
                <p class="lede"><?= Util::e($homepageIntro['body_text']) ?></p>
            </article>
        </section>
    <?php endif; ?>

    <section class="section section--split-home">
        <div class="section-panel">
            <div class="section-heading">
                <p class="eyebrow">Experience</p>
                <h2>Condensed timeline</h2>
            </div>
            <div class="timeline">
                <?php foreach ($experience as $entry): ?>
                    <article class="timeline__entry">
                        <p class="timeline__period"><?= Util::e($entry['period_label']) ?></p>
                        <h3><?= Util::e($entry['role_title']) ?></h3>
                        <p class="timeline__org"><?= Util::e($entry['organisation']) ?></p>
                        <?php if (!empty($entry['summary'])): ?>
                            <p><?= Util::e($entry['summary']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($entry['highlight_text'])): ?>
                            <p class="timeline__highlight"><?= Util::e($entry['highlight_text']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section-panel">
            <div class="section-heading">
                <p class="eyebrow">Credentials</p>
                <h2>Certifications</h2>
            </div>
            <div class="stack-list">
                <?php foreach ($certifications as $entry): ?>
                    <article class="stack-list__item">
                        <h3><?= Util::e($entry['certification_name']) ?></h3>
                        <p><?= Util::e(implode(' | ', array_filter([$entry['issuer'] ?? '', $entry['issued_label'] ?? '']))) ?></p>
                        <?php if (!empty($entry['credential_url'])): ?>
                            <a href="<?= Util::e($entry['credential_url']) ?>" target="_blank" rel="noreferrer">View credential</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <p class="eyebrow">Technology</p>
            <h2>Grouped capability view</h2>
        </div>
        <div class="technology-groups">
            <?php foreach ($technologyGroups as $group): ?>
                <article class="card technology-group">
                    <h3><?= Util::e($group['title']) ?></h3>
                    <?php if (!empty($group['intro_text'])): ?>
                        <p><?= Util::e($group['intro_text']) ?></p>
                    <?php endif; ?>
                    <div class="tag-list">
                        <?php foreach ($group['items'] as $item): ?>
                            <span title="<?= Util::e($item['detail_text'] ?? '') ?>"><?= Util::e($item['label']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <p class="eyebrow">Portfolio</p>
            <h2>Featured work</h2>
        </div>
        <div class="content-grid">
            <?php foreach ($portfolioItems as $item): ?>
                <article class="card portfolio-card">
                    <h3><?= Util::e($item['title']) ?></h3>
                    <?php if (!empty($item['summary'])): ?>
                        <p><?= Util::e($item['summary']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($item['outcome'])): ?>
                        <p class="portfolio-card__outcome"><?= Util::e($item['outcome']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($item['link_url']) && !empty($item['link_label'])): ?>
                        <a href="<?= Util::e($item['link_url']) ?>" target="_blank" rel="noreferrer"><?= Util::e($item['link_label']) ?></a>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <div class="section-heading">
            <p class="eyebrow">Testimonials</p>
            <h2>Selected references</h2>
        </div>
        <div class="content-grid">
            <?php foreach ($testimonials as $item): ?>
                <article class="card testimonial-card">
                    <p class="testimonial-card__quote">&ldquo;<?= Util::e($item['quote_text']) ?>&rdquo;</p>
                    <p class="testimonial-card__byline"><?= Util::e($item['person_name']) ?></p>
                    <p><?= Util::e(implode(' | ', array_filter([$item['person_title'] ?? '', $item['organisation'] ?? '']))) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (!empty($hero['chatbot_teaser']['enabled']) && !empty($hero['chatbot_teaser']['body_text'])): ?>
        <section class="section">
            <article class="card teaser-card">
                <p class="eyebrow"><?= Util::e($hero['chatbot_teaser']['label']) ?></p>
                <p class="lede"><?= Util::e($hero['chatbot_teaser']['body_text']) ?></p>
            </article>
        </section>
    <?php endif; ?>

    <section class="section section--cta footer-contact">
        <div>
            <p class="eyebrow">Contact</p>
            <h2><?= Util::e($footer['heading']) ?></h2>
            <p class="lede"><?= Util::e($footer['body_text']) ?></p>
            <div class="contact-list">
                <?php if (!empty($footer['email'])): ?>
                    <a href="mailto:<?= Util::e($footer['email']) ?>"><?= Util::e($footer['email']) ?></a>
                <?php endif; ?>
                <?php if (!empty($footer['phone'])): ?>
                    <a href="tel:<?= Util::e($footer['phone']) ?>"><?= Util::e($footer['phone']) ?></a>
                <?php endif; ?>
                <?php if (!empty($footer['location'])): ?>
                    <span><?= Util::e($footer['location']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="cta-actions footer-contact__actions">
            <?php if (!empty($footer['cv']['public_url'])): ?>
                <a class="btn primary" href="<?= Util::e($footer['cv']['public_url']) ?>" target="_blank" rel="noreferrer"><?= Util::e($footer['cv']['title']) ?></a>
            <?php endif; ?>
            <?php foreach ($footer['links'] as $link): ?>
                <?php if (!empty($link['public_url'])): ?>
                    <a class="btn ghost" href="<?= Util::e($link['public_url']) ?>" target="_blank" rel="noreferrer"><?= Util::e($link['title']) ?></a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </section>
</main>
