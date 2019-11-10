<?php

use \Bitrix\Main\Loader,
    \Bitrix\Main\Localization\Loc;

global $USER;
$RIGHT_R = $USER->CanDoOperation('site_banner');
$RIGHT_W = $USER->CanDoOperation('site_banner');
if ($RIGHT_R || $RIGHT_W) {


    if (!Loader::includeModule('iblock')) {
        return;
    }

    IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/options.php");
    Loc::loadMessages(__FILE__);

    $module_id = "site.banner";

    $arAllOptions = array(
        array("show_banner", Loc::getMessage("SB_SHOW"), array("checkbox")),
        array("admin_only", Loc::getMessage("SB_ADMIN_ONLY"), array("checkbox")),
        array("show_button", Loc::getMessage("SB_SHOW_BUTTON"), array("checkbox")),
        array("number_of_impressions", Loc::getMessage("SB_NUMBER_OF_IMPRESS"), array("text")),
    );
    $aTabs = array(
        array(
            "DIV" => "edit1",
            "TAB" => Loc::getMessage("SB_TAB_SET"),
            "ICON" => "sb_settings",
            "TITLE" => Loc::getMessage("SB_TAB_TITLE_SET")
        )
    );
    $tabControl = new \CAdminTabControl("tabControl", $aTabs);

    $adminOnly = COption::GetOptionString('site.banner', 'show_banner', 'N');
    $adminOnly = COption::GetOptionString('site.banner', 'admin_only', 'N');
    $showButton = COption::GetOptionString('site.banner', 'show_button', 'N');
    $numberImpress = COption::GetOptionString('site.banner', 'number_of_impressions', '1');

    if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($Update . $Apply) > 0 && $RIGHT_W && check_bitrix_sessid()) {
        foreach ($arAllOptions as $arOption) {
            foreach ($arAllOptions as $arOption) {
                $name = $arOption[0];
                $val = trim($_REQUEST[$name], " \t\n\r");
                if ($arOption[2][0] == "checkbox" && $val != "Y") {
                    $val = "N";
                }
                COption::SetOptionString($module_id, $name, $val, $arOption[1]);
            }
        }
        ob_start();
        $Update = $Update . $Apply;
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/admin/group_rights2.php");
        ob_end_clean();

        if (strlen($_REQUEST["back_url_settings"]) > 0) {
            LocalRedirect($_REQUEST["back_url_settings"]);
        } else {
            LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($module_id) . "&lang=" . urlencode(LANGUAGE_ID) . "&" . $tabControl->ActiveTabParam());
        }
    }

    ?>
    <form method="post"
          action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($module_id) ?>&amp;lang=<?= LANGUAGE_ID ?>">
        <?
        $tabControl->Begin();
        $tabControl->BeginNextTab();

        foreach ($arAllOptions as $arOption) {
            $val = COption::GetOptionString($module_id, $arOption[0]);
            $type = $arOption[2];
            ?>
            <tr>
                <td width="40%" nowrap <? if ($type[0] == "textarea") echo 'class="adm-detail-valign-top"' ?>>
                    <label for="<? echo htmlspecialcharsbx($arOption[0]) ?>"><? echo $arOption[1] ?></label>
                <td width="60%">
                    <? if ($type[0] == "checkbox"): ?>
                        <input type="checkbox" name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                               id="<? echo htmlspecialcharsbx($arOption[0]) ?>" value="Y"<? if ($val == "Y") {
                            echo " checked";
                        } ?>>
                    <? elseif ($type[0] == "text"): ?>
                        <input type="text" size="<? echo $type[1] ?>" maxlength="255"
                               value="<? echo htmlspecialcharsbx($val) ?>"
                               name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                               id="<? echo htmlspecialcharsbx($arOption[0]) ?>">
                    <? elseif ($type[0] == "textarea"): ?>
                        <textarea rows="<? echo $type[1] ?>" cols="<? echo $type[2] ?>"
                                  name="<? echo htmlspecialcharsbx($arOption[0]) ?>"
                                  id="<? echo htmlspecialcharsbx($arOption[0]) ?>"><? echo htmlspecialcharsbx($val) ?></textarea>
                    <? elseif ($type[0] == "selectbox"):
                        ?><select name="<?
                    echo htmlspecialcharsbx($arOption[0]) ?>"><?
                        foreach ($type[1] as $key => $value) {
                            ?>
                            <option value="<?
                            echo $key ?>"<?
                            if ($val == $key) echo " selected" ?>><?
                            echo htmlspecialcharsbx($value) ?></option><?
                        }
                        ?></select><?
                    endif ?>
                </td>
            </tr>
        <? } ?>
        <? $tabControl->Buttons(); ?>
        <input <? if (!$RIGHT_W) echo "disabled" ?> type="submit" name="Update"
                                                    value="<?= Loc::getMessage("SB_SAVE") ?>"
                                                    title="<?= Loc::getMessage("SB_OPT_SAVE_TITLE") ?>"
                                                    class="adm-btn-save">
        <input <? if (!$RIGHT_W) echo "disabled" ?> type="submit" name="Apply"
                                                    value="<?= Loc::getMessage("SB_OPT_APPLY") ?>"
                                                    title="<?= Loc::getMessage("SB_OPT_APPLY_TITLE") ?>">
        <? if (strlen($_REQUEST["back_url_settings"]) > 0): ?>
            <input <? if (!$RIGHT_W) echo "disabled" ?> type="button" name="Cancel"
                                                        value="<?= Loc::getMessage("SB_OPT_CANCEL") ?>"
                                                        title="<?= Loc::getMessage("SB_OPT_CANCEL_TITLE") ?>"
                                                        onclick="window.location='<? echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"])) ?>'">
            <input type="hidden" name="back_url_settings"
                   value="<?= htmlspecialcharsbx($_REQUEST["back_url_settings"]) ?>">
        <? endif ?>
        <?= bitrix_sessid_post(); ?>
        <? $tabControl->End(); ?>
    </form>
<? } ?>