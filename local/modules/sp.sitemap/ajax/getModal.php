<?php

use \Sp\Sitemap\ModuleInterface;
use Bitrix\Main\Context;

define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
if (!$GLOBALS['USER']->isAdmin()) {
    return;
}
$request = Context::getCurrent()->getRequest();
$type = $request->getQuery('type');
$static = ($type == 'static') ? ModuleInterface::getStatic() : ModuleInterface::getIBlock();
?>
<form name="" id="form_modal_sitemap" action="/local/modules/sp.sitemap/ajax/save.php">
    <input type="hidden" name="save_type" value="iblock">
    <? foreach ($static['sites'] as $keySite => $valueSite): ?>
        <table>
            <tbody>
            <tr>
                <td colspan="2"><h3 class="admin-h3">Для сайта <?= $valueSite['NAME'] ?> [<?= $keySite ?>]</h3></div>
                </td>
            </tr>
            <? if ($type == 'static'): ?>
                <? if (isset($static['elements'][$keySite])): ?>
                    <? foreach ($static['elements'][$keySite] as $value): ?>
                        <tr>
                            <td style="width:550px;">
                                <div
                                    class="row-property"><?= $value['CODE'] ?>:<?= $value['NAME'] ?>
                                </div>
                                <? foreach ($value as $keyProp => $valueProp): ?>
                                    <input type="hidden" name="<?= $keyProp ?>[]" value="<?= $valueProp ?>">
                                <? endforeach; ?>
                            </td>
                            <td style="width:50px;">
                                <div onclick="popupModal.delete(this)"
                                     class="row-property"
                                     style="padding: 0;text-align: center;background-color: #597DA3;color: #fff;z-index:1"><span
                                        class="span_x"
                                        style="">х</span></div>
                            </td>
                        </tr>
                    <? endforeach; ?>
                <? endif; ?>
            <? else: ?>
                <? if (isset($static['elements'][$keySite])): ?>
                    <? foreach ($static['elements'][$keySite] as $value): ?>
                        <?
                        if (!isset($static["exists"]['data'][$keySite]['ELEMENT_' . $value['CODE']])):
                            ?>
                            <tr>
                                <input type="hidden" name="TYPE[]"
                                       value="<?= ModuleInterface::$elementType ?>">
                                <input type="hidden" name="NAME[]" value="<?= $value['NAME'] ?>">
                                <input type="hidden" name="CODE[]" value="<?= $value['CODE'] ?>">
                                <input type="hidden" name="SITE_ID[]" value="<?= $keySite ?>">
                                <td style="width:550px;">
                                    <div
                                        class="row-property"><?= $value['NAME'] ?> Элементы
                                    </div>
                                </td>
                                <td style="width:50px;">
                                    <div onclick="popupModal.delete(this)"
                                         class="row-property"
                                         style="padding: 0;text-align: center;background-color: #597DA3;color: #fff;z-index:1"><span
                                            class="span_x"
                                            style="">х</span></div>
                                </td>
                            </tr>
                        <? endif; ?>
                        <?
                        if (!isset($static["exists"]['data'][$keySite]['SECTION_' . $value['CODE']])):
                            ?>
                            <tr>
                                <input type="hidden" name="TYPE[]"
                                       value="<?= ModuleInterface::$sectionType ?>">
                                <input type="hidden" name="NAME[]" value="<?= $value['NAME'] ?>">
                                <input type="hidden" name="CODE[]" value="<?= $value['CODE'] ?>">
                                <input type="hidden" name="SITE_ID[]" value="<?= $keySite ?>">
                                <td style="width:550px;">
                                    <div
                                        class="row-property"><?= $value['NAME'] ?> Разделы
                                    </div>
                                </td>
                                <td style="width:50px;">
                                    <div onclick="popupModal.delete(this)"
                                         class="row-property"
                                         style="padding: 0;text-align: center;background-color: #597DA3;color: #fff;z-index:1"><span
                                            class="span_x"
                                            style="">х</span></div>
                                </td>
                            </tr>
                        <? endif; ?>
                    <? endforeach; ?>
                <? endif; ?>
            <? endif; ?>
            </tbody>
        </table>
    <? endforeach; ?>
</form>

