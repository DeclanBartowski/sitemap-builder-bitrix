<?php
if(empty($_SERVER['DOCUMENT_ROOT'])) {
    $_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/../../../s1';
}
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_AGENT_STATISTIC', true);
define('STOP_STATISTICS', true);
define('BX_CRONTAB_SUPPORT', true);
define('LANGUAGE_ID', 'ru');

error_reporting(E_ERROR | E_WARNING | E_PARSE);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
@set_time_limit(0);
@ignore_user_abort(true);
use \Sp\Sitemap\SiteMapGenerator;

if ($argv) {
    unset($argv[0]);
    parse_str(join('&', $argv), $_REQUEST);
}
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

(new SiteMapGenerator($_REQUEST['SITE_ID']))->run();
