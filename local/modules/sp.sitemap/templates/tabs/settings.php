<?php

use \Bitrix\Main\Config\Option;
use \Sp\Sitemap\ModuleInterface;
use Bitrix\Main\Localization\Loc;

/**
 * файл подключается в \Sp\Sitemap\ModuleInterface::getTabHtml()
 * доступны поля класса \Sp\Sitemap\ModuleInterface
 * @var array $arParams
 * @var array $settings
 * @var string $tabName
 */
?>

<?php
Loc::loadMessages(__FILE__);
$pages = ModuleInterface::getSiteInfo();
?>

<tr>
    <th>Главная страница сайта:</th>
    <th>Протокол:</th>
    <th style="cursor: pointer">Частота обновления:</th>
    <th style="cursor: pointer">Дата модификации:</th>
    <th style="cursor: pointer">Приоритет:</th>
</tr>
<? foreach ($pages['sites'] as $keySite => $valueSite): ?>
    <tr>
        <td style="width:300px;" class="adm-detail-content-cell-l">
            <input title="priority" class="custom-input" type="text" name="settings[<?= $keySite ?>][domen]"
                   value="<?= Option::get(ModuleInterface::MODULE_ID, 'domen', '', $keySite) ?>">
        </td>
        <td class="adm-detail-content-cell-r">
            <select class="custom-input" name="settings[<?= $keySite ?>][port]">
                <option value="http"<?= Option::get(ModuleInterface::MODULE_ID, 'port', '',
                    $keySite) == 'http' ? ' selected="selected"' : '' ?>>http
                </option>
                <option value="https"<?= Option::get(ModuleInterface::MODULE_ID, 'port', '',
                    $keySite) == 'https' ? ' selected="selected"' : '' ?>>https
                </option>
            </select>
        </td>
        <td class="adm-detail-content-cell-r">
            <select class="custom-input" name="settings[<?= $keySite ?>][freq]">
                <? foreach (ModuleInterface::$freq as $freqKey => $freqValue): ?>
                    <option value="<?= $freqKey ?>"
                            <? if ($freqKey == Option::get(ModuleInterface::MODULE_ID, 'freq', '',
                                $keySite)): ?>selected="selected"<? endif; ?>><?= $freqValue ?></option>
                <? endforeach; ?>
            </select>
        </td>
        <td>
            <select title="modification" class="custom-input" name="settings[<?= $keySite ?>][mod]">
                <? foreach (ModuleInterface::$mod as $modKey => $modValue): ?>
                    <option value="<?= $modKey ?>" <? if ($modKey == Option::get(ModuleInterface::MODULE_ID, 'mod', '',
                        $keySite)): ?>selected="selected"<? endif; ?>><?= $modValue ?></option>
                <? endforeach; ?>
            </select>
        </td>
        <td>
            <input title="priority" class="custom-input" type="text" name="settings[<?= $keySite ?>][priority]"
                   value="<?= Option::get(ModuleInterface::MODULE_ID, 'priority', '', $keySite) ?>">
        </td>
    </tr>
<? endforeach; ?>

<tr>
    <td colspan="2">
        <div onclick="popupModal.update(this)" class="row-property-button left-margin">Сохранить</div>
    </td>
</tr>
