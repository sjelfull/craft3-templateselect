<?php

namespace superbig\templateselect\models;

use craft\base\Model;
use craft\helpers\App;
use superbig\templateselect\fields\TemplateSelectField;

class Template extends Model
{
    public TemplateSelectField $field;
    public string $template = '';

    public static function create(array $data): Template
    {
        return new self($data);
    }

    public function template(bool $includeSubfolder = false)
    {
        if ($includeSubfolder && !empty($this->subfolder())) {
            return implode('/', [$this->subfolder(), $this->template]);
        }
    }
    public function withSubfolder()
    {
        return $this->template(true);
    }

    public function filename()
    {
        return collect(explode('/', $this->template))->last();
    }

    public function __toString()
    {
        return $this->template;
    }

    public function subfolder(): string|null
    {
        return !empty($this->field->limitToSubfolder) ? App::parseEnv($this->field->limitToSubfolder) : null;
    }

    public function getMappedValue(): string|null
    {
        return $this->field->getMappedValue($this->template);
    }
}
