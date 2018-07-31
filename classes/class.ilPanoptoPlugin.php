<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Class ilPanoptoPlugin
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class ilPanoptoPlugin extends ilRepositoryObjectPlugin {

    const PLUGIN_NAME = 'Panopto';
    const XPAN = 'xpan';

    /**
     * @var ilPanoptoPlugin
     */
    protected static $instance;


    /**
     * @return ilPanoptoPlugin
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @return string
     */
    function getPluginName() {
        return self::PLUGIN_NAME;
    }

    /**
     *
     */
    protected function uninstallCustom() {
        global $DIC;
        $DIC->database()->dropTable(xpanConfig::DB_TABLE_NAME);
        $DIC->database()->dropTable(xpanSettings::DB_TABLE_NAME);
    }


}