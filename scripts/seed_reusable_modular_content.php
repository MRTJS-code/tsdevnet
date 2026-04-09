<?php
declare(strict_types=1);

use App\Repositories\HomepageDocumentRepository;
use App\Repositories\HomepageFooterSettingsRepository;
use App\Repositories\HomepageHeroSettingsRepository;
use App\Repositories\HomepageModuleRepository;
use App\Repositories\ModuleCaseStudyItemRepository;
use App\Repositories\ModuleCtaBannerPayloadRepository;
use App\Repositories\ModuleListItemRepository;
use App\Repositories\ModuleMediaTextPayloadRepository;
use App\Repositories\ModulePillCardItemRepository;
use App\Repositories\ModuleQuoteCardItemRepository;
use App\Repositories\ModuleRichTextPayloadRepository;
use App\Repositories\ModuleTimelineEntryRepository;
use App\Support\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Run this script from the command line.\n");
    exit(1);
}

$root = dirname(__DIR__);
require_once $root . '/src/Support/Autoloader.php';

$config = require $root . '/config/app.php';
$pdo = Database::connect($config);

$documents = new HomepageDocumentRepository($pdo);
$hero = new HomepageHeroSettingsRepository($pdo);
$footer = new HomepageFooterSettingsRepository($pdo);
$modules = new HomepageModuleRepository($pdo);
$richText = new ModuleRichTextPayloadRepository($pdo);
$timeline = new ModuleTimelineEntryRepository($pdo);
$pillCards = new ModulePillCardItemRepository($pdo);
$caseStudies = new ModuleCaseStudyItemRepository($pdo);
$listItems = new ModuleListItemRepository($pdo);
$quoteCards = new ModuleQuoteCardItemRepository($pdo);
$ctaBanners = new ModuleCtaBannerPayloadRepository($pdo);
$mediaText = new ModuleMediaTextPayloadRepository($pdo);

$pdo->beginTransaction();

