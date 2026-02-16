<?php

use superbig\templateselect\TemplateSelect;

it('boots Craft and installs the template-select plugin', function () {
    expect(TemplateSelect::$plugin)->toBeInstanceOf(TemplateSelect::class);
})->skip('Plugin bootstrap needs configuration');
