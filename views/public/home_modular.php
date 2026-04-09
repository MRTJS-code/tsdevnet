<?php
use App\Support\Util;

$hero = $homepage['hero'];
$modules = $homepage['modules'] ?? [];
$footer = $homepage['footer_contact'];
?>
<main class="site-shell site-shell--homepage">
    <section class="hero hero--profile" data-testid="homepage-hero">
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
                    <span class="pill"><?= Util::e(str_replace('_', ' ', $hero['cta_mode'] ?? '')) ?></span>
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

    <section class="homepage-modules" data-testid="homepage-modules">
    <?php foreach ($modules as $module): ?>
        <?php
        $moduleTemplate = __DIR__ . '/modules/' . preg_replace('/[^a-z_]/', '', (string) $module['module_type']) . '.php';
        if (is_file($moduleTemplate)) {
            require $moduleTemplate;
        }
        ?>
    <?php endforeach; ?>
    </section>
</main>

<footer class="site-footer" data-testid="homepage-footer">
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
