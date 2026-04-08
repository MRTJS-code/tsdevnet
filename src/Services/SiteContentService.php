<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentBlockRepository;
use App\Repositories\ContentItemRepository;
use App\Repositories\HomepageCertificationRepository;
use App\Repositories\HomepageDocumentRepository;
use App\Repositories\HomepageExperienceHighlightRepository;
use App\Repositories\HomepageExperienceRepository;
use App\Repositories\HomepagePortfolioRepository;
use App\Repositories\HomepageTechnologyEntryRepository;
use App\Repositories\HomepageTechnologyGroupRepository;
use App\Repositories\HomepageTestimonialRepository;
use App\Repositories\SiteSettingRepository;

final class SiteContentService
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
        private ContentBlockRepository $blocks,
        private ContentItemRepository $items
    ) {
    }

    public function homepage(): array
    {
        $defaults = $this->defaultHomepage();
        $settings = $this->settings->getAllIndexed();
        $singleton = $this->settings->getSingleton() ?? [];
        $documents = $this->documentsByKey();
        $flexBlocks = $this->flexibleBlocks();

        return [
            'site_settings' => [
                'profile_name' => $this->settingValue($settings, 'profile_name', $defaults['site_settings']['profile_name']),
                'profile_role' => $this->settingValue($settings, 'profile_role', $defaults['site_settings']['profile_role']),
                'profile_location' => $this->settingValue($settings, 'profile_location', $defaults['site_settings']['profile_location']),
                'contact_email' => $this->settingValue($settings, 'contact_email', $defaults['site_settings']['contact_email']),
                'contact_phone' => $this->settingValue($settings, 'contact_phone', $defaults['site_settings']['contact_phone']),
                'footer_heading' => $this->settingValue($settings, 'footer_heading', $defaults['site_settings']['footer_heading']),
                'footer_body' => $this->settingValue($settings, 'footer_body', $defaults['site_settings']['footer_body']),
                'cta_mode' => $this->settingValue($settings, 'cta_mode', $defaults['site_settings']['cta_mode']),
                'chatbot_teaser_enabled' => $this->boolSetting($settings, 'chatbot_teaser_enabled', $defaults['site_settings']['chatbot_teaser_enabled']),
                'chatbot_teaser_label' => $this->settingValue($settings, 'chatbot_teaser_label', $defaults['site_settings']['chatbot_teaser_label']),
                'flexible_sections' => $flexBlocks,
            ],
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
                'chatbot_teaser' => [
                    'enabled' => $this->boolSetting($settings, 'chatbot_teaser_enabled', $defaults['hero']['chatbot_teaser']['enabled']),
                    'label' => $this->settingValue($settings, 'chatbot_teaser_label', $defaults['hero']['chatbot_teaser']['label']),
                    'body_text' => $flexBlocks['chatbot_teaser']['body_text'] ?? $defaults['hero']['chatbot_teaser']['body_text'],
                ],
            ],
            'experience_timeline' => $this->normalizeExperience($this->experience->listAll(true), $defaults['experience_timeline']),
            'certifications' => $this->normalizeCertifications($this->certifications->listAll(true), $defaults['certifications']),
            'technology_groups' => $this->technologyGroups($defaults['technology_groups']),
            'portfolio_items' => $this->normalizePortfolio($this->portfolio->listAll(true), $defaults['portfolio_items']),
            'testimonials' => $this->normalizeTestimonials($this->testimonials->listAll(true), $defaults['testimonials']),
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

    private function normalizeBlock(array $block): array
    {
        return [
            'id' => (int) $block['id'],
            'section_key' => $block['section_key'],
            'title' => $block['title'] ?? '',
            'subtitle' => $block['subtitle'] ?? '',
            'body_text' => $block['body_text'] ?? '',
            'meta' => $this->decodeMeta($block['meta_json'] ?? null),
            'items' => [],
        ];
    }

    private function normalizeItem(array $item): array
    {
        return [
            'id' => (int) $item['id'],
            'item_key' => $item['item_key'] ?? '',
            'label' => $item['label'] ?? '',
            'title' => $item['title'] ?? '',
            'body_text' => $item['body_text'] ?? '',
            'link_url' => $item['link_url'] ?? '',
            'meta' => $this->decodeMeta($item['meta_json'] ?? null),
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

    private function flexibleBlocks(): array
    {
        $blocks = [];
        foreach ($this->blocks->listActive() as $block) {
            if (!in_array($block['section_key'], ['homepage_intro', 'chatbot_teaser'], true)) {
                continue;
            }

            $blocks[$block['section_key']] = $this->normalizeBlock($block);
        }

        if ($blocks === []) {
            return [];
        }

        $itemsByBlockId = $this->items->listGroupedByBlockIds(array_map(
            static fn (array $block): int => (int) $block['id'],
            array_values($blocks)
        ));

        foreach ($blocks as $key => $block) {
            $blocks[$key]['items'] = array_map(
                fn (array $item): array => $this->normalizeItem($item),
                $itemsByBlockId[(int) $block['id']] ?? []
            );
        }

        return $blocks;
    }

    private function technologyGroups(array $defaults): array
    {
        $groups = $this->technologyGroups->listAll(true);
        if ($groups === []) {
            return $defaults;
        }

        $groupIds = array_map(static fn (array $group): int => (int) $group['id'], $groups);
        $entriesByGroupId = $this->technologyEntries->listGroupedByGroupIds($groupIds, true);
        $normalized = [];

        foreach ($groups as $group) {
            $normalized[] = [
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
                'link_url' => $row['link_url'] ?? '',
                'link_label' => $row['link_label'] ?? '',
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

    private function boolSetting(array $settings, string $key, bool $default = false): bool
    {
        if (!isset($settings[$key])) {
            return $default;
        }

        $value = strtolower((string) ($settings[$key]['setting_value_text'] ?? ''));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function decodeMeta(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function defaultHomepage(): array
    {
        return [
            'site_settings' => [
                'profile_name' => 'Profile Name',
                'profile_role' => 'Executive technology and delivery leader',
                'profile_location' => 'Region or remote availability',
                'contact_email' => 'hello@example.com',
                'contact_phone' => '+00 000 000 000',
                'footer_heading' => 'Let\'s discuss delivery, technology leadership, and recruiter access.',
                'footer_body' => 'Replace this placeholder through admin with contact guidance, document links, and the preferred next step.',
                'cta_mode' => 'register_request_chat',
                'chatbot_teaser_enabled' => true,
                'chatbot_teaser_label' => 'Assistant pathway ready for the next phase',
            ],
            'hero' => [
                'eyebrow' => 'Executive Profile',
                'title' => 'A reusable executive-profile homepage with a typed content model.',
                'summary' => 'This placeholder homepage stays generic in-repo while allowing each fork to manage profile content through admin.',
                'supporting_text' => 'Phase 1C moves the homepage beyond generic content blocks so structured sections can be maintained cleanly without overloading JSON metadata.',
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
                'chatbot_teaser' => [
                    'enabled' => true,
                    'label' => 'Assistant pathway ready for the next phase',
                    'body_text' => 'A future gated assistant or teaser can be introduced without redesigning the hero or footer.',
                ],
            ],
            'experience_timeline' => [[
                'role_title' => 'Senior transformation leadership',
                'organisation' => 'Organisation placeholder',
                'period_label' => 'Recent phase',
                'summary' => 'Use admin to enter a concise leadership timeline that highlights scope, scale, and delivery context.',
                'highlight_text' => 'Outcome-oriented placeholder',
            ]],
            'certifications' => [[
                'certification_name' => 'Professional certification placeholder',
                'issuer' => 'Issuing body',
                'issued_label' => 'Issued date',
                'credential_url' => '',
            ]],
            'technology_groups' => [
                [
                    'group_key' => 'core_strengths',
                    'title' => 'Core strengths',
                    'intro_text' => 'High-confidence capabilities that should be foregrounded on the homepage.',
                    'items' => [
                        ['label' => 'Architecture leadership', 'detail_text' => 'Replace through admin'],
                        ['label' => 'Delivery governance', 'detail_text' => 'Replace through admin'],
                    ],
                ],
                [
                    'group_key' => 'supporting_tools',
                    'title' => 'Supporting tools and platforms',
                    'intro_text' => 'Platforms and tools used to support outcomes.',
                    'items' => [
                        ['label' => 'Cloud platforms', 'detail_text' => 'Replace through admin'],
                    ],
                ],
                [
                    'group_key' => 'exposure_familiarity',
                    'title' => 'Exposure and familiarity',
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
