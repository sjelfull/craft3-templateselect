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
use craft\web\twig\variables\Cp;
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
        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Fetch template suggestions and filter out the ones that don't match the subfolder limit (if set)
        $suggestions = (new Cp())->getTemplateSuggestions();
        $filteredSuggestions = [];
        $limitToSubfolder = App::parseEnv($this->limitToSubfolder);

        if (!empty($limitToSubfolder)) {
            // Normalize the subfolder path for comparison
            $limitToSubfolder = trim($limitToSubfolder, '/\\');
            
            foreach ($suggestions[0]['data'] as $suggestion) {
                // Check if the template path starts with the limited subfolder
                if (str_starts_with($suggestion['name'], $limitToSubfolder . '/') || 
                    str_starts_with($suggestion['name'], $limitToSubfolder . DIRECTORY_SEPARATOR)) {
                    $filteredSuggestions[] = $suggestion;
                }
            }
            $suggestions[0]['data'] = $filteredSuggestions;
        }

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'template-select/_components/fields/_input',
            [
                'name' => $this->handle,
                'value' => $value,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
                'suggestions' => $suggestions,
            ]
        );
    }
}
