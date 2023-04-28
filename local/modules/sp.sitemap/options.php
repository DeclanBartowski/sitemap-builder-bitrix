<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global string $mid идентификатор модуля
 */
CJSCore::Init(array("jquery"));
$APPLICATION->SetAdditionalCSS("/local/modules/sp.sitemap/css/admin_sitemap.css");
$APPLICATION->AddHeadScript("/local/modules/sp.sitemap/js/dalee_sitemap.js");
Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
global $USER, $APPLICATION;
IncludeModuleLangFile(__FILE__);

if (!$USER->IsAdmin()) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
$module = new \Sp\Sitemap\ModuleInterface();
$tabs = $module::getTabs();
$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->SetSelectedTab();
?>
<form method="post" action="<? $APPLICATION->GetCurPage() ?>?lang=<?= LANG ?>&mid=<?= $moduleId ?>">
    <?
    echo bitrix_sessid_post();
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    echo $module->getTabHtml('settings');
    $tabControl->EndTab();
    $tabControl->BeginNextTab();
    echo $module->getTabHtml('files');
    $tabControl->EndTab();
    $tabControl->BeginNextTab();
    echo $module->getTabHtml('ibs');
    $tabControl->EndTab();
    $tabControl->End();
    ?>
</form>
