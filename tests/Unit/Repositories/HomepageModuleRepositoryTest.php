<?php
declare(strict_types=1);

use App\Repositories\HomepageModuleRepository;
use App\Repositories\ModuleRichTextSectionRepository;

require_once dirname(__DIR__, 3) . '/tests/bootstrap.php';
require_once dirname(__DIR__, 3) . '/tests/TestCase.php';

final class HomepageModuleRepositoryTest extends TestCase
{
    public function run(): void
    {
        $pdo = test_pdo();
        $modules = new HomepageModuleRepository($pdo);
        $richTextSections = new ModuleRichTextSectionRepository($pdo);

        $secondId = $modules->create([
            'module_key' => 'testimonials',
            'module_type' => 'testimonials',
            'eyebrow' => 'Testimonials',
            'title' => 'Selected references',
            'intro_text' => 'Quotes.',
            'anchor_id' => 'testimonials',
            'style_variant' => 'quotes',
            'display_order' => 20,
            'is_active' => 1,
        ]);

        $firstId = $modules->create([
            'module_key' => 'executive_summary',
            'module_type' => 'rich_text',
            'eyebrow' => 'Executive summary',
            'title' => 'Summary',
            'intro_text' => 'Intro.',
            'anchor_id' => 'summary',
            'style_variant' => 'summary',
            'display_order' => 10,
            'is_active' => 1,
        ]);

        $richTextSections->upsertForModule($firstId, [
            'body_text' => 'Summary body',
            'cta_label' => 'Contact',
            'cta_url' => '/signup.php',
        ]);

        $all = $modules->listAll();
        $this->assertCount(2, $all);
        $this->assertSame('executive_summary', $all[0]['module_key'], 'Modules should be ordered by display_order.');
        $this->assertSame('testimonials', $all[1]['module_key']);

        $active = $modules->listActive();
        $this->assertCount(2, $active);

        $modules->update($secondId, [
            'module_key' => 'testimonials',
            'module_type' => 'testimonials',
            'eyebrow' => 'Testimonials',
            'title' => 'References updated',
            'intro_text' => 'Updated.',
            'anchor_id' => 'testimonials',
            'style_variant' => 'quotes',
            'display_order' => 30,
            'is_active' => 0,
        ]);

        $updated = $modules->findById($secondId);
        $this->assertSame('References updated', $updated['title']);
        $this->assertSame(30, (int) $updated['display_order']);

        $activeAfterUpdate = $modules->listActive();
        $this->assertCount(1, $activeAfterUpdate, 'Inactive modules should not appear in active list.');
        $this->assertSame('executive_summary', $activeAfterUpdate[0]['module_key']);

        $richText = $richTextSections->findByModuleId($firstId);
        $this->assertSame('Summary body', $richText['body_text']);
        $this->assertSame('Contact', $richText['cta_label']);

        $modules->delete($firstId);
        $remaining = $modules->listAll();
        $this->assertCount(1, $remaining);
        $this->assertSame('testimonials', $remaining[0]['module_key']);
        $this->assertTrue($richTextSections->findByModuleId($firstId) === null, 'Deleting module should remove rich text payload row.');
    }
}
