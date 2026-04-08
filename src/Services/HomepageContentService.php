<?php
declare(strict_types=1);

namespace App\Services;

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

final class HomepageContentService
{
    public function __construct(
        private SiteSettingRepository $settings,
        private HomepageExperienceRepository $experience,
        private HomepageExperienceHighlightRepository $experienceHighlights,
        private HomepageCertificationRepository $certifications,
        private HomepageTechnologyGroupRepository $technologyGroups,
        private HomepageTechnologyEntryRepository $technologyEntries,
        private HomepagePortfolioRepository $portfolio,
        private HomepageTestimonialRepository $testimonials,
        private HomepageDocumentRepository $documents,
        private HomepageModuleRepository $modules,
        private ModuleRichTextSectionRepository $richTextSections
    ) {
    }

    public function homepage(): array
    {
        $defaults = $this->defaultHomepage();
        $settings = $this->settings->getAllIndexed();
        $singleton = $this->settings->getSingleton() ?? [];
        $documents = $this->documentsByKey();

        return [
            'hero' => [
                'eyebrow' => $this->settingValue($settings, 'hero_eyebrow', $defaults['hero']['eyebrow']),
                'title' => $this->settingValue($settings, 'hero_title', $defaults['hero']['title']),
                'summary' => $this->settingValue($settings, 'hero_summary', $defaults['hero']['summary']),
                'supporting_text' => $this->settingValue($settings, 'hero_supporting_text', $defaults['hero']['supporting_text']),
                'primary_cta' => [
                    'label' => $this->settingValue($settings, 'cta_primary_label', $defaults['hero']['primary_cta']['label']),
                    'url' => $this->settingValue($settings, 'cta_primary_url', $defaults['hero']['primary_cta']['url']),
                ],
                'secondary_cta' => [
                    'label' => $this->settingValue($settings, 'cta_secondary_label', $defaults['hero']['secondary_cta']['label']),
                    'url' => $this->settingValue($settings, 'cta_secondary_url', $defaults['hero']['secondary_cta']['url']),
                ],
                'profile_card' => [
                    'name' => $this->settingValue($settings, 'profile_name', $defaults['hero']['profile_card']['name']),
                    'role' => $this->settingValue($settings, 'profile_role', $defaults['hero']['profile_card']['role']),
                    'location' => $this->settingValue($settings, 'profile_location', $defaults['hero']['profile_card']['location']),
                    'availability' => $this->settingValue($settings, 'profile_availability', $defaults['hero']['profile_card']['availability']),
                ],
                'headshot' => $this->resolveHeadshotDocument($singleton, $documents, $defaults['hero']['headshot']),
                'cta_mode' => $this->settingValue($settings, 'cta_mode', $defaults['hero']['cta_mode']),
            ],
            'modules' => $this->buildModules($defaults),
            'footer_contact' => [
                'heading' => $this->settingValue($settings, 'footer_heading', $defaults['footer_contact']['heading']),
                'body_text' => $this->settingValue($settings, 'footer_body', $defaults['footer_contact']['body_text']),
                'email' => $this->settingValue($settings, 'contact_email', $defaults['footer_contact']['email']),
                'phone' => $this->settingValue($settings, 'contact_phone', $defaults['footer_contact']['phone']),
                'location' => $this->settingValue($settings, 'contact_location', $defaults['footer_contact']['location']),
                'cv' => $this->resolveCvDocument($singleton, $documents, $defaults['footer_contact']['cv']),
                'links' => $this->footerLinks($settings, $defaults['footer_contact']['links']),
            ],
        ];
    }

