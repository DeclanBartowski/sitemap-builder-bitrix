<?php

use \Sp\Sitemap\ModuleInterface;

/**
 * файл подключается в \Sp\Sitemap\ModuleInterface::getTabHtml()
 * доступны поля класса \Sp\Sitemap\ModuleInterface
 * @var array $arParams
 * @var array $settings
 * @var string $tabName
 */
?>
<tr>
    <td>
        <table>
            <tbody>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <div class="wrapeer-button">
                        <div class="row-property-button"
                             onclick="popupModal.showModal('Статические разделы', 'static')">Получить статические файлы
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
        <div class="adm-cell">

        </div>
    </td>
</tr>

<tr>
    <th>Папка:</th>
    <th style="cursor: pointer">Частота обновления:</th>
    <th style="cursor: pointer">Дата модификации:</th>
    <th style="cursor: pointer">Приоритет:</th>
    <th>Удалить:</th>
</tr>

<?
$pages = \Sp\Sitemap\ModuleInterface::getPages();
?>
<? foreach ($pages["sites"] as $keySite => $sites): ?>
    <tr>
        <td colspan="5">
            <h3 class="admin-h3"><?= $sites['NAME'] ?> [<?= $keySite ?>]</h3>
        </td>
    </tr>

    <? if (!empty($pages['data'][$keySite])): ?>
        <? foreach ($pages['data'][$keySite] as $value): ?>
            <tr>
                <td style="width:300px;" class="adm-detail-content-cell-l">
                    <div class="row-property"><?= $value['UF_TITLE'] ?>:<?= $value['UF_CODE'] ?></div>
                </td>
                <td class="adm-detail-content-cell-r">
                    <select class="custom-input" style="height: 37px;margin-top: 10px;width:130px"
                            name="static[<?= $value['ID'] ?>][freq]">
                        <? foreach (ModuleInterface::$freq as $freqKey => $freqValue): ?>
                            <option value="<?= $freqKey ?>"
                                    <? if ($freqKey == $value['UF_FREQ']): ?>selected="selected"<? endif; ?>><?= $freqValue ?></option>
                        <? endforeach; ?>
                    </select>
                </td>
                <td>
                    <select title="modification" class="custom-input" style="height: 37px;margin-top: 10px;width:130px"
                            name="static[<?= $value['ID'] ?>][mod]">
                        <? foreach (ModuleInterface::$mod as $modKey => $modValue): ?>
                            <option value="<?= $modKey ?>"
                                    <? if ($modKey == $value['UF_LASTMODE']): ?>selected="selected"<? endif; ?>><?= $modValue ?></option>
                        <? endforeach; ?>
                    </select>
                </td>
                <td>
                    <input title="priority" class="custom-input" type="text" style="margin-top: 10px;width:70px"
                           name="static[<?= $value['ID'] ?>][priority]" value="<?= $value['UF_PRIORITY'] ?>">
                </td>
                <td style="width:50px;">
                    <div onclick="popupModal.deleteStatic(this, '<?= $value['ID'] ?>','#settings_edit_table')"
                         class="row-property"
                         style="padding: 0;text-align: center;background-color: #597DA3;color: #fff;z-index:1">
                        <span style="position:relative;font-size: 30.6px;z-index:2;display:block">х</span>
                    </div>
                </td>
            </tr>
        <? endforeach; ?>
    <? endif; ?>
<? endforeach; ?>

<tr>
    <td colspan="2">
        <div onclick="popupModal.update(this)" class="row-property-button left-margin">Сохранить</div>
    </td>
</tr>
