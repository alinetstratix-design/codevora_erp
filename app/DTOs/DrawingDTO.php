<?php

namespace App\DTOs;

class DrawingDTO
{
    public string $svgHtml;
    public string $viewCaption;
    public array $metadata;

    public function __construct(string $svgHtml = '', string $viewCaption = 'View From Inside', array $metadata = [])
    {
        $this->svgHtml = $svgHtml;
        $this->viewCaption = $viewCaption;
        $this->metadata = $metadata;
    }

    public function toArray(): array
    {
        return [
            'svg_html' => $this->svgHtml,
            'view_caption' => $this->viewCaption,
            'metadata' => $this->metadata,
        ];
    }
}