    private function buildModules(array $defaults): array
    {
        $moduleRows = $this->modules->listActive();
        if ($moduleRows === []) {
            return $defaults['modules'];
        }

        $technologyGroups = $this->technologyGroupsByKey($defaults['technology_groups']);
        $experienceItems = $this->normalizeExperience($this->experience->listAll(true), $defaults['experience_timeline']);
        $certificationItems = $this->normalizeCertifications($this->certifications->listAll(true), $defaults['certifications']);
        $portfolioItems = $this->normalizePortfolio($this->portfolio->listAll(true), $defaults['portfolio_items']);
        $testimonialItems = $this->normalizeTestimonials($this->testimonials->listAll(true), $defaults['testimonials']);
        $modules = [];

        foreach ($moduleRows as $module) {
            $moduleType = (string) $module['module_type'];
            $modules[] = match ($moduleType) {
                'experience_timeline' => $this->mapModule($module, ['items' => $experienceItems]),
                'certifications' => $this->mapModule($module, ['items' => $certificationItems]),
                'technology_groups' => $this->mapModule($module, ['items' => array_values($technologyGroups)]),
                'featured_portfolio' => $this->mapModule($module, [
                    'items' => array_values(array_filter(
                        $portfolioItems,
                        static fn (array $item): bool => !array_key_exists('is_featured', $item) || !empty($item['is_featured'])
                    )),
                ]),
                'testimonials' => $this->mapModule($module, ['items' => $testimonialItems]),
                'rich_text', 'cta_info' => $this->mapModule($module, ['content' => $this->normalizeRichTextContent((int) $module['id'])]),
                default => $this->mapModule($module, []),
            };
        }

        return $modules;
    }

    private function mapModule(array $module, array $payload): array
    {
        return [
            'id' => (int) $module['id'],
            'module_key' => $module['module_key'],
            'module_type' => $module['module_type'],
            'eyebrow' => $module['eyebrow'] ?? '',
            'title' => $module['title'] ?? '',
            'intro_text' => $module['intro_text'] ?? '',
            'anchor_id' => $module['anchor_id'] ?? '',
            'style_variant' => $module['style_variant'] ?? '',
            'group_key' => $module['group_key'] ?? '',
            'display_order' => (int) ($module['display_order'] ?? 0),
            'is_active' => !empty($module['is_active']),
        ] + $payload;
    }

    private function normalizeRichTextContent(int $moduleId): array
    {
        $content = $this->richTextSections->findByModuleId($moduleId) ?? [];

        return [
            'body_text' => (string) ($content['body_text'] ?? ''),
            'cta' => [
                'label' => (string) ($content['cta_label'] ?? ''),
                'url' => (string) ($content['cta_url'] ?? ''),
            ],
            'secondary_cta' => [
                'label' => (string) ($content['secondary_cta_label'] ?? ''),
                'url' => (string) ($content['secondary_cta_url'] ?? ''),
            ],
        ];
    }

    private function documentsByKey(): array
    {
        $documents = [];
        foreach ($this->documents->listAll(true) as $document) {
            $documents[$document['document_key']] = $this->normalizeDocument($document);
        }

        return $documents;
    }

    private function technologyGroupsByKey(array $defaults): array
    {
        $groups = $this->technologyGroups->listAll(true);
        if ($groups === []) {
            return $defaults;
        }

        $groupIds = array_map(static fn (array $group): int => (int) $group['id'], $groups);
        $entriesByGroupId = $this->technologyEntries->listGroupedByGroupIds($groupIds, true);
        $normalized = [];

        foreach ($groups as $group) {
            $normalized[$group['group_key']] = [
                'id' => (int) $group['id'],
                'group_key' => $group['group_key'],
                'title' => $group['title'],
                'intro_text' => $group['intro_text'] ?? '',
                'items' => array_map(static function (array $entry): array {
                    return [
                        'id' => (int) $entry['id'],
                        'label' => $entry['label'],
                        'detail_text' => $entry['detail_text'] ?? '',
                    ];
                }, $entriesByGroupId[(int) $group['id']] ?? []),
            ];
        }

        return $normalized;
    }

    private function normalizeExperience(array $rows, array $defaults): array
    {
        if ($rows === []) {
            return $defaults;
        }

        $highlightsByExperienceId = $this->experienceHighlights->listByExperienceIds(array_map(
            static fn (array $row): int => (int) $row['id'],
            $rows
        ));

        return array_map(static function (array $row) use ($highlightsByExperienceId): array {
            return [
                'id' => (int) $row['id'],
                'role_title' => $row['role_title'],
                'organisation' => $row['organisation'] ?? $row['company_name'],
                'period_label' => $row['period_label'],
                'summary' => $row['summary'] ?? '',
                'highlight_text' => $row['highlight_text'] ?? '',
                'highlights' => array_map(
                    static fn (array $highlight): string => $highlight['highlight_text'],
                    $highlightsByExperienceId[(int) $row['id']] ?? []
                ),
            ];
        }, $rows);
    }

