<?php

namespace Site\Banner;

use \Bitrix\Main\Loader,
    \Bitrix\Main\Page\Asset;

class Banner
{
    const BANNER_COOKIE = 'bobby_showed_banner';
    const BANNER_IB_CODE = "site_banner_popup";

    function createBanner()
    {
        $optShowBanner = \COption::GetOptionString("site.banner", "show_banner", "N");
        if ($optShowBanner == "Y") {

            global $APPLICATION, $USER;

            $optNumbImpress = intval(\COption::GetOptionString("site.banner", "number_of_impressions", "1"));
            $siteBannerCookie = intval($APPLICATION->get_cookie(static::BANNER_COOKIE));
            $optAdminOnly = \COption::GetOptionString("site.banner", "admin_only", "N");

            $arLibs = Array(
                "banner_lib" => Array(
                    "js" => "/bitrix/js/site.banner/site.banner.js",
                    "css" => "/bitrix/css/site.banner/style.css",
                    "lang" => "/local/css/site.banner/style.css",
                    "rel" => Array("popup")
                )
            );

            foreach ($arLibs as $libName => $libOptions) {
                \CJSCore::RegisterExt($libName, $libOptions);
            }

            if ($optNumbImpress > 0 && $siteBannerCookie < $optNumbImpress) {
                if (($optAdminOnly == "Y" && $USER->IsAdmin() || ($optAdminOnly == "N"))) {
                    self::showBanner();
                }
            }
        }
    }

    function showBanner()
    {
        global $APPLICATION;
        if (!\CSite::InDir('/bitrix/')) {

            $cookieValue = intval($APPLICATION->get_cookie(static::BANNER_COOKIE));
            \CUtil::InitJSCore(array('banner_lib'));
            Asset::getInstance()->addString("<script>BX.ready(function(){siteBanner.popapAjaxManager();});</script>");
            $cookieValue++;
            $APPLICATION->set_cookie(static::BANNER_COOKIE, $cookieValue, time() + 24 * 60 * 60);
        }
    }

    public static function getData($arBannerParams = Array())
    {
        $obCache = new \CPHPCache;
        $life_time = 60 * 60 * 24 * 7;
        $cache_id = "site.banner";
        if ($obCache->InitCache($life_time, $cache_id, "/" . SITE_ID . "/site.banner")) {
            $vars = $obCache->GetVars();
            $arBanner = $vars["BANNER"];
        } else {
            if (!Loader::includeModule("iblock")) {
                return;
            }

            $arOrder = array();
            $arFilter = array(
                "IBLOCK_CODE" => static::BANNER_IB_CODE,
                "ACTIVE" => "Y"
            );
            $arSelect = array(
                "IBLOCK_ID",
                "ID",
                "NAME",
                "PROPERTY_PC_IMAGE",
                "PROPERTY_MOBILE_IMAGE",
            );
            if ($arBannerParams['SHOW_BUTTON'] == 'Y') {
                $arSelect[] = "PROPERTY_BUTTON_NAME";
                $arSelect[] = "PROPERTY_LINK";
            }
            $banners = \CIBlockElement::GetList(
                $arOrder,
                $arFilter,
                false,
                false,
                $arSelect
            );
            $arBanner = array();
            $i = 0;
            while ($banner = $banners->Fetch()) {
                $arBanner[$i] = array(
                    "PC_IMAGE" => $banner['PROPERTY_PC_IMAGE_VALUE'],
                    "MOBILE_IMAGE" => $banner['PROPERTY_MOBILE_IMAGE_VALUE'],
                    "NAME" => $banner['NAME'],
                );
                if ($arBannerParams['SHOW_BUTTON'] == 'Y') {
                    $arBanner[$i]['BUTTON_NAME'] = $banner['PROPERTY_BUTTON_NAME_VALUE'];
                    $arBanner[$i]['LINK'] = $banner['PROPERTY_LINK_VALUE'];
                }
                $i++;
            }
            foreach ($arBanner as $key => $item) {
                if (!empty($item['PC_IMAGE'])) {
                    $file = \CFile::GetFileArray($item['PC_IMAGE']);
                    $arBanner[$key]['PC_IMAGE'] = $file;
                }
                if (!empty($item['MOBILE_IMAGE'])) {
                    $file = \CFile::GetFileArray($item['MOBILE_IMAGE']);
                    $arBanner[$key]['MOBILE_IMAGE'] = $file;
                }
            }
            if ($obCache->StartDataCache()) {
                $obCache->EndDataCache(array("BANNER" => $arBanner));
            }
        }
        return $arBanner;
    }
}