<?php

use superbig\templateselect\fields\TemplateSelectField;
use superbig\templateselect\models\Template;

it('creates a Template via factory method', function () {
    $field = new TemplateSelectField();
    $template = Template::create([
        'template' => 'layouts/default.twig',
        'field' => $field,
    ]);

    expect($template)->toBeInstanceOf(Template::class);
    expect($template->template)->toBe('layouts/default.twig');
});

it('casts to string via __toString', function () {
    $field = new TemplateSelectField();
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    expect((string) $template)->toBe('homepage.twig');
});

it('returns empty string for empty template via __toString', function () {
    $field = new TemplateSelectField();
    $template = Template::create([
        'template' => '',
        'field' => $field,
    ]);

    expect((string) $template)->toBe('');
});

it('extracts filename from simple path', function () {
    $field = new TemplateSelectField();
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    expect($template->filename())->toBe('homepage.twig');
});

it('extracts filename from nested path', function () {
    $field = new TemplateSelectField();
    $template = Template::create([
        'template' => 'layouts/partials/header.twig',
        'field' => $field,
    ]);

    expect($template->filename())->toBe('header.twig');
});

it('returns null subfolder when field has no limitToSubfolder', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    expect($template->subfolder())->toBeNull();
});

it('returns parsed subfolder when field has limitToSubfolder', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '_pages';
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    expect($template->subfolder())->toBe('_pages');
});

it('combines subfolder and template via withSubfolder()', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '_pages';
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    expect($template->withSubfolder())->toBe('_pages/homepage.twig');
});

it('returns null from template(true) when no subfolder set', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    // template(true) with no subfolder returns null (no return statement hit)
    expect($template->template(true))->toBeNull();
});

it('returns null from template(false)', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '_pages';
    $template = Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    // template(false) — the if condition is false, so no return statement
    expect($template->template(false))->toBeNull();
});
