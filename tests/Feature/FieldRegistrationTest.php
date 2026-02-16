<?php

use craft\services\Fields;
use superbig\templateselect\fields\TemplateSelectField;

it('registers TemplateSelectField as a field type', function () {
    $fieldTypes = \Craft::$app->getFields()->getAllFieldTypes();

    expect($fieldTypes)->toContain(TemplateSelectField::class);
});

it('has the correct display name', function () {
    expect(TemplateSelectField::displayName())->toBe('Template Select');
});

it('returns string content column type', function () {
    $field = new TemplateSelectField();

    expect($field->getContentColumnType())->toBe(\yii\db\Schema::TYPE_STRING);
});

it('normalizes value to Template model', function () {
    $field = new TemplateSelectField();
    $result = $field->normalizeValue('homepage.twig');

    expect($result)->toBeInstanceOf(\superbig\templateselect\models\Template::class);
    expect((string) $result)->toBe('homepage.twig');
});

it('serializes Template model back to string', function () {
    $field = new TemplateSelectField();
    $template = \superbig\templateselect\models\Template::create([
        'template' => 'homepage.twig',
        'field' => $field,
    ]);

    $serialized = $field->serializeValue($template);

    expect($serialized)->toBe('homepage.twig');
});

it('has default field settings', function () {
    $field = new TemplateSelectField();

    expect($field->limitToSubfolder)->toBe('');
    expect($field->friendlyOptionValues)->toBeTrue();
});
