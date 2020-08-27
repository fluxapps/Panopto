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
            if (ILIAS_LOG_DIR === "php:/" && ILIAS_LOG_FILE === "stdout") {
                // Fix Docker-ILIAS log
                self::$instance = new self(ILIAS_LOG_DIR, ILIAS_LOG_FILE);
            } else {
            self::$instance = new self(ILIAS_LOG_DIR, self::LOG_TITLE);
            }
        }

        return self::$instance;
    }
}