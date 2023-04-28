<?php

namespace Sp\Sitemap;

use DOMDocument;
use \Bitrix\Main\Config\Option;

class SiteMapGenerator
{
    private string $siteID;
    private string $domain;
    private array $siteParams = [];
    private array $links = [];
    private array $settings = [];
    private array $filesMap = [];

    public function __construct($siteID)
    {
        $this->siteID = $siteID;
    }

    public function getSiteParams()
    {
        $oSites = \CSite::GetList(
            $by,
            $order,
            ['ID' => $this->siteID]
        );
        if ($aSite = $oSites->Fetch()) {
            $this->siteParams = $aSite;
        }
    }

    public function run()
    {
        $this->getSiteParams();
        $this->getSiteSetting();
        $this->getSettings();
        if (isset($this->settings['STATIC'])) {
            $this->generateStaticMap();
        }
        if (isset($this->settings['SECTION'])) {
            $this->generateSectionsMap();
        }
        if (isset($this->settings['ELEMENT'])) {
            $this->generateElementsMap();
        }

        if (!empty($this->filesMap)) {
            $params = [];
            foreach ($this->filesMap as $value) {
                $lastModeFile = $this->getFormattedDate(filectime($_SERVER['DOCUMENT_ROOT']. '/sitemap/'. $value), false);
                $value = $this->domain . '/sitemap/' . $value;
                $params[] = [
                    'loc' => $value,
                    'lastmod' => $lastModeFile,
                ];
            }
            $this->generateFile($params, 'sitemapindex', 'sitemap', 'sitemap.xml');
        }
    }


    private function getSettings()
    {
        $hlEntity = getHLClass('SiteMapSettings');
        $obj = $hlEntity::getList(['filter' => ['UF_SITE_ID' => $this->siteID]]);
        while ($setting = $obj->fetch()) {
            $this->settings[$setting['UF_TYPE']][] = $setting;
        }
    }

    private function getSiteSetting()
    {
        $config = Option::getForModule('sp.sitemap', $this->siteID);
        $this->domain = $config['port'] . '://' . $config['domen'];
        $this->settings['STATIC'][] = [
            'UF_URL' => '/',
            'UF_PRIORITY' => (isset($config['priority'])) ? $config['priority'] : '',
            'UF_FREQ' => (isset($config['mod'])) ? $config['mod'] : '',
            'UF_LASTMODE' => (isset($config['mod'])) ? $config['mod'] : '',
            'UF_PATH_TO_FILE' => '/index.php',
        ];
    }

    private function generateStaticMap()
    {
        $data = [];
        foreach ($this->settings['STATIC'] as $key => $value) {
            $value['UF_URL'] = $this->getPageUrl($value['UF_URL']);
            $data[$key]['loc'] = $value['UF_URL'];
            if (!empty($value['UF_FREQ'])) {
                $data[$key]['changefreq'] = $value['UF_FREQ'];
            }
            if (!empty($value['UF_PRIORITY'])) {
                $data[$key]['priority'] = $value['UF_PRIORITY'];
            }
            if (!empty($value['UF_LASTMODE'])) {
                $data[$key]['lastmod'] = $this->getFormattedDate(filectime($this->siteParams["ABS_DOC_ROOT"] . $value["UF_PATH_TO_FILE"]),
                    false);
            }

            $this->links[] = $value['UF_URL'];
        }
        if (!empty($data)) {
            $this->generateFile($data, 'urlset', 'url', 'static.xml');
        }
    }

