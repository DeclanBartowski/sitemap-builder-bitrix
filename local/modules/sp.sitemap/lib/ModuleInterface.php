<?php

namespace Sp\Sitemap;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;

class ModuleInterface
{
    const MODULE_ID = 'sp.sitemap';
    public static array $settings = [];
    public static array $freq = [
        '' => 'не выбрано',
        'always' => 'всегда',
        'hourly' => 'ежечасно',
        'daily' => 'ежедневно',
        'weekly' => 'еженедельно',
        'monthly' => 'ежемесячно',
        'yearly' => 'ежегодно',
        'never' => 'никогда',
    ];
    public static array $mod = [
        '' => 'нет',
        '1' => 'да',
    ];
    public static array $staticFolders = [];
    public static array $existsFolders = [];
    public static string $rootSite = '';
    public static string $staticType = 'STATIC';
    public static string $elementType = 'ELEMENT';
    public static string $sectionType = 'SECTION';

    public function getTabHtml($tabName, $arParams = [])
    {
        $fileNAme = __DIR__ . '/../templates/tabs/' . $tabName . '.php';

        if (!file_exists($fileNAme)) {
            return false;
        }
        ob_start();
        include $fileNAme;
        $html = ob_get_clean();

        echo $html;
    }

    public static function getTabs()
    {
        return [
            ['DIV' => 'settings', 'TAB' => 'Основные настройки', 'TITLE' => 'Основные настройки модуля',],
            ['DIV' => 'files', 'TAB' => 'Файлы', 'TITLE' => 'Настройки для файловой структуры',],
            ['DIV' => 'ibs', 'TAB' => 'Инфоблоки', 'TITLE' => 'Настройки для инфоблоков',],
        ];
    }

    static public function getSiteInfo()
    {
        $by = "sort";
        $order = "desc";
        $oSites = \CSite::GetList(
            $by,
            $order,
            array()
        );
        $arSite = [];
        while ($aSite = $oSites->Fetch()) {
            $aDomain = explode("\n", $aSite['DOMAINS']);
            $arSite[$aSite['LID']] = array(
                'NAME' => $aSite['NAME'],
                'DOMAIN' => trim($aDomain[0]),
                'DIR' => $aSite['ABS_DOC_ROOT'] . ((strlen($aSite['DIR']) > 1) ? $aSite['DIR'] : '')
            );
        }

        return array(
            'sites' => $arSite,
        );
    }

    public static function getStatic()
    {
        $aSites = self::getSiteInfo();
        self::getPages();
        foreach ($aSites['sites'] as $site => $aSite) {
            $ROOT = $aSite['DIR'];
            self::$rootSite = $ROOT;
            self::showTree($ROOT, $site);
        }
        return [
            'sites' => $aSites['sites'],
            'elements' => self::$staticFolders
        ];
    }

    public static function showTree($folders, $site = false)
    {

        $files = scandir($folders);

        if (in_array('index.php', $files) && in_array('.section.php', $files)) {
            for ($u = 0; $u < $t = count($files); $u++) {
                if (($files[$u] == '.') || ($files[$u] == '..')) {
                    continue;
                } else {
                    if (is_dir($folders . '/' . $files[$u])) {
                        $files__ = scandir($folders . '/' . $files[$u]);
                        if (in_array('index.php', $files__) && in_array('.section.php', $files__)) {
                            $fp = fopen($folders . '/' . $files[$u] . '/.section.php', 'r');
                            if ($fp) {
                                while (($buffer = fgets($fp)) !== false) {
                                    if (preg_match("/\WsSectionName\s=\s\"(.*?)\"/", $buffer, $matches)) {
                                        break;
                                    }
                                }
                            }

                            $params = [
                                'TYPE' => self::$staticType,
                                'SITE_ID' => $site,
                                'CODE' => $files[$u],
                                'NAME' => $matches[1],
                                'URL' => preg_replace('|([/]+)|s', '/',
                                    str_replace(self::$rootSite, '/', $folders) . '/' . $files[$u] . '/'),
                                'PATH_TO_FILE' => str_replace(self::$rootSite, "",
                                        $folders . '/' . $files[$u]) . '/index.php'
                            ];

                            if (isset(self::$existsFolders[$params['SITE_ID']][$params['URL']])) {
                                continue;
                            }
                            self::$staticFolders[$site][] = $params;
                        }
                        self::showTree($folders . '/' . $files[$u], $site);
                    }
                }
            }
        }
    }

