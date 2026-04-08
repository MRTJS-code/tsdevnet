<?php
declare(strict_types=1);

use App\Repositories\ContentBlockRepository;
use App\Repositories\HomepageCertificationRepository;
use App\Repositories\HomepageDocumentRepository;
use App\Repositories\HomepageExperienceHighlightRepository;
use App\Repositories\HomepageExperienceRepository;
use App\Repositories\HomepagePortfolioRepository;
use App\Repositories\HomepageTechnologyEntryRepository;
use App\Repositories\HomepageTechnologyGroupRepository;
use App\Repositories\HomepageTestimonialRepository;
use App\Repositories\SiteSettingRepository;
use App\Support\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/src/Support/Autoloader.php';

$config = require $root . '/config/app.php';
$pdo = Database::connect($config);

$blocks = new ContentBlockRepository($pdo);
$settings = new SiteSettingRepository($pdo);
$experience = new HomepageExperienceRepository($pdo);
$experienceHighlights = new HomepageExperienceHighlightRepository($pdo);
$certifications = new HomepageCertificationRepository($pdo);
$technologyGroups = new HomepageTechnologyGroupRepository($pdo);
$technologyEntries = new HomepageTechnologyEntryRepository($pdo);
$portfolio = new HomepagePortfolioRepository($pdo);
$testimonials = new HomepageTestimonialRepository($pdo);
$documents = new HomepageDocumentRepository($pdo);

$pdo->beginTransaction();

