<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if($def_group!="")
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
}
else
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
}

$arParamsToDelete = array(
	"login",
	"logout",
	"register",
	"forgot_password",
	"change_password",
	"confirm_registration",
	"confirm_code",
	"confirm_user_id",
);

if(defined("AUTH_404"))
{
	$arResult["AUTH_URL"] = SITE_DIR."auth.php";
}
else
{
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("register=yes", $arParamsToDelete);
}

$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);

foreach ($arResult as $key => $value)
{
	if (!is_array($value)) $arResult[$key] = htmlspecialchars($value);
}

$arRequestParams = array(
	"USER_NAME",
	"USER_LAST_NAME",
	"USER_LOGIN",
	"USER_PASSWORD",
	"USER_CONFIRM_PASSWORD",
);

foreach ($arRequestParams as $param)
{
	$arResult[$param] = strlen($_REQUEST[$param]) > 0 ? $_REQUEST[$param] : "";
	$arResult[$param] = htmlspecialchars($arResult[$param]);
}
// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0)
{
	foreach ($arUserFields as $FIELD_NAME => $arUserField)
	{
		if ($arUserField["MANDATORY"] != "Y")
			continue;
		$arUserField["EDIT_FORM_LABEL"] = strLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
		$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
		$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
	}
}
if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
	$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
$arResult["bVarsFromForm"] = (is_array($arParams['AUTH_RESULT']) || strlen($arParams["AUTH_RESULT"]) <= 0) ? false : true;
// ******************** /User properties ***************************************************
$arResult["USER_EMAIL"] = htmlspecialchars(strlen($_REQUEST["sf_EMAIL"])>0 ? $_REQUEST["sf_EMAIL"] : $_REQUEST["USER_EMAIL"]);

$arResult["USE_CAPTCHA"] = COption::GetOptionString("main", "captcha_registration", "N") == "Y" ? "Y" : "N";

if ($arResult["USE_CAPTCHA"])
{
	$arResult["CAPTCHA_CODE"] = htmlspecialchars($GLOBALS["APPLICATION"]->CaptchaGetCode());
}

$arResult["USE_EMAIL_CONFIRMATION"] = COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" ? "Y" : "N";

$this->IncludeComponentTemplate();
?>