    public static function getIblockBySite()
    {
        Loader::includeModule('iblock');
        $result = [];
        $iBlockList = IblockTable::getList([
            'filter' => ['ACTIVE' => 'Y'],
            'select' => ['LID', 'CODE', 'NAME']
        ]);
        while ($iBlock = $iBlockList->fetch()) {
            $result[$iBlock['LID']][] = $iBlock;
        }
        return $result;
    }

    public static function getIBlock(): array
    {
        return array_merge(self::getSiteInfo(),
            ['elements' => self::getIblockBySite(), 'exists' => self::getIBlocks()]);
    }


    public function update($params, $options = false)
    {
        $hlClassName = self::getHL();
        if (!$hlClassName) {
            throw new  \Exception('Выполнить миграция с HL настройками');
        }
        if ($options) {
            foreach ($params as $keySite => $paramsSite) {
                foreach ($paramsSite as $keyParams => $valueParams) {
                    Option::set(self::MODULE_ID, $keyParams, $valueParams, $keySite);
                }
            }
        } else {
            foreach ($params as $key => $value) {
                $hlClassName::update($key, [
                    'UF_FREQ' => $value['freq'],
                    'UF_PRIORITY' => $value['priority'],
                    'UF_LASTMODE' => $value['mod'],
                ]);
            }
        }
    }

    public static function save($request)
    {
        $hlClassName = self::getHL();
        if (!$hlClassName) {
            throw new  \Exception('Выполнить миграция с HL настройками');
        }
        foreach ($request["TYPE"] as $key => $value) {
            $hlClassName::add([
                'UF_TYPE' => $value,
                'UF_TITLE' => $request["NAME"][$key],
                'UF_CODE' => $request["CODE"][$key],
                'UF_SITE_ID' => $request["SITE_ID"][$key],
                'UF_URL' => $request['URL'][$key],
                'UF_PATH_TO_FILE' => (isset($request['PATH_TO_FILE'][$key])) ? $request['PATH_TO_FILE'][$key] : '',
            ]);
        }
    }


    public static function saveIBlock($request)
    {

        $request = Context::getCurrent()->getRequest();
        $postField = $request->getPostList()->toArray();
        $hlClassName = self::getHL();
        if (!$hlClassName) {
            throw new  \Exception('Выполнить миграция с HL настройками');
        }
        foreach ($postField['TYPE'] as $key => $value) {
            $hlClassName::add(
                [
                    'UF_TYPE' => $value,
                    'UF_SITE_ID' => $postField['SITE_ID'][$key],
                    'UF_CODE' => $postField['CODE'][$key],
                    'UF_TITLE' => $postField['NAME'][$key],
                    'UF_URL' => $postField['URL'][$key],
                ]
            );
        }
    }

    public static function deleteStaticPage()
    {
        $request = Context::getCurrent()->getRequest();
        $postField = $request->getPostList()->toArray();
        $hlClassName = self::getHL();
        if (!$hlClassName) {
            throw new  \Exception('Выполнить миграция с HL настройками');
        }
        $hlClassName::delete($postField['id']);
    }

    public static function getIBlocks()
    {
        $hlClassName = self::getHL();
        $iBlocks = [];
        $result = $hlClassName::getList([
            'filter' => ['UF_TYPE' => [self::$elementType, self::$sectionType]]
        ]);
        while ($settings = $result->fetch()) {

            self::$existsFolders[$settings['UF_SITE_ID']][$settings['UF_TYPE'] . '_' . $settings['UF_CODE']] = $settings;
            $iBlocks[$settings['UF_SITE_ID']][$settings['UF_TYPE'] . '_' . $settings['UF_CODE']] = $settings;
        }
        return array_merge(self::getSiteInfo(), [
            'data' => $iBlocks,
        ]);
    }

    public static function getPages()
    {
        $hlClassName = self::getHL();
        $result = $hlClassName::getList([
            'filter' => ['UF_TYPE' => self::$staticType]
        ]);
        while ($settings = $result->fetch()) {
            self::$existsFolders[$settings['UF_SITE_ID']][$settings['UF_URL']] = $settings;
        }

        return array_merge(self::getSiteInfo(), [
            'data' => self::$existsFolders,
        ]);
    }

    private static function getHL()
    {
        return getHLClass('SiteMapSettings');
    }

    public static function delete($id)
    {
        $hlClassName = self::getHL();
        if (!$hlClassName) {
            throw new  \Exception('Выполнить миграция с HL настройками');
        }
        $hlClassName::delete($id);
    }
}
