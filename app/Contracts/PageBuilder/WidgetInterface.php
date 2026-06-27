<?php

namespace App\Contracts\PageBuilder;

interface WidgetInterface
{
    public function getType(): string;
    public function getLabel(): string;
    public function getIcon(): string;
    public function getCategories(): array;
    public function getDefaultSettings(): array;
    public function render(array $settings, array $content = [], array $styles = []): string;
    public function renderEditor(array $settings, array $content = [], array $styles = []): string;
    public function validateSettings(array $settings): array;
    public function getControls(): array;
    public function isContainer(): bool;
    public function isDynamic(): bool;
    public function getKeywords(): array;
}
