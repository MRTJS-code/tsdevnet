<?php
declare(strict_types=1);

use App\Repositories\HomepageCertificationRepository;
use App\Repositories\HomepageDocumentRepository;
use App\Repositories\HomepageExperienceHighlightRepository;
use App\Repositories\HomepageExperienceRepository;
use App\Repositories\HomepageModuleRepository;
use App\Repositories\HomepagePortfolioRepository;
use App\Repositories\HomepageTechnologyEntryRepository;
use App\Repositories\HomepageTechnologyGroupRepository;
use App\Repositories\HomepageTestimonialRepository;
use App\Repositories\ModuleRichTextSectionRepository;
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

$settings = new SiteSettingRepository($pdo);
$experience = new HomepageExperienceRepository($pdo);
$experienceHighlights = new HomepageExperienceHighlightRepository($pdo);
$certifications = new HomepageCertificationRepository($pdo);
$technologyGroups = new HomepageTechnologyGroupRepository($pdo);
$technologyEntries = new HomepageTechnologyEntryRepository($pdo);
$portfolio = new HomepagePortfolioRepository($pdo);
$testimonials = new HomepageTestimonialRepository($pdo);
$documents = new HomepageDocumentRepository($pdo);
$modules = new HomepageModuleRepository($pdo);
$richTextSections = new ModuleRichTextSectionRepository($pdo);

$pdo->beginTransaction();

try {
    $pdo->exec('DELETE FROM module_rich_text_sections');
    $pdo->exec('DELETE FROM homepage_modules');
    $pdo->exec('DELETE FROM content_items');
    $pdo->exec('DELETE FROM content_blocks');
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
        'hero_headline' => 'A reusable executive-profile homepage with modular content blocks.',
        'hero_subheadline' => 'This template stays forkable while allowing each deployment to manage ordered content modules through admin and local seed paths.',
        'hero_supporting_text' => 'Use the module registry for ordered middle-page content while keeping hero and footer as fixed design regions.',
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

    $moduleSpecs = [
        [
            'module_key' => 'executive_summary',
            'module_type' => 'rich_text',
            'eyebrow' => 'Executive summary',
            'title' => 'Core strengths',
            'intro_text' => 'Use this module to frame the strongest capabilities between the hero and the timeline.',
            'anchor_id' => 'executive-summary',
            'style_variant' => 'summary',
            'display_order' => 10,
            'is_active' => 1,
            'content' => [
                'body_text' => 'Use admin to add a concise executive summary or information block for the middle of the homepage.',
            ],
        ],
        [
            'module_key' => 'experience_timeline',
            'module_type' => 'experience_timeline',
            'eyebrow' => 'Experience',
            'title' => 'Condensed timeline',
            'intro_text' => 'Ordered career highlights and selected outcomes.',
            'anchor_id' => 'experience',
            'style_variant' => 'timeline',
            'display_order' => 20,
            'is_active' => 1,
        ],
        [
            'module_key' => 'certifications',
            'module_type' => 'certifications',
            'eyebrow' => 'Credentials',
            'title' => 'Certifications',
            'intro_text' => 'Professional qualifications and current credentials.',
            'anchor_id' => 'certifications',
            'style_variant' => 'cards',
            'display_order' => 30,
            'is_active' => 1,
        ],
        [
            'module_key' => 'technology_groups',
            'module_type' => 'technology_groups',
            'eyebrow' => 'Capability',
            'title' => 'Grouped capability',
            'intro_text' => 'Supporting tools, strengths, and exposure grouped for quick scanning.',
            'anchor_id' => 'technology-groups',
            'style_variant' => 'grouped-capability',
            'display_order' => 40,
            'is_active' => 1,
        ],
        [
            'module_key' => 'featured_portfolio',
            'module_type' => 'featured_portfolio',
            'eyebrow' => 'Portfolio',
            'title' => 'Featured work',
            'intro_text' => 'Selected initiatives and delivery outcomes.',
            'anchor_id' => 'portfolio',
            'style_variant' => 'cards',
            'display_order' => 50,
            'is_active' => 1,
        ],
        [
            'module_key' => 'testimonials',
            'module_type' => 'testimonials',
            'eyebrow' => 'Testimonials',
            'title' => 'Selected references',
            'intro_text' => 'Short quote cards and public social proof.',
            'anchor_id' => 'testimonials',
            'style_variant' => 'quotes',
            'display_order' => 60,
            'is_active' => 1,
        ],
        [
            'module_key' => 'chatbot_teaser',
            'module_type' => 'cta_info',
            'eyebrow' => 'Optional CTA',
            'title' => 'Chatbot teaser placeholder',
            'intro_text' => 'Optional CTA/info block above the footer.',
            'anchor_id' => 'chatbot-teaser',
            'style_variant' => 'callout',
            'display_order' => 70,
            'is_active' => 1,
            'content' => [
                'body_text' => 'A future gated assistant or teaser can be introduced above the footer without redesigning the homepage.',
            ],
        ],
    ];

    foreach ($moduleSpecs as $moduleSpec) {
        $content = $moduleSpec['content'] ?? null;
        unset($moduleSpec['content']);
        $moduleId = $modules->create($moduleSpec);
        if (is_array($content)) {
            $richTextSections->upsertForModule($moduleId, $content);
        }
    }

    $pdo->commit();
    fwrite(STDOUT, "Reusable homepage/profile content seeded.\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
