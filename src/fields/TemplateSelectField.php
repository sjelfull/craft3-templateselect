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

use craft\helpers\FileHelper;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use yii\db\Schema;
use craft\web\twig\variables\Cp;

/**
 * @author    Superbig
 * @package   TemplateSelect
 * @since     2.0.0
 */
class TemplateSelectField extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $limitToSubfolder = '';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName (): string
    {
        return Craft::t('template-select', 'Template Select');
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules (): array
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
    public function getContentColumnType (): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function normalizeValue ($value, ElementInterface $element = null): mixed
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue ($value, ElementInterface $element = null): mixed
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml (): ?string
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
    public function getInputHtml ($value, ElementInterface $element = null): string
    {
        // Get our id and namespace
        $id           = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Fetch template suggestions and filter out the ones that don't match the subfolder limit (if set)
        $suggestions  = (new Cp())->getTemplateSuggestions();
        $filteredSuggestions = [];
        $limitToSubfolder = $this->limitToSubfolder;

        if ( !empty($limitToSubfolder) ) {
            foreach ($suggestions[0]["data"] as $suggestion) {
                if (strpos($suggestion["name"], $limitToSubfolder) !== false) {
                    $filteredSuggestions[] = $suggestion;
                }
            }
            $suggestions[0]["data"] = $filteredSuggestions;
        }

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'template-select/_components/fields/_input',
            [
                'name'         => $this->handle,
                'value'        => $value,
                'field'        => $this,
                'id'           => $id,
                'namespacedId' => $namespacedId,
                'suggestions'  => $suggestions,
            ]
        );
    }
}
