<?php
/**
 * Template Select plugin for Craft CMS 3.x
 *
 * A fieldtype that allows you to select a template from a dropdown.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\templateselect\fields;

use Craft;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\App;
use craft\helpers\FileHelper;
use superbig\templateselect\helpers\TemplateHelper;
use superbig\templateselect\models\Template;
use yii\db\Schema;

/**
 * @author    Superbig
 * @package   TemplateSelect
 * @since     2.0.0
 */
class TemplateSelectField extends Field
{
    public string $limitToSubfolder = '';
    public bool $friendlyOptionValues = true;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('template-select', 'Template Select');
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules = array_merge($rules, [
            [ 'limitToSubfolder', 'string' ],
            [ 'limitToSubfolder', 'default', 'value' => '' ],
            [ 'friendlyOptionValues', 'boolean' ],
            [ 'friendlyOptionValues', 'default', 'value' => true ],
        ]);

        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue($value, ElementInterface $element = null): mixed
    {
        return Template::create([
            'template' => $value,
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function serializeValue($value, ElementInterface $element = null): mixed
    {
        if ($value instanceof Template) {
            $value = $value->template;
        }

        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml(): ?string
    {
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'template-select/_components/fields/_settings',
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Get site templates path
        $templatesPath = Craft::$app->path->getSiteTemplatesPath();
        $limitToSubfolder = App::parseEnv($this->limitToSubfolder);
        $friendlyOptionValues = App::parseBooleanEnv($this->friendlyOptionValues);

        if (!empty($limitToSubfolder)) {
            $templatesPath = $templatesPath . DIRECTORY_SEPARATOR . ltrim(rtrim($limitToSubfolder, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        
        // Normalize the path so it also works as intended in Windows
        $templatesPath = FileHelper::normalizePath($templatesPath);
        
        // Check if folder exists, or give error
        if (!file_exists($templatesPath)) {
            throw new \InvalidArgumentException(
                Craft::t('template-select', "Template Select Folder doesn't exist: {folder}", [
                    'folder' => $templatesPath,
                ])
            );
        }

        // Get folder contents
        $templates = FileHelper::findFiles($templatesPath, [
            'only' => [
                '*.twig',
                '*.html',
            ],
            'caseSensitive' => false,
        ]);

        // Add placeholder for when there is no template selected
        $filteredTemplates = [];

        // Iterate over template list
        foreach ($templates as $path) {
            $path = FileHelper::normalizePath($path);
            $pathWithoutBase = str_replace($templatesPath, '', $path);
            $filenameIncludingSubfolder = ltrim($pathWithoutBase, DIRECTORY_SEPARATOR);
            $optionValue = $filenameIncludingSubfolder;

            if ($friendlyOptionValues) {
                $optionValue = TemplateHelper::friendlyTemplateName($optionValue);
            }

            $filteredTemplates[ $filenameIncludingSubfolder ] = $optionValue;
        }
        
        // Sort filtered templates alphabetically, maintaining index -> value association
        asort($filteredTemplates);

        $placeholder[] = Craft::t('template-select', 'No template selected');
        $filteredTemplates = array_merge($placeholder, $filteredTemplates);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'template-select/_components/fields/_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'templates' => $filteredTemplates,
            ]
        );
    }
}
