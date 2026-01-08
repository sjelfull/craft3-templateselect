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

    /**
     * Extract description from template file
     * Looks for {# @description: Your description here #} at the start of the file
     *
     * @param string $templatePath Full path to the template file
     * @return string|null The description or null if not found
     */
    public static function extractTemplateDescription(string $templatePath): ?string
    {
        if (!file_exists($templatePath) || !is_readable($templatePath)) {
            return null;
        }

        // Read first few KB to find description (no need to read entire file)
        $handle = fopen($templatePath, 'r');
        if ($handle === false) {
            return null;
        }

        $content = fread($handle, 8192); // Read first 8KB
        fclose($handle);

        if ($content === false) {
            return null;
        }

        // Look for {# @description: ... #} pattern
        // Support both single-line and multi-line descriptions
        $pattern = '/{#\s*@description:\s*([^#]*?)#}/is';
        if (preg_match($pattern, $content, $matches)) {
            $description = trim($matches[1]);
            // Clean up whitespace and newlines
            $description = preg_replace('/\s+/', ' ', $description);
            // HTML-escape for security
            $description = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
            return $description;
        }

        return null;
    }
}
