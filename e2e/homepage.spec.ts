import { expect, test } from '@playwright/test';

test('homepage renders modular homepage content', async ({ page }) => {
  await page.goto('/');

  await expect(page.getByTestId('homepage-hero')).toBeVisible();
  await expect(page.getByTestId('homepage-footer')).toBeVisible();

  const modules = page.locator('[data-module-key]');
  await expect(modules).toHaveCount(8);

  await expect(modules.nth(0)).toHaveAttribute('data-module-type', 'rich_text');
  await expect(modules.nth(1)).toHaveAttribute('data-module-type', 'timeline');
  await expect(modules.nth(2)).toHaveAttribute('data-module-type', 'pill_cards');
  await expect(modules.nth(3)).toHaveAttribute('data-module-type', 'case_studies');
  await expect(modules.nth(4)).toHaveAttribute('data-module-type', 'list');
  await expect(modules.nth(5)).toHaveAttribute('data-module-type', 'quote_cards');
  await expect(modules.nth(6)).toHaveAttribute('data-module-type', 'cta_banner');
  await expect(modules.nth(7)).toHaveAttribute('data-module-type', 'media_text');

  await expect(page.locator('[data-module-key="hidden-example"]')).toHaveCount(0);
});

test('timeline details and seeded footer links work', async ({ page }) => {
  await page.goto('/');

  const firstTimelineItem = page.getByTestId('timeline-item').first();
  await expect(firstTimelineItem).toHaveAttribute('open', '');
  await firstTimelineItem.locator('summary').click();
  await expect(firstTimelineItem).not.toHaveAttribute('open', '');
  await firstTimelineItem.locator('summary').click();
  await expect(firstTimelineItem).toHaveAttribute('open', '');

  await expect(page.locator('[data-module-type="cta_banner"] a')).toHaveCount(1);
  await expect(page.getByRole('link', { name: 'Download CV' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'LinkedIn' })).toBeVisible();
  await expect(page.getByRole('link', { name: 'GitHub' })).toBeVisible();
});
