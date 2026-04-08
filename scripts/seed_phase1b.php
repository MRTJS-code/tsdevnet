<?php
declare(strict_types=1);

use App\Repositories\AdminUserRepository;
use App\Repositories\AssistantKnowledgeRepository;
use App\Repositories\ContentBlockRepository;
use App\Repositories\HomepageCertificationRepository;
use App\Repositories\HomepageDocumentRepository;
use App\Repositories\HomepageExperienceRepository;
use App\Repositories\HomepagePortfolioRepository;
use App\Repositories\HomepageTechnologyEntryRepository;
use App\Repositories\HomepageTechnologyGroupRepository;
use App\Repositories\HomepageTestimonialRepository;
use App\Repositories\SiteSettingRepository;
use App\Support\Database;
use App\Support\Util;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/src/Support/Autoloader.php';

$config = require $root . '/config/app.php';
$pdo = Database::connect($config);

$blocks = new ContentBlockRepository($pdo);
$knowledge = new AssistantKnowledgeRepository($pdo);
$admins = new AdminUserRepository($pdo);
$settings = new SiteSettingRepository($pdo);
$experience = new HomepageExperienceRepository($pdo);
$certifications = new HomepageCertificationRepository($pdo);
$technologyGroups = new HomepageTechnologyGroupRepository($pdo);
$technologyEntries = new HomepageTechnologyEntryRepository($pdo);
$portfolio = new HomepagePortfolioRepository($pdo);
$testimonials = new HomepageTestimonialRepository($pdo);
$documents = new HomepageDocumentRepository($pdo);

$settings->upsertMany([
    'hero_eyebrow' => ['value' => 'Executive Profile', 'type' => 'string'],
    'hero_title' => ['value' => 'A reusable executive-profile homepage with a typed content model.', 'type' => 'string'],
    'hero_summary' => ['value' => 'This placeholder homepage stays generic in-repo while allowing each fork to manage profile content through admin.', 'type' => 'text'],
    'hero_supporting_text' => ['value' => 'Phase 1C moves the homepage beyond generic content blocks so structured sections can be maintained cleanly without overloading JSON metadata.', 'type' => 'text'],
    'profile_name' => ['value' => 'Profile Name', 'type' => 'string'],
    'profile_role' => ['value' => 'Executive technology and delivery leader', 'type' => 'string'],
    'profile_location' => ['value' => 'Region or remote availability', 'type' => 'string'],
    'profile_availability' => ['value' => 'CTA state configurable through admin', 'type' => 'string'],
    'cta_mode' => ['value' => 'register_request_chat', 'type' => 'string'],
    'cta_primary_label' => ['value' => 'Register and request to chat', 'type' => 'string'],
    'cta_primary_url' => ['value' => '/signup.php', 'type' => 'url'],
    'cta_secondary_label' => ['value' => 'Recruiter portal login', 'type' => 'string'],
    'cta_secondary_url' => ['value' => '/login.php', 'type' => 'url'],
    'footer_heading' => ['value' => 'Let\'s discuss delivery, technology leadership, and recruiter access.', 'type' => 'string'],
    'footer_body' => ['value' => 'Use admin to maintain contact details, footer links, and the downloadable CV.', 'type' => 'text'],
    'contact_email' => ['value' => 'hello@example.com', 'type' => 'email'],
    'contact_phone' => ['value' => '+00 000 000 000', 'type' => 'tel'],
    'chatbot_teaser_enabled' => ['value' => '1', 'type' => 'bool'],
    'chatbot_teaser_label' => ['value' => 'Assistant pathway ready for the next phase', 'type' => 'string'],
]);

$seedKnowledge = [
    ['knowledge_key' => 'role_scope', 'trigger_type' => 'contains', 'trigger_value' => 'role', 'response_text' => 'This assistant can summarise role scope, delivery accountabilities, and how the profile aligns to the brief. Approved access can be configured with deeper recruiter-facing guidance.', 'minimum_access_tier' => 'pending', 'priority' => 100, 'is_active' => 1],
    ['knowledge_key' => 'skills_stack', 'trigger_type' => 'contains', 'trigger_value' => 'skills', 'response_text' => 'Use admin to tailor this response for systems, data, delivery, and platform capability themes relevant to the profile owner.', 'minimum_access_tier' => 'pending', 'priority' => 90, 'is_active' => 1],
    ['knowledge_key' => 'leadership_style', 'trigger_type' => 'contains', 'trigger_value' => 'lead', 'response_text' => 'This rule-based response can be customised to explain leadership style, governance posture, and operating model preferences for recruiter conversations.', 'minimum_access_tier' => 'approved', 'priority' => 80, 'is_active' => 1],
];