    /**
     * @throws \Bitrix\Main\SystemException
     */
    private function generateSectionsMap()
    {
        $params = [];
        $sectionKeys = [];
        foreach ($this->settings['SECTION'] as $sectionParams) {
            $sectionParams['IBLOCK_ID'] = getIblockIdByCode($sectionParams['UF_CODE']);
            $sectionKeys[$sectionParams['UF_CODE']] = $sectionParams;
        }
        $iBlockIDFilter = array_column($sectionKeys, 'IBLOCK_ID');
        $tree = \CIBlockSection::GetTreeList(
            ['IBLOCK_ID' => $iBlockIDFilter, 'ACTIVE' => 'Y'],
            ['IBLOCK_ID', 'IBLOCK_CODE', 'SECTION_PAGE_URL', 'TIMESTAMP_X']
        );
        while ($section = $tree->GetNext()) {
            $section['SECTION_PAGE_URL'] = $this->getPageUrl($section['SECTION_PAGE_URL']);

            if (in_array($section['SECTION_PAGE_URL'], $this->links)) {
                continue;
            }
            $value = $sectionKeys[$section['IBLOCK_CODE']];
            $params[$section['ID']]['loc'] = $section['SECTION_PAGE_URL'];
            if (!empty($value['UF_FREQ'])) {
                $params[$section['ID']]['changefreq'] = $value['UF_FREQ'];
            }
            if (!empty($value['UF_PRIORITY'])) {
                $params[$section['ID']]['priority'] = $value['UF_PRIORITY'];
            }
            if (!empty($value['UF_LASTMODE'])) {
                $params[$section['ID']]['lastmod'] = $this->getFormattedDate($section['TIMESTAMP_X']);
            }
            $this->links[] = $section['SECTION_PAGE_URL'];
        }
        if (!empty($params)) {
            $this->generateFile($params, 'urlset', 'url', 'sections.xml');
        }

    }

    /**
     * @throws \Bitrix\Main\SystemException
     */
    private function generateElementsMap()
    {
        $params = [];
        $elementsSetting = [];
        foreach ($this->settings["ELEMENT"] as $elementParams) {
            $elementParams['IBLOCK_ID'] = getIblockIdByCode($elementParams['UF_CODE']);
            $elementsSetting[$elementParams['UF_CODE']] = $elementParams;
        }

        $res = \CIBlockElement::GetList([], [
            'IBLOCK_ID' => array_column($elementsSetting, 'IBLOCK_ID'),
            'ACTIVE' => 'Y'
        ], false, false, ['TIMESTAMP_X', 'DETAIL_PAGE_URL', 'IBLOCK_CODE', 'IBLOCK_ID']);
        while ($element = $res->GetNext()) {
            $element['DETAIL_PAGE_URL'] = $this->getPageUrl($element['DETAIL_PAGE_URL']);
            if (in_array($element['DETAIL_PAGE_URL'], $this->links)) {
                continue;
            }
            $value = $elementsSetting[$element['IBLOCK_CODE']];
            $params[$element['ID']]['loc'] = $element['DETAIL_PAGE_URL'];
            if (!empty($value['UF_FREQ'])) {
                $params[$element['ID']]['changefreq'] = $value['UF_FREQ'];
            }
            if (!empty($value['UF_PRIORITY'])) {
                $params[$element['ID']]['priority'] = $value['UF_PRIORITY'];
            }
            if (!empty($value['UF_LASTMODE'])) {
                $params[$element['ID']]['lastmod'] = $this->getFormattedDate($element['TIMESTAMP_X']);
            }
            $this->links[] = $element['DETAIL_PAGE_URL'];
        }

        if (!empty($params)) {
            $this->generateFile($params, 'urlset', 'url', 'elements.xml');
        }
    }

    private function generateFile($params, $mainNode, $childNodeKey, $file)
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $root = $dom->createElement($mainNode);
        $root->setAttribute("xmlns", 'http://www.sitemaps.org/schemas/sitemap/0.9');
        foreach ($params as $childParamsValue) {
            $childNode = $dom->createElement($childNodeKey);
            foreach ($childParamsValue as $key => $childValue) {
                $paramsNode = $dom->createElement($key, $childValue);
                $childNode->appendChild($paramsNode);
            }
            $root->appendChild($childNode);
        }
        $dom->appendChild($root);
        $status = $dom->save($_SERVER['DOCUMENT_ROOT'] . '/../sitemap/' . $file);
        if ($status) {
            $this->filesMap[] = $file;
        }
    }


    private function getFormattedDate($date, $timestamp = true)
    {
        if ($timestamp) {
            $date = strtotime($date);
        }
        return date('c', $date);
    }

    private function getPageUrl($link): string
    {
        return $this->domain . $link;
    }

}