    private function normalizeCertifications(array $rows, array $defaults): array
    {
        if ($rows === []) {
            return $defaults;
        }

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'certification_name' => $row['certification_name'],
                'issuer' => $row['issuer'] ?? '',
                'issued_label' => $row['issued_label'] ?? '',
                'credential_url' => $row['credential_url'] ?? '',
            ];
        }, $rows);
    }

    private function normalizePortfolio(array $rows, array $defaults): array
    {
        if ($rows === []) {
            return $defaults;
        }

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'summary' => $row['summary'] ?? '',
                'outcome' => $row['outcome'] ?? '',
                'category' => $row['category'] ?? '',
                'problem_text' => $row['problem_text'] ?? '',
                'approach_text' => $row['approach_text'] ?? '',
                'tech_text' => $row['tech_text'] ?? '',
                'repo_url' => $row['repo_url'] ?? '',
                'demo_url' => $row['demo_url'] ?? '',
                'link_url' => $row['link_url'] ?? '',
                'link_label' => $row['link_label'] ?? '',
                'is_featured' => !empty($row['is_featured']),
            ];
        }, $rows);
    }

    private function normalizeTestimonials(array $rows, array $defaults): array
    {
        if ($rows === []) {
            return $defaults;
        }

        return array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'quote_text' => $row['quote_text'],
                'person_name' => $row['person_name'],
                'person_title' => $row['person_title'] ?? '',
                'organisation' => $row['organisation'] ?? '',
            ];
        }, $rows);
    }

    private function normalizeDocument(array $document): array
    {
        return [
            'id' => (int) $document['id'],
            'document_key' => $document['document_key'],
            'document_type' => $document['document_type'],
            'title' => $document['title'],
            'description_text' => $document['description_text'] ?? '',
            'file_path' => $document['file_path'] ?? '',
            'external_url' => $document['external_url'] ?? '',
            'mime_type' => $document['mime_type'] ?? '',
            'file_size_bytes' => isset($document['file_size_bytes']) ? (int) $document['file_size_bytes'] : null,
            'public_url' => $document['file_path'] ?: ($document['external_url'] ?? ''),
        ];
    }

    private function footerLinks(array $settings, array $defaults): array
    {
        return [
            [
                'document_key' => 'linkedin',
                'title' => 'LinkedIn',
                'public_url' => $this->settingValue($settings, 'linkedin_url', $defaults['linkedin']['public_url']),
            ],
            [
                'document_key' => 'github',
                'title' => 'GitHub',
                'public_url' => $this->settingValue($settings, 'github_url', $defaults['github']['public_url']),
            ],
        ];
    }

    private function resolveHeadshotDocument(array $singleton, array $documents, array $default): array
    {
        $document = $this->resolveDocumentById($singleton['headshot_document_id'] ?? null);
        if ($document !== null) {
            return $document;
        }

        return $documents['hero_headshot'] ?? $default;
    }

    private function resolveCvDocument(array $singleton, array $documents, array $default): array
    {
        $document = $this->resolveDocumentById($singleton['cv_document_id'] ?? null);
        if ($document !== null) {
            return $document;
        }

        return $documents['footer_cv'] ?? $default;
    }

    private function resolveDocumentById(mixed $documentId): ?array
    {
        $id = (int) $documentId;
        if ($id <= 0) {
            return null;
        }

        $document = $this->documents->findById($id);
        return $document ? $this->normalizeDocument($document) : null;
    }

    private function settingValue(array $settings, string $key, string $default = ''): string
    {
        if (!isset($settings[$key])) {
            return $default;
        }

        return (string) ($settings[$key]['setting_value_text'] ?? $default);
    }

    private function defaultHomepage(): array
    {
        return [
            'hero' => [
                'eyebrow' => 'Executive Profile',
                'title' => 'A reusable executive-profile homepage with modular content blocks.',
                'summary' => 'This placeholder homepage stays generic in-repo while allowing each fork to manage profile content through admin.',
                'supporting_text' => 'Phase 1E moves the homepage to a fixed hero, ordered modules, and a fixed footer/contact region.',
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
                    'description_text' => 'Upload a headshot through admin to replace this placeholder state.',
                    'public_url' => '',
                ],
                'cta_mode' => 'register_request_chat',
            ],
            'modules' => [
                [
                    'id' => 0,
                    'module_key' => 'executive_summary',
                    'module_type' => 'rich_text',
                    'eyebrow' => 'Executive summary',
                    'title' => 'Core strengths',
                    'intro_text' => 'Use admin to frame the strongest capabilities between the hero and the timeline.',
                    'anchor_id' => 'executive-summary',
                    'style_variant' => 'summary',
                    'group_key' => '',
                    'display_order' => 10,
                    'is_active' => true,
                    'content' => [
                        'body_text' => 'Use admin to add a concise executive summary or information block.',
                        'cta' => ['label' => '', 'url' => ''],
                        'secondary_cta' => ['label' => '', 'url' => ''],
                    ],
                ],
                [
                    'id' => 0,
                    'module_key' => 'experience_timeline',
                    'module_type' => 'experience_timeline',
                    'eyebrow' => 'Experience',
                    'title' => 'Condensed timeline',
                    'intro_text' => '',
                    'anchor_id' => 'experience',
                    'style_variant' => 'timeline',
                    'group_key' => '',
                    'display_order' => 20,
                    'is_active' => true,
                    'items' => [[
                        'role_title' => 'Senior transformation leadership',
                        'organisation' => 'Organisation placeholder',
                        'period_label' => 'Recent phase',
                        'summary' => 'Use admin to enter a concise leadership timeline that highlights scope, scale, and delivery context.',
                        'highlight_text' => 'Outcome-oriented placeholder',
                        'highlights' => ['Outcome-oriented placeholder'],
                    ]],
                ],
                [
                    'id' => 0,
                    'module_key' => 'certifications',
                    'module_type' => 'certifications',
                    'eyebrow' => 'Credentials',
                    'title' => 'Certifications',
                    'intro_text' => 'Professional qualifications and current credentials.',
                    'anchor_id' => 'certifications',
                    'style_variant' => 'cards',
                    'group_key' => '',
                    'display_order' => 30,
                    'is_active' => true,
                    'items' => [[
                        'certification_name' => 'Professional certification placeholder',
                        'issuer' => 'Issuing body',
                        'issued_label' => 'Issued date',
                        'credential_url' => '',
                    ]],
                ],
                [
                    'id' => 0,
                    'module_key' => 'technology_groups',
                    'module_type' => 'technology_groups',
                    'eyebrow' => 'Capability',
                    'title' => 'Grouped capability',
                    'intro_text' => 'Supporting tools, strengths, and technology exposure grouped for quick scanning.',
                    'anchor_id' => 'technology-groups',
                    'style_variant' => 'grouped-capability',
                    'group_key' => '',
                    'display_order' => 40,
                    'is_active' => true,
                    'items' => [[
                        'group_key' => 'core_strengths',
                        'title' => 'Core strengths',
                        'intro_text' => 'High-confidence capabilities that should be foregrounded on the homepage.',
                        'items' => [
                            ['label' => 'Architecture leadership', 'detail_text' => 'Replace through admin'],
                            ['label' => 'Delivery governance', 'detail_text' => 'Replace through admin'],
                        ],
                    ]],
                ],
                [
                    'id' => 0,
                    'module_key' => 'featured_portfolio',
                    'module_type' => 'featured_portfolio',
                    'eyebrow' => 'Portfolio',
                    'title' => 'Featured work',
                    'intro_text' => '',
                    'anchor_id' => 'portfolio',
                    'style_variant' => 'cards',
                    'group_key' => '',
                    'display_order' => 50,
                    'is_active' => true,
                    'items' => [[
                        'title' => 'Featured initiative placeholder',
                        'summary' => 'Describe the initiative, environment, or transformation stream.',
                        'outcome' => 'Capture the business or delivery outcome in one compact statement.',
                        'link_url' => '',
                        'link_label' => '',
                    ]],
                ],
                [
                    'id' => 0,
                    'module_key' => 'testimonials',
                    'module_type' => 'testimonials',
                    'eyebrow' => 'Testimonials',
                    'title' => 'Selected references',
                    'intro_text' => '',
                    'anchor_id' => 'testimonials',
                    'style_variant' => 'quotes',
                    'group_key' => '',
                    'display_order' => 60,
                    'is_active' => true,
                    'items' => [[
                        'quote_text' => 'Add a concise testimonial through admin to validate leadership style, execution quality, or stakeholder confidence.',
                        'person_name' => 'Reference name',
                        'person_title' => 'Role title',
                        'organisation' => 'Organisation',
                    ]],
                ],
                [
                    'id' => 0,
                    'module_key' => 'chatbot_teaser',
                    'module_type' => 'cta_info',
                    'eyebrow' => 'Optional CTA',
                    'title' => 'Chatbot teaser placeholder',
                    'intro_text' => 'Optional CTA/info block above the footer.',
                    'anchor_id' => 'chatbot-teaser',
                    'style_variant' => 'callout',
                    'group_key' => '',
                    'display_order' => 70,
                    'is_active' => true,
                    'content' => [
                        'body_text' => 'A future gated assistant or teaser can be introduced above the footer without redesigning the page structure.',
                        'cta' => ['label' => '', 'url' => ''],
                        'secondary_cta' => ['label' => '', 'url' => ''],
                    ],
                ],
            ],
            'experience_timeline' => [[
                'role_title' => 'Senior transformation leadership',
                'organisation' => 'Organisation placeholder',
                'period_label' => 'Recent phase',
                'summary' => 'Use admin to enter a concise leadership timeline that highlights scope, scale, and delivery context.',
                'highlight_text' => 'Outcome-oriented placeholder',
                'highlights' => ['Outcome-oriented placeholder'],
            ]],
            'certifications' => [[
                'certification_name' => 'Professional certification placeholder',
                'issuer' => 'Issuing body',
                'issued_label' => 'Issued date',
                'credential_url' => '',
            ]],
            'technology_groups' => [
                'core_strengths' => [
                    'group_key' => 'core_strengths',
                    'title' => 'Core strengths',
                    'intro_text' => 'High-confidence capabilities that should be foregrounded on the homepage.',
                    'items' => [
                        ['label' => 'Architecture leadership', 'detail_text' => 'Replace through admin'],
                        ['label' => 'Delivery governance', 'detail_text' => 'Replace through admin'],
                    ],
                ],
                'supporting_tools' => [
                    'group_key' => 'supporting_tools',
                    'title' => 'Supporting tools / platforms',
                    'intro_text' => 'Platforms and tools used to support outcomes.',
                    'items' => [
                        ['label' => 'Cloud platforms', 'detail_text' => 'Replace through admin'],
                    ],
                ],
                'exposure_familiarity' => [
                    'group_key' => 'exposure_familiarity',
                    'title' => 'Exposure / familiarity',
                    'intro_text' => 'Adjacent technologies that add context without overstating depth.',
                    'items' => [
                        ['label' => 'Emerging tooling', 'detail_text' => 'Replace through admin'],
                    ],
                ],
            ],
            'portfolio_items' => [[
                'title' => 'Featured initiative placeholder',
                'summary' => 'Describe the initiative, environment, or transformation stream.',
                'outcome' => 'Capture the business or delivery outcome in one compact statement.',
                'link_url' => '',
                'link_label' => '',
            ]],
            'testimonials' => [[
                'quote_text' => 'Add a concise testimonial through admin to validate leadership style, execution quality, or stakeholder confidence.',
                'person_name' => 'Reference name',
                'person_title' => 'Role title',
                'organisation' => 'Organisation',
            ]],
            'footer_contact' => [
                'heading' => 'Let\'s discuss delivery, technology leadership, and recruiter access.',
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
                    'linkedin' => [
                        'document_key' => 'linkedin',
                        'document_type' => 'footer_link',
                        'title' => 'LinkedIn',
                        'public_url' => 'https://www.linkedin.com/',
                    ],
                    'github' => [
                        'document_key' => 'github',
                        'document_type' => 'footer_link',
                        'title' => 'GitHub',
                        'public_url' => 'https://github.com/',
                    ],
                ],
            ],
        ];
    }
}
