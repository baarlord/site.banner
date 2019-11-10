<?php

namespace Site\Banner\Controller;

use Bitrix\Main\Engine\Controller,
    Bitrix\Main\Loader,
    Site\Banner\Banner;

class Ajax extends Controller
{
    public function configureActions()
    {
        return [
            'apply' => [
                'prefilters' => []
            ]
        ];
    }

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