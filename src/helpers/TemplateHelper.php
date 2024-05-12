<?php

namespace superbig\templateselect\helpers;

use Stringy\Stringy;

class TemplateHelper
{
    public static ?Stringy $_stringyInstance = null;

    public static function friendlyTemplateName(string $name): string
    {
        $stringy = Stringy::create($name);

        return $stringy
            ->replace('.twig', '', caseSensitive: false)
            ->replace('.html', '', caseSensitive: false)
            ->replace('_', '', caseSensitive: false)
            ->replace(DIRECTORY_SEPARATOR, " - ")
            ->replace(' - ', " › ")
            ->replace(' - ', " &#8250; ")
            ->titleize();
    }
}
