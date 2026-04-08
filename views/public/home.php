<?php
use App\Support\Util;

$siteSettings = $homepage['site_settings'];
$hero = $homepage['hero'];
$experience = $homepage['experience_timeline'];
$coreStrengths = $homepage['core_strengths'];
$groupedCapability = $homepage['grouped_capability'];
$portfolioItems = $homepage['portfolio_items'];
$testimonials = $homepage['testimonials'];
$footer = $homepage['footer_contact'];
$flexibleSections = $siteSettings['flexible_sections'] ?? [];
$topSection = $flexibleSections['top'] ?? null;
$middleSection = $flexibleSections['middle'] ?? null;
$bottomSection = $flexibleSections['bottom'] ?? null;
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

    <section class="section">
        <article class="section-wrapper section-wrapper--top">
            <?php if (!empty($topSection['subtitle'])): ?>
                <p class="eyebrow"><?= Util::e($topSection['subtitle']) ?></p>
            <?php endif; ?>
            <div class="section-heading">
                <h2><?= Util::e($topSection['title'] ?? 'Core strengths') ?></h2>
                <?php if (!empty($topSection['body_text'])): ?>
                    <p class="lede"><?= Util::e($topSection['body_text']) ?></p>
                <?php endif; ?>
            </div>
            <div class="tag-list tag-list--strengths">
                <?php foreach (($coreStrengths['items'] ?? []) as $item): ?>
                    <span title="<?= Util::e($item['detail_text'] ?? '') ?>"><?= Util::e($item['label']) ?></span>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="section">
        <div class="section-heading">
            <p class="eyebrow">Experience</p>
            <h2>Condensed timeline</h2>
        </div>
        <div class="card-rail">
            <?php foreach ($experience as $entry): ?>
                <button class="rail-card rail-card--interactive" type="button" data-dialog-target="experience-<?= (int) $entry['id'] ?>">
                    <p class="rail-card__eyebrow"><?= Util::e($entry['period_label']) ?></p>
                    <h3><?= Util::e($entry['role_title']) ?></h3>
                    <p class="rail-card__meta"><?= Util::e($entry['organisation']) ?></p>
                    <?php if (!empty($entry['summary'])): ?>
                        <p><?= Util::e($entry['summary']) ?></p>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section">
        <article class="section-wrapper section-wrapper--middle">
            <?php if (!empty($middleSection['subtitle'])): ?>
                <p class="eyebrow"><?= Util::e($middleSection['subtitle']) ?></p>
            <?php endif; ?>
            <div class="section-heading">
                <h2><?= Util::e($middleSection['title'] ?? 'Grouped capability') ?></h2>
                <?php if (!empty($middleSection['body_text'])): ?>
                    <p class="lede"><?= Util::e($middleSection['body_text']) ?></p>
                <?php endif; ?>
            </div>
            <div class="card-rail">
                <?php foreach (($groupedCapability['cards'] ?? []) as $card): ?>
                    <button class="rail-card rail-card--interactive" type="button" data-dialog-target="capability-<?= Util::e($card['key']) ?>">
                        <p class="rail-card__eyebrow">Capability</p>
                        <h3><?= Util::e($card['title']) ?></h3>
                        <?php if (!empty($card['intro_text'])): ?>
                            <p><?= Util::e($card['intro_text']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($card['preview_items'])): ?>
                            <ul class="feature-list feature-list--compact">
                                <?php foreach ($card['preview_items'] as $preview): ?>
                                    <li><?= Util::e($preview) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="section">
        <div class="section-heading">
            <p class="eyebrow">Portfolio</p>
            <h2>Featured work</h2>
        </div>
        <div class="card-rail">
            <?php foreach ($portfolioItems as $item): ?>
                <button class="rail-card rail-card--interactive" type="button" data-dialog-target="portfolio-<?= (int) $item['id'] ?>">
                    <?php if (!empty($item['category'])): ?>
                        <p class="rail-card__eyebrow"><?= Util::e($item['category']) ?></p>
                    <?php endif; ?>
                    <h3><?= Util::e($item['title']) ?></h3>
                    <?php if (!empty($item['summary'])): ?>
                        <p><?= Util::e($item['summary']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($item['outcome'])): ?>
                        <p class="rail-card__accent"><?= Util::e($item['outcome']) ?></p>
                    <?php endif; ?>
                </button>
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

    <?php if (!empty($hero['chatbot_teaser']['enabled']) && !empty($bottomSection['body_text'])): ?>
        <section class="section">
            <article class="section-wrapper section-wrapper--bottom">
                <?php if (!empty($bottomSection['subtitle'])): ?>
                    <p class="eyebrow"><?= Util::e($bottomSection['subtitle']) ?></p>
                <?php endif; ?>
                <h2><?= Util::e($bottomSection['title'] ?? $hero['chatbot_teaser']['label']) ?></h2>
                <p class="lede"><?= Util::e($bottomSection['body_text']) ?></p>
            </article>
        </section>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="site-footer__inner">
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
    </div>
