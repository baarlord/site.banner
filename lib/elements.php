<?php
/**
 * Используется класс Elements взятый у другого программиста.
 */

namespace Site\Banner;

use \Bitrix\Main\Loader;

class Elements
{
    const IBID_AGENT_CHECK = 10;

    private static $selfName = __CLASS__;

    function ClearCache()
    {
        $cache = new \CPHPCache();
        $cache_id = 'site.banner';
        $cache_path = '/s1/site.banner';
        $cache->Clean($cache_id, $cache_path);
        $iTime = time();
        $iNearest = self::CheckDates($iTime);
        global $pPERIOD;
        $pPERIOD = ($iNearest - $iTime) + 10;
        self::SetAgent($iNearest, $iTime);
        return self::$selfName . "::ClearCache();";
    }

    function CheckItems(&$arFields)
    {
        if ($arFields['IBLOCK_ID'] != self::IBID_AGENT_CHECK) {
            return;
        }
        $iTime = time();
        $iNearest = self::CheckDates($iTime);
        self::SetAgent($iNearest, $iTime);
    }

    function CheckDates($iTime)
    {
        if (!Loader::includeModule("iblock")) {
            return;
        }
        $iNearest = 9999999999;
        $bFind = false;
        $arFilter = array(
            'IBLOCK_ID' => self::IBID_AGENT_CHECK,
            array(
                'LOGIC' => 'OR',
                array('>=DATE_ACTIVE_FROM' => ConvertTimeStamp($iTime, "FULL")),
                array('>=DATE_ACTIVE_TO' => ConvertTimeStamp($iTime, "FULL"))
            )
        );

        $rsNews = \CIBlockElement::GetList(array(), $arFilter);
        while ($arNews = $rsNews->GetNext()) {
            if ($arNews['ACTIVE_FROM']) {
                $iCurTime = MakeTimeStamp($arNews['ACTIVE_FROM'], \CSite::GetDateFormat());
                if (($iCurTime > $iTime) && ($iCurTime < $iNearest)) {
                    $iNearest = $iCurTime;
                    $bFind = true;
                }
            }
            if ($arNews['ACTIVE_TO']) {
                $iCurTime = MakeTimeStamp($arNews['ACTIVE_TO'], \CSite::GetDateFormat());
                if (($iCurTime > $iTime) && ($iCurTime < $iNearest)) {
                    $iNearest = $iCurTime;
                    $bFind = true;
                }
            }

        }
        if ($bFind) {
            return $iNearest;
        } else {
            return $iTime + 36000000;
        }
    }

    function SetAgent($iNearest, $iTime)
    {
        $rsAgent = \CAgent::GetList(array(), array('NAME' => '%' . self::$selfName . '::ClearCache%'));
        if ($arAgent = $rsAgent->GetNext()) {
            $arFields = array(
                'NEXT_EXEC' => ConvertTimeStamp($iNearest + 10, "FULL"),
                'AGENT_INTERVAL' => ($iNearest - $iTime) + 10,
            );
            \CAgent::Update($arAgent['ID'], $arFields);
        } else {
            \CAgent::AddAgent(
                self::$selfName . "::ClearCache();",
                "site.banner",
                "N",
                ($iNearest - $iTime) + 10,
                "",
                "Y",
                "",
                100
            );
        }
    }
}