try {
    $pdo->exec('DELETE FROM homepage_modules');
    $pdo->exec('DELETE FROM module_rich_text_payloads');
    $pdo->exec('DELETE FROM module_timeline_highlights');
    $pdo->exec('DELETE FROM module_timeline_entries');
    $pdo->exec('DELETE FROM module_pill_card_items');
    $pdo->exec('DELETE FROM module_case_study_items');
    $pdo->exec('DELETE FROM module_list_items');
    $pdo->exec('DELETE FROM module_quote_card_items');
    $pdo->exec('DELETE FROM module_cta_banner_payloads');
    $pdo->exec('DELETE FROM module_media_text_payloads');
    $pdo->exec('DELETE FROM homepage_hero_settings');
    $pdo->exec('DELETE FROM homepage_footer_settings');
    $pdo->exec('DELETE FROM documents');

    $headshotId = $documents->create([
        'document_key' => 'hero_headshot',
        'document_type' => 'headshot',
        'title' => 'Profile headshot placeholder',
        'description_text' => 'Upload a public-safe headshot through admin.',
        'file_path' => '',
        'mime_type' => '',
        'is_public' => 0,
        'sort_order' => 10,
        'is_active' => 1,
    ]);

    $cvId = $documents->create([
        'document_key' => 'footer_cv',
        'document_type' => 'cv_pdf',
        'title' => 'Download CV',
        'description_text' => 'Upload a public-safe CV PDF through admin.',
        'file_path' => '',
        'mime_type' => 'application/pdf',
        'is_public' => 1,
        'sort_order' => 20,
        'is_active' => 1,
    ]);

    $moduleMediaId = $documents->create([
        'document_key' => 'module_architecture_diagram',
        'document_type' => 'module_media',
        'title' => 'Architecture diagram placeholder',
        'description_text' => 'Optional media used by the media-text module.',
        'file_path' => '',
        'mime_type' => 'image/png',
        'is_public' => 1,
        'sort_order' => 30,
        'is_active' => 1,
    ]);

    $hero->replace([
        'site_title' => 'Professional Profile and Recruiter Portal',
        'eyebrow' => 'Executive Profile',
        'title' => 'A reusable executive-profile homepage with modular blocks.',
        'summary_text' => 'This template keeps the code focused on rendering shapes while the editor decides what each block means.',
        'supporting_text' => 'Hero and footer remain fixed. Everything between them is an ordered list of reusable homepage modules.',
        'profile_name' => 'Profile Name',
        'profile_role' => 'Enterprise systems, data, and integration leader',
        'profile_location' => 'Region or remote availability',
        'profile_availability' => 'Open to recruiter conversations',
        'open_to_work' => 1,
        'cta_mode' => 'register_request_chat',
        'primary_cta_label' => 'Register and request to chat',
        'primary_cta_url' => '/signup.php',
        'secondary_cta_label' => 'Recruiter portal login',
        'secondary_cta_url' => '/login.php',
        'headshot_document_id' => $headshotId,
    ]);

    $footer->replace([
        'heading' => 'Discuss delivery, platform change, and recruiter access.',
        'body_text' => 'Replace this placeholder through admin or a local-only profile seed.',
        'contact_email' => 'hello@example.com',
        'contact_phone' => '+00 000 000 000',
        'contact_location' => 'Region or remote availability',
        'cv_document_id' => $cvId,
        'linkedin_url' => 'https://www.linkedin.com/',
        'github_url' => 'https://github.com/',
    ]);

    $moduleSpecs = [
        ['module_key' => 'executive-summary', 'module_type' => 'rich_text', 'eyebrow' => 'Overview', 'title' => 'Executive summary', 'intro_text' => 'A generic summary block.', 'anchor_id' => 'executive-summary', 'style_variant' => 'summary', 'display_order' => 10, 'is_active' => 1],
        ['module_key' => 'leadership-timeline', 'module_type' => 'timeline', 'eyebrow' => 'History', 'title' => 'Selected timeline', 'intro_text' => 'An ordered timeline rendered as inline details.', 'anchor_id' => 'timeline', 'style_variant' => 'accordion', 'display_order' => 20, 'is_active' => 1],
        ['module_key' => 'strengths-grid', 'module_type' => 'pill_cards', 'eyebrow' => 'Capabilities', 'title' => 'Pill card strengths', 'intro_text' => 'Reusable compact cards for strengths or themes.', 'anchor_id' => 'strengths', 'style_variant' => 'compact', 'display_order' => 30, 'is_active' => 1],
        ['module_key' => 'case-study-grid', 'module_type' => 'case_studies', 'eyebrow' => 'Work', 'title' => 'Case studies', 'intro_text' => 'Selected public-safe work examples.', 'anchor_id' => 'case-studies', 'style_variant' => 'cards', 'display_order' => 40, 'is_active' => 1],
        ['module_key' => 'credentials-list', 'module_type' => 'list', 'eyebrow' => 'Details', 'title' => 'List module', 'intro_text' => 'Use for certifications, grouped facts, or other structured lists.', 'anchor_id' => 'list-module', 'style_variant' => 'stack', 'display_order' => 50, 'is_active' => 1],
        ['module_key' => 'social-proof', 'module_type' => 'quote_cards', 'eyebrow' => 'Quotes', 'title' => 'Quote cards', 'intro_text' => 'Short testimonials or quotations.', 'anchor_id' => 'quote-cards', 'style_variant' => 'quotes', 'display_order' => 60, 'is_active' => 1],
        ['module_key' => 'recruiter-callout', 'module_type' => 'cta_banner', 'eyebrow' => 'Action', 'title' => 'CTA banner', 'intro_text' => 'A reusable callout above the footer.', 'anchor_id' => 'cta-banner', 'style_variant' => 'banner', 'display_order' => 70, 'is_active' => 1],
        ['module_key' => 'diagram-callout', 'module_type' => 'media_text', 'eyebrow' => 'Visual', 'title' => 'Media and text', 'intro_text' => 'Image plus explanatory text.', 'anchor_id' => 'media-text', 'style_variant' => 'split', 'media_document_id' => $moduleMediaId, 'display_order' => 80, 'is_active' => 1],
        ['module_key' => 'hidden-example', 'module_type' => 'rich_text', 'eyebrow' => 'Hidden', 'title' => 'Hidden example', 'intro_text' => 'Used by browser tests to assert hidden modules do not render.', 'anchor_id' => 'hidden-example', 'style_variant' => 'hidden', 'display_order' => 90, 'is_active' => 0],
    ];

    $moduleIds = [];
    foreach ($moduleSpecs as $spec) {
        $moduleIds[$spec['module_key']] = $modules->create($spec);
    }

    $richText->upsertForModule($moduleIds['executive-summary'], [
        'body_text' => 'Use rich text for a summary, positioning statement, or any other editor-defined introduction.',
        'primary_cta_label' => 'Register',
        'primary_cta_url' => '/signup.php',
        'secondary_cta_label' => 'Login',
        'secondary_cta_url' => '/login.php',
    ]);

    $timeline->replaceForModule($moduleIds['leadership-timeline'], [
        [
            'title' => 'Senior transformation leadership',
            'subtitle' => 'Organisation placeholder',
            'meta' => 'Recent phase',
            'summary_text' => 'Use this module for role history, delivery phases, or any chronological sequence.',
            'detail_text' => 'Inline detail keeps the interaction simple and browser-testable.',
            'highlights' => ['Outcome-oriented placeholder', 'Cross-functional delivery placeholder'],
        ],
        [
            'title' => 'Prior delivery role',
            'subtitle' => 'Organisation placeholder',
            'meta' => 'Earlier phase',
            'summary_text' => 'A second timeline row to prove ordered rendering.',
            'detail_text' => 'Editors decide what the timeline means.',
            'highlights' => ['Migration placeholder', 'Stakeholder alignment placeholder'],
        ],
    ]);

    $pillCards->replaceForModule($moduleIds['strengths-grid'], [
        ['title' => 'Systems leadership', 'body_text' => 'Reusable pill card content.', 'badge_text' => 'Core', 'link_label' => '', 'link_url' => ''],
        ['title' => 'Data and reporting', 'body_text' => 'Another reusable pill card.', 'badge_text' => 'Core', 'link_label' => '', 'link_url' => ''],
    ]);

    $caseStudies->replaceForModule($moduleIds['case-study-grid'], [
        ['title' => 'Case study placeholder', 'category_text' => 'Website', 'summary_text' => 'A public-safe example case study.', 'outcome_text' => 'Clear outcome statement.', 'detail_text' => 'Keep detail concise and reusable.', 'link_label' => 'View repository', 'link_url' => 'https://github.com/'],
        ['title' => 'Second case study', 'category_text' => 'Integration', 'summary_text' => 'Another example card.', 'outcome_text' => 'Another outcome statement.', 'detail_text' => 'Demonstrates generic ordering.', 'link_label' => '', 'link_url' => ''],
    ]);

    $listItems->replaceForModule($moduleIds['credentials-list'], [
        ['item_title' => 'Credential placeholder', 'item_body' => 'Useful for certifications or grouped facts.', 'item_meta' => 'Current', 'link_label' => '', 'link_url' => ''],
        ['item_title' => 'Grouped fact placeholder', 'item_body' => 'The code only cares that this is a list.', 'item_meta' => 'Reusable', 'link_label' => '', 'link_url' => ''],
    ]);

    $quoteCards->replaceForModule($moduleIds['social-proof'], [
        ['quote_text' => 'A concise quote card keeps social proof compact.', 'attribution_name' => 'Reference name', 'attribution_role' => 'Role title', 'attribution_context' => 'Organisation'],
        ['quote_text' => 'This second card proves ordered quote rendering.', 'attribution_name' => 'Another reference', 'attribution_role' => 'Role title', 'attribution_context' => 'Organisation'],
    ]);

    $ctaBanners->upsertForModule($moduleIds['recruiter-callout'], [
        'body_text' => 'Use the CTA banner for recruiter guidance, campaign messaging, or any other call to action.',
        'primary_cta_label' => 'Request access',
        'primary_cta_url' => '/signup.php',
        'secondary_cta_label' => 'Contact',
        'secondary_cta_url' => 'mailto:hello@example.com',
    ]);

    $mediaText->upsertForModule($moduleIds['diagram-callout'], [
        'body_text' => 'Use media-text for screenshots, diagrams, or other visual callouts paired with narrative copy.',
        'media_position' => 'right',
        'primary_cta_label' => 'Learn more',
        'primary_cta_url' => '/login.php',
        'secondary_cta_label' => '',
        'secondary_cta_url' => '',
    ]);

    $richText->upsertForModule($moduleIds['hidden-example'], [
        'body_text' => 'This inactive module should not render.',
    ]);

    $pdo->commit();
    fwrite(STDOUT, "Reusable modular homepage content seeded.\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
