<?php

/**
 * Class xpanLog
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xpanLog extends ilLog {

    const LOG_TITLE = 'panopto.log';

    /**
     * @var xpanLog
     */
    protected static $instance;

    /**
     * @return xpanLog
     * @throws ilLogException
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new self(ILIAS_LOG_DIR, self::LOG_TITLE);
        }

        return self::$instance;
    }
}