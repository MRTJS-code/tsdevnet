<?php
declare(strict_types=1);

namespace App\Services;

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

final class ModularHomepageContentService
{
    public function __construct(
        private HomepageHeroSettingsRepository $heroSettings,
        private HomepageFooterSettingsRepository $footerSettings,
        private HomepageDocumentRepository $documents,
        private HomepageModuleRepository $modules,
        private ModuleRichTextPayloadRepository $richTextPayloads,
        private ModuleTimelineEntryRepository $timelineEntries,
        private ModulePillCardItemRepository $pillCards,
        private ModuleCaseStudyItemRepository $caseStudies,
        private ModuleListItemRepository $listItems,
        private ModuleQuoteCardItemRepository $quoteCards,
        private ModuleCtaBannerPayloadRepository $ctaBanners,
        private ModuleMediaTextPayloadRepository $mediaTextPayloads
    ) {
    }

    public function homepage(): array
    {
        $documentsById = $this->documentsById();
        $hero = $this->hero($documentsById);
        $footer = $this->footer($documentsById);
        $modules = $this->buildModules($documentsById);

        return [
            'hero' => $hero,
            'modules' => $modules,
            'footer_contact' => $footer,
        ];
    }

    private function hero(array $documentsById): array
    {
        $defaults = $this->defaults()['hero'];
        $settings = $this->heroSettings->get() ?? [];
        $headshot = $documentsById[(int) ($settings['headshot_document_id'] ?? 0)] ?? $defaults['headshot'];

        return [
            'site_title' => (string) ($settings['site_title'] ?? $defaults['site_title']),
            'eyebrow' => (string) ($settings['eyebrow'] ?? $defaults['eyebrow']),
            'title' => (string) ($settings['title'] ?? $defaults['title']),
            'summary' => (string) ($settings['summary_text'] ?? $defaults['summary']),
            'supporting_text' => (string) ($settings['supporting_text'] ?? $defaults['supporting_text']),
            'primary_cta' => [
                'label' => (string) ($settings['primary_cta_label'] ?? $defaults['primary_cta']['label']),
                'url' => (string) ($settings['primary_cta_url'] ?? $defaults['primary_cta']['url']),
            ],
            'secondary_cta' => [
                'label' => (string) ($settings['secondary_cta_label'] ?? $defaults['secondary_cta']['label']),
                'url' => (string) ($settings['secondary_cta_url'] ?? $defaults['secondary_cta']['url']),
            ],
            'profile_card' => [
                'name' => (string) ($settings['profile_name'] ?? $defaults['profile_card']['name']),
                'role' => (string) ($settings['profile_role'] ?? $defaults['profile_card']['role']),
                'location' => (string) ($settings['profile_location'] ?? $defaults['profile_card']['location']),
                'availability' => (string) ($settings['profile_availability'] ?? $defaults['profile_card']['availability']),
            ],
            'headshot' => $headshot,
            'cta_mode' => (string) ($settings['cta_mode'] ?? $defaults['cta_mode']),
            'open_to_work' => !empty($settings['open_to_work']),
        ];
    }

    private function footer(array $documentsById): array
    {
        $defaults = $this->defaults()['footer_contact'];
        $settings = $this->footerSettings->get() ?? [];
        $cv = $documentsById[(int) ($settings['cv_document_id'] ?? 0)] ?? $defaults['cv'];

        return [
            'heading' => (string) ($settings['heading'] ?? $defaults['heading']),
            'body_text' => (string) ($settings['body_text'] ?? $defaults['body_text']),
            'email' => (string) ($settings['contact_email'] ?? $defaults['email']),
            'phone' => (string) ($settings['contact_phone'] ?? $defaults['phone']),
            'location' => (string) ($settings['contact_location'] ?? $defaults['location']),
            'cv' => $cv,
            'links' => [
                [
                    'title' => 'LinkedIn',
                    'public_url' => (string) ($settings['linkedin_url'] ?? $defaults['links'][0]['public_url']),
                ],
                [
                    'title' => 'GitHub',
                    'public_url' => (string) ($settings['github_url'] ?? $defaults['links'][1]['public_url']),
                ],
            ],
        ];
    }