if ($experience->listAll() === []) {
    $experience->create([
        'role_title' => 'Senior transformation leadership',
        'organisation' => 'Organisation placeholder',
        'period_label' => 'Recent phase',
        'summary' => 'Use admin to enter a concise leadership timeline that highlights scope, scale, and delivery context.',
        'highlight_text' => 'Outcome-oriented placeholder',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
}

if ($certifications->listAll() === []) {
    $certifications->create([
        'certification_name' => 'Professional certification placeholder',
        'issuer' => 'Issuing body',
        'issued_label' => 'Issued date',
        'credential_url' => '',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
}

$groupMap = [
    'core_strengths' => ['title' => 'Core strengths', 'intro_text' => 'High-confidence capabilities that should be foregrounded on the homepage.', 'sort_order' => 10],
    'supporting_tools' => ['title' => 'Supporting tools and platforms', 'intro_text' => 'Platforms and tools used to support outcomes.', 'sort_order' => 20],
    'exposure' => ['title' => 'Exposure and familiarity', 'intro_text' => 'Adjacent technologies that add context without overstating depth.', 'sort_order' => 30],
];
foreach ($groupMap as $groupKey => $groupData) {
    $technologyGroups->upsertByGroupKey($groupData + ['group_key' => $groupKey, 'is_active' => 1]);
}

if ($technologyEntries->listAllWithGroup() === []) {
    $groupsByKey = [];
    foreach ($technologyGroups->listAll() as $group) {
        $groupsByKey[$group['group_key']] = (int) $group['id'];
    }

    $technologyEntries->create(['group_id' => $groupsByKey['core_strengths'], 'label' => 'Architecture leadership', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);
    $technologyEntries->create(['group_id' => $groupsByKey['core_strengths'], 'label' => 'Delivery governance', 'detail_text' => 'Replace through admin', 'sort_order' => 20, 'is_active' => 1]);
    $technologyEntries->create(['group_id' => $groupsByKey['supporting_tools'], 'label' => 'Cloud platforms', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);
    $technologyEntries->create(['group_id' => $groupsByKey['exposure'], 'label' => 'Emerging tooling', 'detail_text' => 'Replace through admin', 'sort_order' => 10, 'is_active' => 1]);
}

if ($portfolio->listAll() === []) {
    $portfolio->create([
        'title' => 'Featured initiative placeholder',
        'summary' => 'Describe the initiative, environment, or transformation stream.',
        'outcome' => 'Capture the business or delivery outcome in one compact statement.',
        'link_url' => '',
        'link_label' => '',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
}

if ($testimonials->listAll() === []) {
    $testimonials->create([
        'quote_text' => 'Add a concise testimonial through admin to validate leadership style, execution quality, or stakeholder confidence.',
        'person_name' => 'Reference name',
        'person_title' => 'Role title',
        'organisation' => 'Organisation',
        'sort_order' => 10,
        'is_active' => 1,
    ]);
}

$documents->upsertByDocumentKey([
    'document_key' => 'hero_headshot',
    'document_type' => 'headshot',
    'title' => 'Profile headshot placeholder',
    'description_text' => 'Upload a headshot through admin to replace this placeholder state.',
    'file_path' => '',
    'external_url' => '',
    'mime_type' => '',
    'file_size_bytes' => 0,
    'sort_order' => 10,
    'is_active' => 1,
]);
$documents->upsertByDocumentKey([
    'document_key' => 'footer_cv',
    'document_type' => 'cv_pdf',
    'title' => 'Download CV',
    'description_text' => 'Upload a PDF CV through admin.',
    'file_path' => '',
    'external_url' => '',
    'mime_type' => '',
    'file_size_bytes' => 0,
    'sort_order' => 20,
    'is_active' => 1,
]);
$documents->upsertByDocumentKey([
    'document_key' => 'linkedin',
    'document_type' => 'footer_link',
    'title' => 'LinkedIn',
    'description_text' => '',
    'file_path' => '',
    'external_url' => 'https://www.linkedin.com/',
    'mime_type' => '',
    'file_size_bytes' => 0,
    'sort_order' => 30,
    'is_active' => 1,
]);
$documents->upsertByDocumentKey([
    'document_key' => 'github',
    'document_type' => 'footer_link',
    'title' => 'GitHub',
    'description_text' => '',
    'file_path' => '',
    'external_url' => 'https://github.com/',
    'mime_type' => '',
    'file_size_bytes' => 0,
    'sort_order' => 40,
    'is_active' => 1,
]);

$blocks->upsertBySectionKey([
    'section_key' => 'homepage_intro',
    'title' => 'Placeholder homepage intro',
    'subtitle' => 'Optional flexible content',
    'body_text' => 'Use this optional generic block for lightweight introductory copy that does not justify a dedicated typed table.',
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

$existingKnowledge = [];
foreach ($knowledge->listAll() as $entry) {
    $existingKnowledge[$entry['knowledge_key']] = true;
}
foreach ($seedKnowledge as $entry) {
    if (!isset($existingKnowledge[$entry['knowledge_key']])) {
        $knowledge->create($entry);
    }
}

if ($admins->countActive() === 0) {
    $email = trim((string) ($config['admin']['seed_email'] ?? ''));
    $password = (string) ($config['admin']['seed_password'] ?? '');
    $name = trim((string) ($config['admin']['seed_name'] ?? 'Site Admin'));

    if ($email === '') {
        $email = trim((string) readline('Admin email: '));
    }
    if ($password === '') {
        $password = (string) readline('Admin password: ');
    }
    if ($name === '') {
        $name = 'Site Admin';
    }

    if (!Util::validateEmail($email) || $password === '') {
        fwrite(STDERR, "A valid admin email and password are required to seed the initial admin user.\n");
        exit(1);
    }

    $admins->create(strtolower($email), password_hash($password, PASSWORD_DEFAULT), $name);
    fwrite(STDOUT, "Initial admin user created for {$email}\n");
} else {
    fwrite(STDOUT, "Admin users already exist. Skipping admin seed.\n");
}

fwrite(STDOUT, "Phase 1C typed homepage placeholder content and assistant knowledge seeded.\n");
