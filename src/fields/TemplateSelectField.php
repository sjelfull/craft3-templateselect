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
use craft\behaviors\EnvAttributeParserBehavior;
use craft\helpers\App;
use craft\helpers\ArrayHelper;
use craft\helpers\FileHelper;
use superbig\templateselect\helpers\TemplateHelper;
use superbig\templateselect\models\Template;
use yii\base\Exception;
use yii\db\Schema;
use craft\web\twig\variables\Cp;

/**
 * @author    Superbig
 * @package   TemplateSelect
 * @since     2.0.0
 */
class TemplateSelectField extends Field
{
    public string $limitToSubfolder = '';
    private bool|string $_friendlyOptionValues = false;

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
            $value = $this->getMappedValue($value->template);
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
                'settings' => $this->getSettings(),
            ]
        );
    }

    public function getSuggestions(bool $friendlyNames = true)
    {
        $subfolder = $this->getSubfolder();
        $suggestions  = (new Cp())->getTemplateSuggestions();
        $filteredSuggestions = [];

        // Fetch template suggestions and filter out the ones that don't match the subfolder limit (if set)
        foreach ($suggestions[0]["data"] as $suggestion) {
            if (str_contains($suggestion["name"], $subfolder)) {
                if ($friendlyNames) {
                    $suggestion['name'] = TemplateHelper::friendlyTemplateName($suggestion['name']);
                }
                $filteredSuggestions[] = $suggestion;
            }
        }

        $suggestions[0]["data"] = $filteredSuggestions;

        return $suggestions;
    }

    public function getMappedValue(string|null $value): string|null
    {
        return ArrayHelper::getValue($this->getSuggestionsValueMap(), $value);
    }

    public function getSuggestionsValueMap()
    {
        $subfolder = $this->getSubfolder();
        $suggestions  = (new Cp())->getTemplateSuggestions();
        $map = [];

        foreach ($suggestions[0]["data"] as $suggestion) {
            if (str_contains($suggestion["name"], $subfolder)) {
                // Here we place both versions so we can easily map between the values even if
                $map[TemplateHelper::friendlyTemplateName($suggestion['name'])] = $suggestion['name'];
                $map[$suggestion['name']] = TemplateHelper::friendlyTemplateName($suggestion['name']);

                if (!$this->getFriendlyOptionValues()) {
                    $map[$suggestion['name']] = $suggestion['name'];
                }
            }
        }

        return $map;
    }

    /**
     * @throws Exception
     */
    public function getTemplatesPath(): string
    {
        $templatesPath = Craft::$app->getPath()->getSiteTemplatesPath();
        $subfolder = $this->getSubfolder();

        if (!empty($subfolder)) {
            $templatesPath = $templatesPath . DIRECTORY_SEPARATOR . $subfolder . DIRECTORY_SEPARATOR;
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

        return $templatesPath;
    }

    public function getSubfolder()
    {
        $subfolder = App::parseEnv($this->limitToSubfolder);

        return ltrim(rtrim($subfolder, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR);
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Get site templates path
        $templatesPath = $this->getTemplatesPath();
        $friendlyOptionValues = $this->getFriendlyOptionValues();
        $suggestions = $this->getSuggestions($friendlyOptionValues);

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
                'suggestions'  => $suggestions,
            ]
        );
    }

    public function getFriendlyOptionValues(bool $parse = true)
    {
        return $parse ? App::parseBooleanEnv($this->_friendlyOptionValues) : $this->_friendlyOptionValues;
    }

    public function setFriendlyOptionValues(bool|string $value): void
    {
        $this->_friendlyOptionValues = $value;
    }

    /**
     * @inheritdoc
     */
    public function getSettings(): array
    {
        $settings = parent::getSettings();
        $settings['friendlyOptionValues'] = $this->getFriendlyOptionValues(false);

        return $settings;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool {
        return parent::beforeSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew): bool {
        return parent::beforeElementSave($element, $isNew);
    }
}
