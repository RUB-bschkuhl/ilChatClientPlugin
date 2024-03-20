<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * ChatClient plugin
 * @author Bastian Schmidt-Kuhl <bastian.schmidt-kuhl@ruhr-uni-bochum.de>
 */

class ilChatClientPlugin extends ilPageComponentPlugin
{
    private static $instance;

    const PLUGIN_NAME = 'ChatClient';
    const TABLE_NAME = "excpc_data";
    const CTYPE = 'Services';
    const CNAME = 'COPage';
    const SLOT_ID = 'pgcp';
    const PLUGIN_ID = 'excpc';

    public function __construct()
    {
        global $DIC;
        $this->db = $DIC->database();
        
        parent::__construct($this->db, $DIC["component.repository"], self::PLUGIN_ID);
    }

    /**
     * Get plugin name
     * @return string
     */
    public function getPluginName(): string
    {
        return self::PLUGIN_NAME;
    }

    /**
     * Check if parent type is valid
     */
    public function isValidParentType(string $a_parent_type): bool
    {
        // only available in content page
        if (in_array($a_parent_type, array("copa"))) {
            return true;
        }
        return false;
    }

    public static function getInstance(): ilChatClientPlugin
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Get Javascript files regardless of output mode
     */
    public function getJavascriptFiles(string $a_mode): array
    {
        return ['dist/assets/chat.min.js'];
    }

    public static function setValue($setting, $value, $type)
    {
        global $DIC;
        $db = $DIC->database();

        $db->manipulate(
            "UPDATE " . ilChatClientPlugin::TABLE_NAME . " SET " .
                " value = " . $db->quote($value, $type) .
                " WHERE name = " . $db->quote($setting, "text")
        );
    }

    public static function getValue($setting)
    {
        global $DIC;
        $db = $DIC->database();
        $value = null;
        $set = $db->query(
            "SELECT value FROM " . ilChatClientPlugin::TABLE_NAME .
                " WHERE name = " . $db->quote($setting, "text")
        );

        if ($rec = $set->fetchRow()) {
            $value = $rec['value'];
        }
        return $value;
    }
}
