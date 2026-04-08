<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\ContentBlockRepository;
use App\Repositories\ContentItemRepository;

final class SiteContentService
{
    private const SECTION_ORDER = [
        'hero',
        'summary_cards',
        'about',
        'achievements',
        'technology_tags',
        'operating_approach',
        'contact_cta',
    ];

    public function __construct(
        private ContentBlockRepository $blocks,
        private ContentItemRepository $items
    ) {
    }

    public function homepage(): array
    {
        $blocks = $this->blocks->listActive();
        $blocksByKey = [];
        $blockIds = [];
        foreach ($blocks as $block) {
            $blocksByKey[$block['section_key']] = $this->normalizeBlock($block);
            $blockIds[] = (int) $block['id'];
        }

        $itemsByBlockId = $this->items->listGroupedByBlockIds($blockIds);
        foreach ($blocksByKey as $key => $block) {
            $blocksByKey[$key]['items'] = array_map(
                fn (array $item): array => $this->normalizeItem($item),
                $itemsByBlockId[(int) $block['id']] ?? []
            );
        }

        $defaults = $this->defaultHomepage();
        $homepage = [];
        foreach (self::SECTION_ORDER as $sectionKey) {
            $section = $blocksByKey[$sectionKey] ?? $defaults[$sectionKey];
            if ($section['items'] === [] && $defaults[$sectionKey]['items'] !== []) {
                $section['items'] = $defaults[$sectionKey]['items'];
            }
            $homepage[$sectionKey] = $section;
        }

        return $homepage;
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
            'hero' => [
                'id' => 0,
                'section_key' => 'hero',
                'title' => 'A modular professional platform ready for personalised content.',
                'subtitle' => 'Placeholder homepage content is active until real profile content is entered through admin.',
                'body_text' => 'This project ships with neutral placeholder content so forks stay reusable and personal profile details never need to live in the repository.',
                'meta' => ['eyebrow' => 'Professional Profile'],
                'items' => [
                    ['id' => 0, 'item_key' => 'primary_cta', 'label' => 'Primary', 'title' => 'Request recruiter access', 'body_text' => '', 'link_url' => '/signup.php', 'meta' => ['style' => 'primary']],
                    ['id' => 0, 'item_key' => 'secondary_cta', 'label' => 'Secondary', 'title' => 'Portal login', 'body_text' => '', 'link_url' => '/login.php', 'meta' => ['style' => 'ghost']],
                ],
            ],
            'summary_cards' => [
                'id' => 0,
                'section_key' => 'summary_cards',
                'title' => 'Homepage Highlights',
                'subtitle' => '',
                'body_text' => '',
                'meta' => [],
                'items' => [
                    ['id' => 0, 'item_key' => 'summary_focus', 'label' => 'Focus', 'title' => 'Systems and delivery leadership', 'body_text' => 'Use admin to describe the core areas this profile should emphasise.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'summary_style', 'label' => 'Style', 'title' => 'Practical and senior', 'body_text' => 'Keep the tone credible, operational, and governance-aware.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'summary_portal', 'label' => 'Portal', 'title' => 'Gated recruiter assistant', 'body_text' => 'The recruiter portal remains the secure differentiator behind signup and approval.', 'link_url' => '', 'meta' => []],
                ],
            ],
            'about' => [
                'id' => 0,
                'section_key' => 'about',
                'title' => 'What this profile covers',
                'subtitle' => 'About',
                'body_text' => '',
                'meta' => [],
                'items' => [
                    ['id' => 0, 'item_key' => 'about_one', 'label' => '', 'title' => 'Leadership and operating model', 'body_text' => 'Use this section to describe operating model, governance, and leadership themes.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'about_two', 'label' => '', 'title' => 'Systems and data', 'body_text' => 'Add platform, data, and business systems focus areas through admin.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'about_three', 'label' => '', 'title' => 'Transformation delivery', 'body_text' => 'Describe the types of transformation work and delivery environments this profile should represent.', 'link_url' => '', 'meta' => []],
                ],
            ],
            'achievements' => [
                'id' => 0,
                'section_key' => 'achievements',
                'title' => 'Selected achievements',
                'subtitle' => '',
                'body_text' => '',
                'meta' => ['eyebrow' => 'Highlights'],
                'items' => [
                    ['id' => 0, 'item_key' => 'achievement_one', 'label' => '', 'title' => 'Placeholder achievement', 'body_text' => 'Add short, outcome-oriented achievements through the CMS.', 'link_url' => '', 'meta' => []],
                ],
            ],
            'technology_tags' => [
                'id' => 0,
                'section_key' => 'technology_tags',
                'title' => 'Technology exposure',
                'subtitle' => '',
                'body_text' => '',
                'meta' => ['eyebrow' => 'Platforms'],
                'items' => [
                    ['id' => 0, 'item_key' => 'tag_php', 'label' => 'PHP', 'title' => 'PHP', 'body_text' => '', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'tag_mysql', 'label' => 'MySQL', 'title' => 'MySQL', 'body_text' => '', 'link_url' => '', 'meta' => []],
                ],
            ],
            'operating_approach' => [
                'id' => 0,
                'section_key' => 'operating_approach',
                'title' => 'Operating approach',
                'subtitle' => '',
                'body_text' => '',
                'meta' => ['eyebrow' => 'Approach'],
                'items' => [
                    ['id' => 0, 'item_key' => 'approach_one', 'label' => '', 'title' => 'Credible governance', 'body_text' => 'Describe the practical governance style you want recruiters to understand.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'approach_two', 'label' => '', 'title' => 'Useful architecture', 'body_text' => 'Keep this focused on real delivery and operating outcomes.', 'link_url' => '', 'meta' => []],
                    ['id' => 0, 'item_key' => 'approach_three', 'label' => '', 'title' => 'Phaseable product thinking', 'body_text' => 'Explain how work is phased and governed without overbuilding.', 'link_url' => '', 'meta' => []],
                ],
            ],
            'contact_cta' => [
                'id' => 0,
                'section_key' => 'contact_cta',
                'title' => 'Recruiter portal and contact',
                'subtitle' => 'Contact',
                'body_text' => 'Enter real contact details and calls to action through admin after setup.',
                'meta' => ['anchor' => 'contact'],
                'items' => [
                    ['id' => 0, 'item_key' => 'contact_primary', 'label' => 'Primary', 'title' => 'Request portal access', 'body_text' => '', 'link_url' => '/signup.php', 'meta' => ['style' => 'primary']],
                    ['id' => 0, 'item_key' => 'contact_secondary', 'label' => 'Secondary', 'title' => 'Use a magic link', 'body_text' => '', 'link_url' => '/login.php', 'meta' => ['style' => 'ghost']],
                ],
            ],
        ];
    }
}

