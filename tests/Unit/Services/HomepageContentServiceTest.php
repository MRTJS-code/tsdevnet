<?php
declare(strict_types=1);

use App\Repositories\ContentBlockRepository;
use App\Repositories\ContentItemRepository;
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
use App\Services\HomepageContentService;

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
            'title' => 'Headshot',
            'file_path' => '/uploads/headshot.jpg',
            'mime_type' => 'image/jpeg',
            'sort_order' => 10,
            'is_active' => 1,
        ]);
        $cvId = $documents->create([
            'document_key' => 'footer_cv',
            'title' => 'Download CV',
            'file_path' => '/uploads/cv.pdf',
            'mime_type' => 'application/pdf',
            'sort_order' => 20,
            'is_active' => 1,
        ]);

        $pdo->exec("
            INSERT INTO site_settings (
                id, site_title, hero_eyebrow, hero_headline, hero_subheadline, hero_supporting_text,
                profile_name, profile_role, profile_location, profile_availability, open_to_work,
                primary_cta_mode, primary_cta_label, primary_cta_url, secondary_cta_label, secondary_cta_url,
                linkedin_url, github_url, contact_email, contact_phone, contact_location,
                footer_heading, footer_body, chatbot_teaser_enabled, chatbot_teaser_label,
                headshot_document_id, cv_document_id, created_at, updated_at
            ) VALUES (
                1, 'Site', 'Executive Profile', 'Hero Title', 'Hero Summary', 'Hero Supporting Text',
                'Tony Smith', 'Enterprise Systems Leader', 'Auckland', 'Available', 1,
                'register_request_chat', 'Register & request to chat', '/signup.php', 'Login', '/login.php',
                'https://linkedin.com/in/tony', 'https://github.com/tony', 'tony@example.com', '123', 'Auckland',
                'Footer Heading', 'Footer Body', 0, '',
                {$headshotId}, {$cvId}, '2026-01-01 00:00:00', '2026-01-01 00:00:00'
            )
        ");

        $experience = new HomepageExperienceRepository($pdo);
        $experienceHighlights = new HomepageExperienceHighlightRepository($pdo);
        $experienceId = $experience->create([
            'role_title' => 'Senior Manager',
            'organisation' => 'First Security',
            'start_date' => '2021-01-01',
            'end_date' => null,
            'is_current' => 1,
            'summary' => 'Leads systems and reporting.',
            'display_order' => 10,
            'is_active' => 1,
        ]);
        $experienceHighlights->replaceForExperience($experienceId, ['Improved SLA performance', 'Reduced load times']);

        $certifications = new HomepageCertificationRepository($pdo);
        $certifications->create([
            'certification_name' => 'Azure Fundamentals',
            'issuer' => 'Microsoft',
            'issued_year' => 2024,
            'display_order' => 10,
            'is_active' => 1,
        ]);

        $technologyGroups = new HomepageTechnologyGroupRepository($pdo);
        $coreGroupId = $technologyGroups->create([
            'group_key' => 'core_strengths',
            'group_label' => 'Core strengths',
            'intro_text' => 'Strongest capabilities.',
            'display_order' => 10,
            'is_active' => 1,
        ]);
        $supportingGroupId = $technologyGroups->create([
            'group_key' => 'supporting_tools',
            'group_label' => 'Supporting tools / platforms',
            'intro_text' => 'Supporting platforms.',
            'display_order' => 20,
            'is_active' => 1,
        ]);

        $technologyEntries = new HomepageTechnologyEntryRepository($pdo);
        $technologyEntries->create([
            'group_id' => $coreGroupId,
            'label' => 'SQL Server',
            'detail_text' => 'Advanced SQL',
            'display_order' => 10,
            'is_active' => 1,
        ]);
        $technologyEntries->create([
            'group_id' => $supportingGroupId,
            'label' => 'Power BI',
            'detail_text' => 'Reporting',
            'display_order' => 10,
            'is_active' => 1,
        ]);

        $portfolio = new HomepagePortfolioRepository($pdo);
        $portfolio->create([
            'title' => 'Website template',
            'slug' => 'website-template',
            'short_summary' => 'Reusable personal website starter.',
            'category' => 'Case study',
            'outcome_text' => 'Forkable starter',
            'is_featured' => 1,
            'display_order' => 10,
            'is_active' => 1,
        ]);

        $testimonials = new HomepageTestimonialRepository($pdo);
        $testimonials->create([
            'quote_text' => 'Trusted leader.',
            'person_name' => 'Wade Wilson',
            'person_title' => 'GM',
            'organisation' => 'First Security',
            'is_featured' => 1,
            'display_order' => 10,
            'is_active' => 1,
        ]);

        $modules = new HomepageModuleRepository($pdo);
        $richTextSections = new ModuleRichTextSectionRepository($pdo);

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
        $richTextSections->upsertForModule($summaryModuleId, [
            'body_text' => 'Trusted business and technology leadership.',
            'cta_label' => 'Talk',
            'cta_url' => '/signup.php',
        ]);

        $modules->create([
            'module_key' => 'experience_timeline',
            'module_type' => 'experience_timeline',
            'eyebrow' => 'Experience',
            'title' => 'Condensed timeline',
            'intro_text' => 'Timeline intro',
            'anchor_id' => 'experience',
            'style_variant' => 'timeline',
            'display_order' => 20,
            'is_active' => 1,
        ]);
        $modules->create([
            'module_key' => 'certifications',
            'module_type' => 'certifications',
            'eyebrow' => 'Credentials',
            'title' => 'Certifications',
            'intro_text' => 'Cert intro',
            'anchor_id' => 'certifications',
            'style_variant' => 'cards',
            'display_order' => 30,
            'is_active' => 1,
        ]);
        $modules->create([
            'module_key' => 'technology_groups',
            'module_type' => 'technology_groups',
            'eyebrow' => 'Capability',
            'title' => 'Grouped capability',
            'intro_text' => 'Capability intro',
            'anchor_id' => 'technology-groups',
            'style_variant' => 'grouped-capability',
            'display_order' => 40,
            'is_active' => 1,
        ]);
        $modules->create([
            'module_key' => 'featured_portfolio',
            'module_type' => 'featured_portfolio',
            'eyebrow' => 'Portfolio',
            'title' => 'Featured work',
            'intro_text' => 'Portfolio intro',
            'anchor_id' => 'portfolio',
            'style_variant' => 'cards',
            'display_order' => 50,
            'is_active' => 1,
        ]);
        $modules->create([
            'module_key' => 'testimonials',
            'module_type' => 'testimonials',
            'eyebrow' => 'Testimonials',
            'title' => 'Selected references',
            'intro_text' => 'Testimonial intro',
            'anchor_id' => 'testimonials',
            'style_variant' => 'quotes',
            'display_order' => 60,
            'is_active' => 1,
        ]);
        $hiddenModuleId = $modules->create([
            'module_key' => 'hidden_callout',
            'module_type' => 'cta_info',
            'eyebrow' => 'Optional CTA',
            'title' => 'Hidden callout',
            'intro_text' => 'Should not render',
            'anchor_id' => 'hidden-callout',
            'style_variant' => 'callout',
            'display_order' => 70,
            'is_active' => 0,
        ]);
        $richTextSections->upsertForModule($hiddenModuleId, [
            'body_text' => 'Hidden body',
        ]);

        $service = new HomepageContentService(
            new SiteSettingRepository($pdo),
            $experience,
            $experienceHighlights,
            $certifications,
            $technologyGroups,
            $technologyEntries,
            $portfolio,
            $testimonials,
            $documents,
            $modules,
            $richTextSections
        );

        $homepage = $service->homepage();

        $this->assertSame('Hero Title', $homepage['hero']['title']);
        $this->assertSame('/uploads/headshot.jpg', $homepage['hero']['headshot']['public_url']);
        $this->assertSame('Footer Heading', $homepage['footer_contact']['heading']);
        $this->assertSame('/uploads/cv.pdf', $homepage['footer_contact']['cv']['public_url']);

        $this->assertCount(6, $homepage['modules'], 'Only active homepage modules should be assembled.');
        $this->assertSame('executive_summary', $homepage['modules'][0]['module_key']);
        $this->assertSame('experience_timeline', $homepage['modules'][1]['module_key']);
        $this->assertSame('Trusted business and technology leadership.', $homepage['modules'][0]['content']['body_text']);
        $this->assertSame('Improved SLA performance', $homepage['modules'][1]['items'][0]['highlights'][0]);
        $this->assertSame('Azure Fundamentals', $homepage['modules'][2]['items'][0]['certification_name']);
        $this->assertSame('SQL Server', $homepage['modules'][3]['items'][0]['items'][0]['label']);
        $this->assertSame('Website template', $homepage['modules'][4]['items'][0]['title']);
        $this->assertSame('Trusted leader.', $homepage['modules'][5]['items'][0]['quote_text']);
    }
}
