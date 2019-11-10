<?php

namespace Site\Banner\Controller;

use Bitrix\Main\Engine\Controller,
    Bitrix\Main\Loader,
    Site\Banner\Banner;

class Ajax extends Controller
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'apply' => [
                'prefilters' => []
            ]
        ];
    }

    /**
     * @param string $param2
     * @param string $param1
     * @return array
     */
    public static function applyAction()
    {
        if (!Loader::includeModule("site.banner")) {
            return false;
        }
        return [
            'ITEMS' => Banner::getData(\COption::GetOptionString("site.banner", "show_button", "N"))
        ];
    }
}