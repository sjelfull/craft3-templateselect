<?php

namespace superbig\templateselect\helpers;

use Stringy\Stringy;

class TemplateHelper
{
    public const PATH_SEPARATOR = ' › ';

    public static function friendlyTemplateName(string $name): string
    {
        $stringy = Stringy::create($name);

        return $stringy
            ->replace('.twig', '', caseSensitive: false)
            ->replace('.html', '', caseSensitive: false)
            ->replace('_', '', caseSensitive: false)
            ->replace(DIRECTORY_SEPARATOR, " - ")
            ->replace(' - ', static::PATH_SEPARATOR)
            ->replace(' - ', " &#8250; ")
            ->titleize();
    }
}
