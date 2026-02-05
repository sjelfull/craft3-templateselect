<?php

namespace superbig\templateselect\helpers;

use craft\helpers\StringHelper;

class TemplateHelper
{
    /**
     * Maximum bytes to read from template files when extracting descriptions
     */
    private const DESCRIPTION_READ_BUFFER_SIZE = 8192;

    public static function friendlyTemplateName(string $name): string
    {
        $name = preg_replace('/\.(twig|html)$/i', '', $name);

        $name = str_replace('_', ' ', $name);

        $name = str_replace(DIRECTORY_SEPARATOR, ' › ', $name);

        $name = preg_replace('/\s+/', ' ', $name);

        return StringHelper::toTitleCase(trim($name));
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
        $content = file_get_contents($templatePath, false, null, 0, self::DESCRIPTION_READ_BUFFER_SIZE);

        if ($content === false) {
            return null;
        }

        // Look for {# @description: ... #} pattern
        // Support both single-line and multi-line descriptions
        // Pattern matches everything between {# @description: and the closing #}
        $pattern = '/{#\s*@description:\s*(.*?)#}/is';
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
