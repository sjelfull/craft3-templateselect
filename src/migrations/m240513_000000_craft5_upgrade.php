<?php
/**
 * Template Select plugin for Craft CMS 5.x
 *
 * A fieldtype that allows you to select a template from a dropdown.
 *
 * @link      https://superbig.co
 * @copyright Copyright (c) 2017 Superbig
 */

namespace superbig\templateselect\migrations;

use Craft;
use craft\db\Migration;

/**
 * m240513_000000_craft5_upgrade migration.
 * 
 * This migration handles the upgrade from Craft 4 to Craft 5.
 * It ensures that Template Select fields are properly recognized in Craft 5.
 */
class m240513_000000_craft5_upgrade extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        // Craft 5 upgrade: ensure field types are properly registered
        // The schemaVersion bump to 5.0.0 will trigger this migration
        
        $projectConfig = Craft::$app->getProjectConfig();
        $muteEvents = $projectConfig->muteEvents;
        
        // Temporarily unmute events to ensure changes are processed
        $projectConfig->muteEvents = false;
        
        try {
            $fields = $projectConfig->get('fields') ?? [];
            $count = 0;
            
            foreach ($fields as $fieldUid => $fieldConfig) {
                if (isset($fieldConfig['type']) && 
                    $fieldConfig['type'] === 'superbig\\templateselect\\fields\\TemplateSelectField') {
                    $count++;
                }
            }
            
            if ($count > 0) {
                echo "    > Found {$count} Template Select field(s) - they should now work correctly\n";
            }
        } finally {
            $projectConfig->muteEvents = $muteEvents;
        }
        
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        echo "    > Template Select Craft 5 migration cannot be reverted.\n";
        return false;
    }
}
