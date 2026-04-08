<?php
declare(strict_types=1);

abstract class TestCase
{
    abstract public function run(): void;

    protected function assertSame(mixed $expected, mixed $actual, string $message = ''): void
    {
        if ($expected !== $actual) {
            throw new RuntimeException($message !== '' ? $message : 'Failed asserting values are identical.');
        }
    }

    protected function assertTrue(bool $condition, string $message = ''): void
    {
        if (!$condition) {
            throw new RuntimeException($message !== '' ? $message : 'Failed asserting condition is true.');
        }
    }

    protected function assertCount(int $expectedCount, array|Countable $items, string $message = ''): void
    {
        $actualCount = count($items);
        if ($actualCount !== $expectedCount) {
            throw new RuntimeException($message !== '' ? $message : "Failed asserting count {$expectedCount}, got {$actualCount}.");
        }
    }

    protected function assertNotNull(mixed $value, string $message = ''): void
    {
        if ($value === null) {
            throw new RuntimeException($message !== '' ? $message : 'Failed asserting value is not null.');
        }
    }
}
