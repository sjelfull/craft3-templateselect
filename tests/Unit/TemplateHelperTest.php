<?php

use superbig\templateselect\helpers\TemplateHelper;

// --- friendlyTemplateName() ---

it('strips .twig extension', function () {
    expect(TemplateHelper::friendlyTemplateName('homepage.twig'))->toBe('Homepage');
});

it('strips .html extension', function () {
    expect(TemplateHelper::friendlyTemplateName('homepage.html'))->toBe('Homepage');
});

it('strips .TWIG extension (case-insensitive)', function () {
    expect(TemplateHelper::friendlyTemplateName('homepage.TWIG'))->toBe('Homepage');
});

it('replaces underscores with spaces', function () {
    expect(TemplateHelper::friendlyTemplateName('my_template.twig'))->toBe('My Template');
});

it('replaces directory separators with ›', function () {
    $name = 'layouts' . DIRECTORY_SEPARATOR . 'default.twig';
    expect(TemplateHelper::friendlyTemplateName($name))->toBe('Layouts › Default');
});

it('collapses multiple spaces', function () {
    expect(TemplateHelper::friendlyTemplateName('my__double__spaced.twig'))->toBe('My Double Spaced');
});

it('title-cases the result', function () {
    expect(TemplateHelper::friendlyTemplateName('about us.twig'))->toBe('About Us');
});

it('handles deeply nested paths', function () {
    $name = 'layouts' . DIRECTORY_SEPARATOR . 'partials' . DIRECTORY_SEPARATOR . 'header.twig';
    expect(TemplateHelper::friendlyTemplateName($name))->toBe('Layouts › Partials › Header');
});

it('handles name with no extension', function () {
    expect(TemplateHelper::friendlyTemplateName('homepage'))->toBe('Homepage');
});

it('does not strip non-template extensions', function () {
    // .php should not be stripped — only .twig and .html
    expect(TemplateHelper::friendlyTemplateName('config.php'))->toBe('Config.Php');
});

it('trims leading and trailing whitespace', function () {
    expect(TemplateHelper::friendlyTemplateName('  padded.twig  '))->toBe('Padded');
});

// --- extractTemplateDescription() ---

it('extracts single-line description from template', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, '{# @description: A simple homepage template #}' . "\n<html></html>");

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBe('A simple homepage template');

    unlink($tmpFile);
});

it('extracts multi-line description from template', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, "{# @description:\n  This is a multi-line\n  description for the template\n#}\n<html></html>");

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBe('This is a multi-line description for the template');

    unlink($tmpFile);
});

it('returns null for missing file', function () {
    expect(TemplateHelper::extractTemplateDescription('/nonexistent/path/template.twig'))->toBeNull();
});

it('returns null when no description comment exists', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, '<html><body>No description here</body></html>');

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBeNull();

    unlink($tmpFile);
});

it('returns null for empty file', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, '');

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBeNull();

    unlink($tmpFile);
});

it('HTML-escapes description for security', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, '{# @description: Template with <script>alert("xss")</script> #}');

    $result = TemplateHelper::extractTemplateDescription($tmpFile);
    expect($result)->not->toContain('<script>');
    expect($result)->toContain('&lt;script&gt;');

    unlink($tmpFile);
});

it('handles description with extra whitespace', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, '{#   @description:    Lots   of   spaces   #}');

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBe('Lots of spaces');

    unlink($tmpFile);
});

it('ignores regular Twig comments without @description', function () {
    $tmpFile = tempnam(sys_get_temp_dir(), 'tpl_');
    file_put_contents($tmpFile, "{# This is just a regular comment #}\n<html></html>");

    expect(TemplateHelper::extractTemplateDescription($tmpFile))->toBeNull();

    unlink($tmpFile);
});
