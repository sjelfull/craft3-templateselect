<?php
/**
 * Template Select plugin for Craft CMS 3.x
 *
 * A fieldtype that allows you to select a template from a dropdown.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\templateselect;

use Craft;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Fields;

use superbig\templateselect\fields\TemplateSelectField;
use yii\base\Event;

/**
 * Class TemplateSelect
 *
 * @author    Superbig
 * @package   TemplateSelect
 * @since     2.0.0
 *
 */
class TemplateSelect extends Plugin
{
    public static TemplateSelect $plugin;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function(RegisterComponentTypesEvent $event) {
                $event->types[] = TemplateSelectField::class;
            }
        );

        Craft::info(
            Craft::t(
                'template-select',
                '{name} plugin loaded',
                [ 'name' => $this->name ]
            ),
            __METHOD__
        );
    }
}
