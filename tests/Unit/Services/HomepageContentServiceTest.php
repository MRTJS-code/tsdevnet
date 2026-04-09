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
use App\Services\ModularHomepageContentService;

require_once dirname(__DIR__, 3) . '/tests/bootstrap.php';
require_once dirname(__DIR__, 3) . '/tests/TestCase.php';

final class HomepageContentServiceTest extends TestCase
{
    public function run(): void
    {
        $pdo = test_pdo();

        $documents = new HomepageDocumentRepository($pdo);
        $headshotId = $documents->create([
            'document_key' => 'hero_headshot',
            'document_type' => 'headshot',
            'title' => 'Headshot',
            'file_path' => '/uploads/headshot.jpg',
            'mime_type' => 'image/jpeg',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $cvId = $documents->create([
            'document_key' => 'footer_cv',
            'document_type' => 'cv_pdf',
            'title' => 'Download CV',
            'file_path' => '/uploads/cv.pdf',
            'mime_type' => 'application/pdf',
            'sort_order' => 20,
            'is_active' => 1,
        ]);
        (new HomepageHeroSettingsRepository($pdo))->replace([
            'site_title' => 'Site',
            'eyebrow' => 'Executive Profile',
            'title' => 'Hero Title',
            'summary_text' => 'Hero Summary',
            'supporting_text' => 'Hero Supporting Text',
            'profile_name' => 'Tony Smith',
            'profile_role' => 'Enterprise Systems Leader',
            'profile_location' => 'Auckland',
            'profile_availability' => 'Available',
            'open_to_work' => 1,
            'cta_mode' => 'register_request_chat',
            'primary_cta_label' => 'Register & request to chat',
            'primary_cta_url' => '/signup.php',
            'secondary_cta_label' => 'Login',
            'secondary_cta_url' => '/login.php',
            'headshot_document_id' => $headshotId,
        ]);
        (new HomepageFooterSettingsRepository($pdo))->replace([
            'heading' => 'Footer Heading',
            'body_text' => 'Footer Body',
            'contact_email' => 'tony@example.com',
            'contact_phone' => '123',
            'contact_location' => 'Auckland',
            'cv_document_id' => $cvId,
            'linkedin_url' => 'https://linkedin.com/in/tony',
            'github_url' => 'https://github.com/tony',
        ]);

        $modules = new HomepageModuleRepository($pdo);
        $richText = new ModuleRichTextPayloadRepository($pdo);
        $timeline = new ModuleTimelineEntryRepository($pdo);
        $pillCards = new ModulePillCardItemRepository($pdo);
        $caseStudies = new ModuleCaseStudyItemRepository($pdo);
        $listItems = new ModuleListItemRepository($pdo);
        $quoteCards = new ModuleQuoteCardItemRepository($pdo);
        $ctaBanners = new ModuleCtaBannerPayloadRepository($pdo);
        $mediaText = new ModuleMediaTextPayloadRepository($pdo);

        $summaryModuleId = $modules->create([
            'module_key' => 'executive_summary',
            'module_type' => 'rich_text',
            'eyebrow' => 'Executive summary',
            'title' => 'Core strengths',
            'intro_text' => 'Summary intro',
            'anchor_id' => 'executive-summary',
            'style_variant' => 'summary',
            'display_order' => 10,
            'is_active' => 1,
        ]);
        $richText->upsertForModule($summaryModuleId, [
            'body_text' => 'Trusted business and technology leadership.',
            'primary_cta_label' => 'Talk',
            'primary_cta_url' => '/signup.php',
        ]);

        $timelineModuleId = $modules->create([
            'module_key' => 'experience_timeline',
            'module_type' => 'timeline',
            'eyebrow' => 'Experience',
            'title' => 'Condensed timeline',
            'intro_text' => 'Timeline intro',
            'anchor_id' => 'experience',
            'style_variant' => 'timeline',
            'display_order' => 20,
            'is_active' => 1,
        ]);
        $timeline->replaceForModule($timelineModuleId, [[
            'title' => 'Senior Manager',
            'subtitle' => 'First Security',
            'meta' => '2021-present',
            'summary_text' => 'Leads systems and reporting.',
            'detail_text' => 'Inline detail.',
            'highlights' => ['Improved SLA performance', 'Reduced load times'],
        ]]);

        $listModuleId = $modules->create([
            'module_key' => 'certifications',
            'module_type' => 'list',
            'eyebrow' => 'Credentials',
            'title' => 'Certifications',
            'intro_text' => 'Cert intro',
            'anchor_id' => 'certifications',
            'style_variant' => 'cards',
            'display_order' => 30,
            'is_active' => 1,
        ]);
        $listItems->replaceForModule($listModuleId, [[
            'item_title' => 'Azure Fundamentals',
            'item_body' => 'Microsoft',
            'item_meta' => '2024',
        ]]);

        $pillCardsModuleId = $modules->create([
            'module_key' => 'technology_groups',
            'module_type' => 'pill_cards',
            'eyebrow' => 'Capability',
            'title' => 'Grouped capability',
            'intro_text' => 'Capability intro',
            'anchor_id' => 'technology-groups',
            'style_variant' => 'grouped-capability',
            'display_order' => 40,
            'is_active' => 1,
        ]);
        $pillCards->replaceForModule($pillCardsModuleId, [[
            'title' => 'SQL Server',
            'body_text' => 'Advanced SQL',
            'badge_text' => 'Core',
        ]]);

        $caseStudyModuleId = $modules->create([
            'module_key' => 'featured_portfolio',
            'module_type' => 'case_studies',
            'eyebrow' => 'Portfolio',
            'title' => 'Featured work',
            'intro_text' => 'Portfolio intro',
            'anchor_id' => 'portfolio',
            'style_variant' => 'cards',
            'display_order' => 50,
            'is_active' => 1,
        ]);
        $caseStudies->replaceForModule($caseStudyModuleId, [[
            'title' => 'Website template',
            'category_text' => 'Case study',
            'summary_text' => 'Reusable personal website starter.',
            'outcome_text' => 'Forkable starter',
        ]]);

        $quoteModuleId = $modules->create([
            'module_key' => 'testimonials',
            'module_type' => 'quote_cards',
            'eyebrow' => 'Testimonials',
            'title' => 'Selected references',
            'intro_text' => 'Testimonial intro',
            'anchor_id' => 'testimonials',
            'style_variant' => 'quotes',
            'display_order' => 60,
            'is_active' => 1,
        ]);
        $quoteCards->replaceForModule($quoteModuleId, [[
            'quote_text' => 'Trusted leader.',
            'attribution_name' => 'Wade Wilson',
            'attribution_role' => 'GM',
            'attribution_context' => 'First Security',
        ]]);

        $ctaModuleId = $modules->create([
            'module_key' => 'chatbot_teaser',
            'module_type' => 'cta_banner',
            'eyebrow' => 'CTA',
            'title' => 'Banner',
            'intro_text' => 'Banner intro',
            'anchor_id' => 'cta',
            'style_variant' => 'banner',
            'display_order' => 70,
            'is_active' => 1,
        ]);
        $ctaBanners->upsertForModule($ctaModuleId, [
            'body_text' => 'Talk to us',
            'primary_cta_label' => 'Talk',
            'primary_cta_url' => '/signup.php',
        ]);

        $mediaDocumentId = $documents->create([
            'document_key' => 'module_visual',
            'document_type' => 'module_media',
            'title' => 'Module visual',
            'file_path' => '/uploads/module.png',
            'mime_type' => 'image/png',
            'sort_order' => 30,
            'is_active' => 1,
        ]);
        $mediaModuleId = $modules->create([
            'module_key' => 'diagram',
            'module_type' => 'media_text',
            'eyebrow' => 'Visual',
            'title' => 'Diagram',
            'intro_text' => 'Media intro',
            'anchor_id' => 'diagram',
            'style_variant' => 'split',
            'media_document_id' => $mediaDocumentId,
            'display_order' => 80,
            'is_active' => 1,
        ]);
        $mediaText->upsertForModule($mediaModuleId, [
            'body_text' => 'Diagram copy',
            'media_position' => 'right',
        ]);

        $hiddenModuleId = $modules->create([
            'module_key' => 'hidden_callout',
            'module_type' => 'rich_text',
            'eyebrow' => 'Optional CTA',
            'title' => 'Hidden callout',
            'intro_text' => 'Should not render',
            'anchor_id' => 'hidden-callout',
            'style_variant' => 'callout',
            'display_order' => 70,
            'is_active' => 0,
        ]);
        $richText->upsertForModule($hiddenModuleId, [
            'body_text' => 'Hidden body',
        ]);

        $service = new ModularHomepageContentService(
            new HomepageHeroSettingsRepository($pdo),
            new HomepageFooterSettingsRepository($pdo),
            $documents,
            $modules,
            $richText,
            $timeline,
            $pillCards,
            $caseStudies,
            $listItems,
            $quoteCards,
            $ctaBanners,
            $mediaText
        );

        $homepage = $service->homepage();

        $this->assertSame('Hero Title', $homepage['hero']['title']);
        $this->assertSame('/uploads/headshot.jpg', $homepage['hero']['headshot']['public_url']);
        $this->assertSame('Footer Heading', $homepage['footer_contact']['heading']);
        $this->assertSame('/uploads/cv.pdf', $homepage['footer_contact']['cv']['public_url']);

        $this->assertCount(8, $homepage['modules'], 'Only active homepage modules should be assembled.');
        $this->assertSame('executive_summary', $homepage['modules'][0]['module_key']);
        $this->assertSame('experience_timeline', $homepage['modules'][1]['module_key']);
        $this->assertSame('Trusted business and technology leadership.', $homepage['modules'][0]['content']['body_text']);
        $this->assertSame('Improved SLA performance', $homepage['modules'][1]['items'][0]['highlights'][0]);
        $this->assertSame('Azure Fundamentals', $homepage['modules'][2]['items'][0]['item_title']);
        $this->assertSame('SQL Server', $homepage['modules'][3]['items'][0]['title']);
        $this->assertSame('Website template', $homepage['modules'][4]['items'][0]['title']);
        $this->assertSame('Trusted leader.', $homepage['modules'][5]['items'][0]['quote_text']);
        $this->assertSame('Talk to us', $homepage['modules'][6]['content']['body_text']);
        $this->assertSame('Diagram copy', $homepage['modules'][7]['content']['body_text']);
    }
}
