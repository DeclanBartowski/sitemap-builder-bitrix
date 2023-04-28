<?php

use Bitrix\Main\Context;
use \Sp\Sitemap\ModuleInterface;

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!$GLOBALS['USER']->isAdmin()) {
    return;
}
$request = Context::getCurrent()->getRequest();
$values = $request->getPostList()->toArray();
ModuleInterface::save($values);
