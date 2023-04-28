<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class sp_sitemap extends CModule
{
    public function __construct()
    {
        if (file_exists(__DIR__ . "/version.php")) {
            $arModuleVersion = [];

            include_once(__DIR__ . "/version.php");

            $this->MODULE_ID = str_replace("_", ".", get_class($this));
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            $this->MODULE_NAME = Loc::getMessage("MODULE_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("MODULE_DESCRIPTION");
            $this->PARTNER_NAME = Loc::getMessage("MODULE_PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("MODULE_PARTNER_URI");
        }

        return false;
    }

    public function doInstall()
    {
        $this->installFiles();
        ModuleManager::registerModule($this->MODULE_ID);
    }

    public function doUninstall()
    {
        $this->uninstallFiles();
        ModuleManager::unRegisterModule($this->MODULE_ID);
    }
}