try {
    $pdo->exec('DELETE FROM profile_experience_highlights');
    $pdo->exec('DELETE FROM profile_experience');
    $pdo->exec('DELETE FROM profile_certifications');
    $pdo->exec('DELETE FROM profile_technologies');
    $pdo->exec('DELETE FROM profile_technology_groups');
    $pdo->exec('DELETE FROM portfolio_items');
    $pdo->exec('DELETE FROM testimonials');
    $pdo->exec('DELETE FROM site_settings');
    $pdo->exec('DELETE FROM documents');

    $headshotId = $documents->create([
        'document_key' => 'hero_headshot',
        'title' => 'Profile headshot placeholder',
        'file_path' => '',
        'mime_type' => '',
        'is_public' => 0,
        'sort_order' => 10,
        'is_active' => 1,
    ]);

    $cvId = $documents->create([
        'document_key' => 'footer_cv',
        'title' => 'Download CV',
        'file_path' => '',
        'mime_type' => 'application/pdf',
        'is_public' => 1,
        'sort_order' => 20,
        'is_active' => 1,
    ]);

    $settings->replaceSingleton([
        'site_title' => 'Professional Profile and Recruiter Portal',
        'hero_eyebrow' => 'Executive Profile',
        'hero_headline' => 'A reusable executive-profile homepage with a canonical profile content model.',
        'hero_subheadline' => 'This template stays forkable while allowing each deployment to maintain structured profile content through admin and local seed paths.',
        'hero_supporting_text' => 'Use the canonical profile tables for timeline, certifications, grouped technologies, portfolio, testimonials, and documents. Keep personal content out of the public template seed.',
        'profile_name' => 'Profile Name',
        'profile_role' => 'Enterprise systems, data, and integration leader',
        'profile_location' => 'Region or remote availability',
        'profile_availability' => 'CTA state configurable through admin',
        'open_to_work' => 1,
        'primary_cta_mode' => 'register_request_chat',
        'primary_cta_label' => 'Register and request to chat',
        'primary_cta_url' => '/signup.php',
        'secondary_cta_label' => 'Recruiter portal login',
        'secondary_cta_url' => '/login.php',
        'linkedin_url' => 'https://www.linkedin.com/',
        'github_url' => 'https://github.com/',
        'contact_email' => 'hello@example.com',
        'contact_phone' => '+00 000 000 000',
        'contact_location' => 'Region or remote availability',
        'footer_heading' => 'Discuss leadership, delivery, and platform change.',
        'footer_body' => 'Replace this placeholder through admin or a private local seed with real contact guidance and public document paths.',
        'chatbot_teaser_enabled' => 1,
        'chatbot_teaser_label' => 'Assistant pathway ready for the next phase',
        'headshot_document_id' => $headshotId,
        'cv_document_id' => $cvId,
    ]);

    $experienceRows = [
        [
            'role_title' => 'Senior transformation leadership',
            'organisation' => 'Organisation placeholder',
            'start_date' => null,
            'end_date' => null,
            'is_current' => 1,
            'summary' => 'Use this row to describe the current or most relevant leadership role.',
            'sort_order' => 10,
            'highlights' => [
                'Replace with a systems, data, or integration outcome.',
                'Replace with an operating-model or delivery highlight.',
            ],
        ],
        [
            'role_title' => 'Previous enterprise platform role',
            'organisation' => 'Organisation placeholder',
            'start_date' => null,
            'end_date' => null,
            'is_current' => 0,
            'summary' => 'Use additional rows for prior roles that support the homepage narrative.',
            'sort_order' => 20,
            'highlights' => [
                'Replace with a commercial or reporting impact statement.',
            ],
        ],
    ];

    foreach ($experienceRows as $row) {
        $highlights = $row['highlights'];
        unset($row['highlights']);
        $experienceId = $experience->create($row + ['is_active' => 1]);
        $experienceHighlights->replaceForExperience($experienceId, $highlights);
    }

    $certifications->create([
        'certification_name' => 'Professional certification placeholder',
        'issuer' => 'Issuing body',
        'issued_year' => 2026,
        'status_text' => '',
        'credential_url' => '',
        'display_order' => 10,
        'is_active' => 1,
    ]);

    $groupIds = [];
    foreach ([
        ['group_key' => 'core_strengths', 'group_label' => 'Core strengths', 'intro_text' => 'Core capabilities to foreground on the homepage.', 'display_order' => 10],
        ['group_key' => 'supporting_tools', 'group_label' => 'Supporting tools / platforms', 'intro_text' => 'Platforms and tools used to support outcomes.', 'display_order' => 20],
        ['group_key' => 'exposure_familiarity', 'group_label' => 'Exposure / familiarity', 'intro_text' => 'Adjacent technologies that add context without overstating depth.', 'display_order' => 30],
    ] as $group) {
        $groupIds[$group['group_key']] = $technologyGroups->create($group + ['is_active' => 1]);
    }

    $technologyEntries->create(['group_id' => $groupIds['core_strengths'], 'label' => 'Architecture leadership', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);
    $technologyEntries->create(['group_id' => $groupIds['supporting_tools'], 'label' => 'Cloud and platform tooling', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);
    $technologyEntries->create(['group_id' => $groupIds['exposure_familiarity'], 'label' => 'Emerging AI and automation tooling', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);

    $portfolio->create([
        'title' => 'Featured initiative placeholder',
        'slug' => 'featured-initiative-placeholder',
        'short_summary' => 'Describe the initiative, business context, and intended value.',
        'category' => 'Case study',
        'problem_text' => 'Describe the problem that had to be solved.',
        'approach_text' => 'Describe the approach at a high level.',
        'outcome_text' => 'Describe the public-safe result.',
        'tech_text' => 'Describe the platforms, data tooling, or stack involved.',
        'repo_url' => '',
        'demo_url' => '',
        'is_gated' => 0,
        'is_featured' => 1,
        'status' => 'draft',
        'display_order' => 10,
        'is_active' => 1,
    ]);

    $testimonials->create([
        'quote_text' => 'Add a concise testimonial through admin or a local private seed.',
        'person_name' => 'Reference name',
        'person_title' => 'Role title',
        'organisation' => 'Organisation',
        'is_featured' => 1,
        'display_order' => 10,
        'is_active' => 1,
    ]);

    $blocks->upsertBySectionKey([
        'section_key' => 'homepage_intro',
        'title' => 'Placeholder homepage intro',
        'subtitle' => 'Optional flexible content',
        'body_text' => 'Use this generic block only for lightweight introductory copy that does not justify a dedicated typed table.',
        'meta_json' => null,
        'sort_order' => 10,
        'is_active' => 1,
    ]);
    $blocks->upsertBySectionKey([
        'section_key' => 'chatbot_teaser',
        'title' => 'Chatbot teaser placeholder',
        'subtitle' => 'Optional flexible content',
        'body_text' => 'A future gated assistant or teaser can be introduced without redesigning the hero or footer.',
        'meta_json' => null,
        'sort_order' => 20,
        'is_active' => 1,
    ]);

    $pdo->commit();
    fwrite(STDOUT, "Canonical profile template content seeded.\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
