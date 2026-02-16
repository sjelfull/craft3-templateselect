<?php

use superbig\templateselect\fields\TemplateSelectField;
use superbig\templateselect\models\Template;

// --- __toString in Twig ({{ value }}) ---

it('renders the template path as a string in Twig', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => 'pages/home.twig',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/output', ['value' => $template])
        ->assertSee('pages/home.twig');
});

it('renders empty string for empty template value', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => '',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/output', ['value' => $template])
        ->assertOk();
});

// --- {% include value %} ---

it('can be used with Twig include', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => '_test/included.twig',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/includer', ['value' => $template])
        ->assertSee('INCLUDED_CONTENT');
});

// --- filename() in Twig ---

it('exposes filename() in Twig context', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => 'pages/home.twig',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/filename-output', ['value' => $template])
        ->assertSee('home.twig');
});

it('exposes filename() for deeply nested paths', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => 'layouts/sections/blog/post.twig',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/filename-output', ['value' => $template])
        ->assertSee('post.twig');
});

// --- withSubfolder() in Twig ---

it('exposes withSubfolder() in Twig context', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '_pages';

    $template = Template::create([
        'template' => 'home.twig',
        'field' => $field,
    ]);

    $this->renderTemplate('_test/subfolder-output', ['value' => $template])
        ->assertSee('_pages/home.twig');
});

it('renders empty withSubfolder() when no subfolder set', function () {
    $field = new TemplateSelectField();
    $field->limitToSubfolder = '';

    $template = Template::create([
        'template' => 'home.twig',
        'field' => $field,
    ]);

    // withSubfolder() calls template(true), which returns null when no subfolder
    $this->renderTemplate('_test/subfolder-output', ['value' => $template])
        ->assertOk();
});
