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
    public function rules ()
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
    public function normalizeValue ($value, ElementInterface $element = null)
    {
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function serializeValue ($value, ElementInterface $element = null)
    {
        return parent::serializeValue($value, $element);
    }

    /**
     * @inheritdoc
     */
    public function getSettingsHtml ()
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
        // Get site templates path
        $templatesPath = $siteTemplatesPath = Craft::$app->path->getSiteTemplatesPath();

        $limitToSubfolder = $this->limitToSubfolder;

        if ( !empty($limitToSubfolder) ) {
            $templatesPath = $templatesPath . DIRECTORY_SEPARATOR . ltrim(rtrim($limitToSubfolder, DIRECTORY_SEPARATOR), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        }
        
        // Normalize the path so it also works as intended in Windows
        $templatesPath = FileHelper::normalizePath($templatesPath);
        
        // Check if folder exists, or give error
        if ( !file_exists($templatesPath) ) {
            throw new \InvalidArgumentException('(Template Select) Folder doesn\'t exist: ' . $templatesPath);
        }

        // Get folder contents
        $templates = FileHelper::findFiles($templatesPath, [
            'only'          => [
                '*.twig',
                '*.html',
            ],
            'caseSensitive' => false,
        ]);

        // Add placeholder for when there is no template selected
        $filteredTemplates = [ '' => Craft::t('template-select', 'No template selected') ];

        // Iterate over template list
        foreach ($templates as $path) {
            $path            = FileHelper::normalizePath($path);
            $pathWithoutBase = str_replace($templatesPath, '', $path);

            $filenameIncludingSubfolder = ltrim($pathWithoutBase, DIRECTORY_SEPARATOR);

            $filteredTemplates[ $filenameIncludingSubfolder ] = $filenameIncludingSubfolder;
        }
		
		// Sort filtered templates alphabetically, maintaining index -> value association
		asort($filteredTemplates);

        // Get our id and namespace
        $id           = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'template-select/_components/fields/_input',
            [
                'name'         => $this->handle,
                'value'        => $value,
                'field'        => $this,
                'id'           => $id,
                'namespacedId' => $namespacedId,
                'templates'    => $filteredTemplates,
            ]
        );
    }
}