    private function buildModules(array $documentsById): array
    {
        $moduleRows = $this->modules->listActive();
        if ($moduleRows === []) {
            return $this->defaults()['modules'];
        }

        $moduleIds = array_map(static fn (array $row): int => (int) $row['id'], $moduleRows);
        $timelineByModule = $this->timelineEntries->listGroupedByModuleIds($moduleIds);
        $pillCardsByModule = $this->pillCards->listGroupedByModuleIds($moduleIds);
        $caseStudiesByModule = $this->caseStudies->listGroupedByModuleIds($moduleIds);
        $listItemsByModule = $this->listItems->listGroupedByModuleIds($moduleIds);
        $quoteCardsByModule = $this->quoteCards->listGroupedByModuleIds($moduleIds);
        $modules = [];

        foreach ($moduleRows as $module) {
            $moduleId = (int) $module['id'];
            $type = (string) $module['module_type'];
            $base = [
                'id' => $moduleId,
                'module_key' => $module['module_key'],
                'module_type' => $type,
                'eyebrow' => (string) ($module['eyebrow'] ?? ''),
                'title' => (string) ($module['title'] ?? ''),
                'intro_text' => (string) ($module['intro_text'] ?? ''),
                'anchor_id' => (string) ($module['anchor_id'] ?? ''),
                'style_variant' => (string) ($module['style_variant'] ?? ''),
                'display_order' => (int) ($module['display_order'] ?? 0),
                'is_active' => !empty($module['is_active']),
                'media' => $documentsById[(int) ($module['media_document_id'] ?? 0)] ?? null,
            ];

            $payload = match ($type) {
                'rich_text' => ['content' => $this->normalizeCtaPayload($this->richTextPayloads->findByModuleId($moduleId))],
                'timeline' => ['items' => $timelineByModule[$moduleId] ?? []],
                'pill_cards' => ['items' => $pillCardsByModule[$moduleId] ?? []],
                'case_studies' => ['items' => $caseStudiesByModule[$moduleId] ?? []],
                'list' => ['items' => $listItemsByModule[$moduleId] ?? []],
                'quote_cards' => ['items' => $quoteCardsByModule[$moduleId] ?? []],
                'cta_banner' => ['content' => $this->normalizeCtaPayload($this->ctaBanners->findByModuleId($moduleId))],
                'media_text' => ['content' => $this->normalizeMediaTextPayload($this->mediaTextPayloads->findByModuleId($moduleId))],
                default => ['items' => []],
            };

            $modules[] = $base + $payload;
        }

        return $modules;
    }

    private function normalizeCtaPayload(?array $row): array
    {
        return [
            'body_text' => (string) ($row['body_text'] ?? ''),
            'primary_cta' => [
                'label' => (string) ($row['primary_cta_label'] ?? ''),
                'url' => (string) ($row['primary_cta_url'] ?? ''),
            ],
            'secondary_cta' => [
                'label' => (string) ($row['secondary_cta_label'] ?? ''),
                'url' => (string) ($row['secondary_cta_url'] ?? ''),
            ],
        ];
    }

    private function normalizeMediaTextPayload(?array $row): array
    {
        return $this->normalizeCtaPayload($row) + [
            'media_position' => (string) ($row['media_position'] ?? 'right'),
        ];
    }

    private function documentsById(): array
    {
        $documents = [];
        foreach ($this->documents->listAll(true) as $document) {
            $documents[(int) $document['id']] = [
                'id' => (int) $document['id'],
                'document_key' => $document['document_key'],
                'document_type' => $document['document_type'],
                'title' => $document['title'],
                'description_text' => $document['description_text'] ?? '',
                'public_url' => $document['file_path'] ?: ($document['external_url'] ?? ''),
            ];
        }

        return $documents;
    }

    private function defaults(): array
    {
        return [
            'hero' => [
                'site_title' => 'Professional Profile and Recruiter Portal',
                'eyebrow' => 'Executive Profile',
                'title' => 'A reusable executive-profile homepage with modular blocks.',
                'summary' => 'This homepage stays generic in-repo while each fork controls block meaning through ordered content modules.',
                'supporting_text' => 'Hero and footer stay fixed by design; the middle page is assembled from reusable rendering blocks.',
                'primary_cta' => ['label' => 'Register and request to chat', 'url' => '/signup.php'],
                'secondary_cta' => ['label' => 'Recruiter portal login', 'url' => '/login.php'],
                'profile_card' => [
                    'name' => 'Profile Name',
                    'role' => 'Executive technology and delivery leader',
                    'location' => 'Region or remote availability',
                    'availability' => 'CTA state configurable through admin',
                ],
                'headshot' => [
                    'document_key' => 'hero_headshot',
                    'document_type' => 'headshot',
                    'title' => 'Profile headshot placeholder',
                    'description_text' => 'Upload a headshot through admin.',
                    'public_url' => '',
                ],
                'cta_mode' => 'register_request_chat',
            ],
            'footer_contact' => [
                'heading' => 'Let’s discuss leadership, delivery, and recruiter access.',
                'body_text' => 'Use admin to maintain contact details, footer links, and the downloadable CV.',
                'email' => 'hello@example.com',
                'phone' => '+00 000 000 000',
                'location' => 'Region or remote availability',
                'cv' => [
                    'document_key' => 'footer_cv',
                    'document_type' => 'cv_pdf',
                    'title' => 'Download CV',
                    'description_text' => 'Upload a PDF CV through admin.',
                    'public_url' => '',
                ],
                'links' => [
                    ['title' => 'LinkedIn', 'public_url' => 'https://www.linkedin.com/'],
                    ['title' => 'GitHub', 'public_url' => 'https://github.com/'],
                ],
            ],
            'modules' => [],
        ];
    }
}
