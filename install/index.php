<?php

use \Bitrix\Main\Localization\Loc,
    \Bitrix\Main\ModuleManager,
    \Bitrix\Main\Loader,
    \Bitrix\Main\EventManager;

Loc::loadMessages(__FILE__);

Class site_banner extends CModule
{
    var $MODULE_ID = "site.banner";
    var $MODULE_NAME;
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function site_banner()
    {
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->PARTNER_NAME = "Site default";
        $this->PARTNER_URI = "#";

        $this->MODULE_NAME = Loc::getMessage("THIS_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("THIS_MODULE_DESCRIPTION");
    }

    function DoInstall()
    {
        if (!IsModuleInstalled($this->MODULE_ID)) {
            $this->InstallDB();
            $this->InstallIblock();
            $this->InstallEvents();
            $this->InstallFiles();
        }
    }

    function InstallDB()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        return true;
    }

    function InstallIblock()
    {

        if (!Loader::includeModule("iblock")) {
            return;
        }
        $res = CIBlockType::GetByID("site_banner_popup");
        if (!$v = $res->GetNext()) {
            $arFields = Array(
                'ID' => 'site_banner_popup',
                'SECTIONS' => 'Y',
                'IN_RSS' => 'N',
                'SORT' => 100,
                'LANG' => Array(
                    'ru' => Array(
                        'NAME' => Loc::getMessage("SITE_BANNER_POPUP")
                    )
                )
            );
            $obBlocktype = new CIBlockType;
            $obBlocktype->Add($arFields);
        }

        $rsSites = CSite::GetList($by = "sort", $order = "desc", Array());
        $i = 0;
        while ($arSite = $rsSites->Fetch()) {
            $arSiteID[$i] = $arSite["ID"];
            $i++;
        }
        $res = CIBlock::GetList(
            Array(),
            Array(
                'TYPE' => 'site_banner_popup',
                'CODE' => 'site_banner_popup'
            ),
            true
        );
        $check_ib = false;
        while ($arRes = $res->Fetch()) {
            if ($arRes) {
                $check_ib = true;
            }
        }
        if (!$check_ib) {
            for ($i = 0; $i < count($arSiteID); $i++) {
                $ib = new CIBlock;
                $arFields = Array(
                    "ACTIVE" => "Y",
                    "NAME" => Loc::getMessage("SITE_BANNER_POPUP"),
                    "CODE" => "site_banner_popup",
                    "IBLOCK_TYPE_ID" => "site_banner_popup",
                    "INDEX_ELEMENT" => "N",
                    "INDEX_SECTION" => "N",
                    "WORKFLOW" => "N",
                    "SITE_ID" => $arSiteID[$i]
                );
                $ib->Add($arFields);
            }
        }
        $res = CIBlock::GetList(Array(), Array("CODE" => 'site_banner_popup'), true);
        $arRes = $res->Fetch();
        $rsProp = CIBlockProperty::GetList(Array("sort" => "asc", "name" => "asc"),
            Array("ACTIVE" => "Y", "IBLOCK_ID" => $arRes["ID"]));
        while ($arr = $rsProp->Fetch()) {
            $arPropsCode[] = $arr["CODE"];
        }
        if (!is_array($arPropsCode)) {
            $arPropsCode = array();
        }

        $ibp = new CIBlockProperty;

        if (!in_array("PC_IMAGE", $arPropsCode)) {
            $arFields = Array(
                "NAME" => Loc::getMessage("SITE_BANNER_POPUP_IB_PC_IMAGE"),
                "ACTIVE" => "Y",
                "SORT" => "100",
                "CODE" => "PC_IMAGE",
                "PROPERTY_TYPE" => "F",
                "IBLOCK_ID" => $arRes['ID'],
                "FILE_TYPE" => "jpg, gif, bmp, png, jpeg"
            );
            $PropID = $ibp->Add($arFields);
        }
        if (!in_array("MOBILE_IMAGE", $arPropsCode)) {
            $arFields = Array(
                "NAME" => Loc::getMessage("SITE_BANNER_POPUP_IB_MOBILE_IMAGE"),
                "ACTIVE" => "Y",
                "SORT" => "110",
                "CODE" => "MOBILE_IMAGE",
                "PROPERTY_TYPE" => "F",
                "IBLOCK_ID" => $arRes['ID'],
                "FILE_TYPE" => "jpg, gif, bmp, png, jpeg"
            );
            $PropID = $ibp->Add($arFields);
        }
        if (!in_array("BUTTON_NAME", $arPropsCode)) {
            $arFields = Array(
                "NAME" => Loc::getMessage("SITE_BANNER_POPUP_IB_BUTTON_NAME"),
                "ACTIVE" => "Y",
                "SORT" => "120",
                "CODE" => "BUTTON_NAME",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $arRes['ID']
            );
            $PropID = $ibp->Add($arFields);
        }
        if (!in_array("LINK", $arPropsCode)) {
            $arFields = Array(
                "NAME" => Loc::getMessage("SITE_BANNER_POPUP_IB_LINK"),
                "ACTIVE" => "Y",
                "SORT" => "130",
                "CODE" => "LINK",
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => $arRes['ID']
            );
            $PropID = $ibp->Add($arFields);
        }

        CIBlock::SetPermission($arRes['ID'], Array("1" => "X", "2" => "R"));
        return true;
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->registerEventHandler('main', 'OnEpilog', $this->MODULE_ID, '\Site\Banner\Banner', 'createBanner');
        $eventManager->registerEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, '\Site\Banner\Elements', 'CheckItems');
        $eventManager->registerEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, '\Site\Banner\Elements', 'CheckItems');
        return true;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/site.banner/install/css", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/css", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/site.banner/install/js", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/js", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/local/modules/site.banner/install/images", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/images", true, true);
        return true;
    }

    function DoUninstall()
    {
        $this->UnInstallDB();
        $this->InstallIblock();
        $this->UnInstallEvents();
        $this->UnInstallFiles();
    }

    function UnInstallDB()
    {
        COption::RemoveOption("site.banner");
        ModuleManager::unRegisterModule($this->MODULE_ID);
        return true;
    }

    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler('main', 'OnEpilog', $this->MODULE_ID, '\Site\Banner\Banner', 'createBanner');
        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementUpdate', $this->MODULE_ID, '\Site\Banner\Elements', 'CheckItems');
        $eventManager->unRegisterEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, '\Site\Banner\Elements', 'CheckItems');
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/css/site.banner/");
        DeleteDirFilesEx("/bitrix/js/site.banner/");
        DeleteDirFilesEx("/bitrix/images/site.banner/");
        return true;
    }
}