</footer>

<?php foreach ($experience as $entry): ?>
    <dialog class="modal-card" id="experience-<?= (int) $entry['id'] ?>">
        <div class="modal-card__header">
            <div>
                <p class="eyebrow"><?= Util::e($entry['period_label']) ?></p>
                <h2><?= Util::e($entry['role_title']) ?></h2>
                <p class="rail-card__meta"><?= Util::e($entry['organisation']) ?></p>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($entry['summary'])): ?>
            <p class="lede"><?= Util::e($entry['summary']) ?></p>
        <?php endif; ?>
        <?php if (!empty($entry['highlights'])): ?>
            <ul class="feature-list">
                <?php foreach ($entry['highlights'] as $highlight): ?>
                    <li><?= Util::e($highlight) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </dialog>
<?php endforeach; ?>

<?php foreach (($groupedCapability['cards'] ?? []) as $card): ?>
    <dialog class="modal-card" id="capability-<?= Util::e($card['key']) ?>">
        <div class="modal-card__header">
            <div>
                <p class="eyebrow">Capability</p>
                <h2><?= Util::e($card['title']) ?></h2>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($card['intro_text'])): ?>
            <p class="lede"><?= Util::e($card['intro_text']) ?></p>
        <?php endif; ?>
        <ul class="feature-list">
            <?php foreach (($card['detail_items'] ?? []) as $detail): ?>
                <li>
                    <?php if (!empty($detail['certification_name'])): ?>
                        <?= Util::e(trim(implode(' | ', array_filter([$detail['certification_name'], $detail['issuer'] ?? '', $detail['issued_label'] ?? ''])))) ?>
                    <?php else: ?>
                        <?= Util::e($detail['label'] ?? '') ?>
                        <?php if (!empty($detail['detail_text'])): ?>
                            <span class="modal-inline-note"> - <?= Util::e($detail['detail_text']) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </dialog>
<?php endforeach; ?>

<?php foreach ($portfolioItems as $item): ?>
    <dialog class="modal-card" id="portfolio-<?= (int) $item['id'] ?>">
        <div class="modal-card__header">
            <div>
                <?php if (!empty($item['category'])): ?>
                    <p class="eyebrow"><?= Util::e($item['category']) ?></p>
                <?php endif; ?>
                <h2><?= Util::e($item['title']) ?></h2>
            </div>
            <button class="btn ghost modal-card__close" type="button" data-dialog-close>Close</button>
        </div>
        <?php if (!empty($item['summary'])): ?>
            <p class="lede"><?= Util::e($item['summary']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['problem_text'])): ?>
            <p><strong>Problem:</strong> <?= Util::e($item['problem_text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['approach_text'])): ?>
            <p><strong>Approach:</strong> <?= Util::e($item['approach_text']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['outcome'])): ?>
            <p><strong>Outcome:</strong> <?= Util::e($item['outcome']) ?></p>
        <?php endif; ?>
        <?php if (!empty($item['tech_text'])): ?>
            <p><strong>Technology:</strong> <?= Util::e($item['tech_text']) ?></p>
        <?php endif; ?>
        <div class="actions">
            <?php if (!empty($item['demo_url'])): ?>
                <a class="btn primary" href="<?= Util::e($item['demo_url']) ?>" target="_blank" rel="noreferrer">View project</a>
            <?php endif; ?>
            <?php if (!empty($item['repo_url'])): ?>
                <a class="btn ghost" href="<?= Util::e($item['repo_url']) ?>" target="_blank" rel="noreferrer">View repository</a>
            <?php endif; ?>
            <?php if (empty($item['demo_url']) && !empty($item['link_url'])): ?>
                <a class="btn primary" href="<?= Util::e($item['link_url']) ?>" target="_blank" rel="noreferrer"><?= Util::e($item['link_label'] ?: 'View project') ?></a>
            <?php endif; ?>
        </div>
    </dialog>
<?php endforeach; ?>

<script>
document.addEventListener('click', function (event) {
    const trigger = event.target.closest('[data-dialog-target]');
    if (trigger) {
        const dialog = document.getElementById(trigger.getAttribute('data-dialog-target'));
        if (dialog && typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    }

    if (event.target.matches('[data-dialog-close]')) {
        const dialog = event.target.closest('dialog');
        if (dialog) {
            dialog.close();
        }
    }
});

document.querySelectorAll('dialog').forEach(function (dialog) {
    dialog.addEventListener('click', function (event) {
        const bounds = dialog.getBoundingClientRect();
        const inside = bounds.top <= event.clientY && event.clientY <= bounds.bottom
            && bounds.left <= event.clientX && event.clientX <= bounds.right;
        if (!inside) {
            dialog.close();
        }
    });
});
</script>
