<?php
defined('TYPO3_MODE') or die('Access denied.');

(static function () {
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['recall_data'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['recall_data'] = [];
    }
})();
