<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/***************** For custom component only ***********************/
	if (!empty($arParams["arFormParams"]) && is_array($arParams["arFormParams"]))
	{
		$arParams["FID"] = $arParams["arFormParams"]["FID"];
		$arParams["TID"] = $arParams["arFormParams"]["TID"];
		$arParams["MID"] = $arParams["arFormParams"]["MID"];
		
		$arParams["URL_TEMPLATES_LIST"] = $arParams["arFormParams"]["URL_TEMPLATES_LIST"];
		$arParams["URL_TEMPLATES_READ"] = $arParams["arFormParams"]["URL_TEMPLATES_READ"];
		
		$arParams["PAGE_NAME"] = $arParams["arFormParams"]["PAGE_NAME"];
		$arParams["MESSAGE_TYPE"] = $arParams["arFormParams"]["MESSAGE_TYPE"];
		$arParams["FORUM"] = $arParams["arFormParams"]["arForum"];
		$arParams["bVarsFromForm"] = $arParams["arFormParams"]["bVarsFromForm"];
		
		$arParams["PATH_TO_SMILE"] = $arParams["arFormParams"]["PATH_TO_SMILE"];
		$arParams["PATH_TO_ICON"] = $arParams["arFormParams"]["PATH_TO_ICON"];
		$arParams["CACHE_TIME"] = $arParams["arFormParams"]["CACHE_TIME"];
	}
/***************** BASE ********************************************/
	$arParams["FID"] = intVal(empty($arParams["FID"]) ? $_REQUEST["FID"] : $arParams["FID"]);
	$arParams["TID"] = intVal(empty($arParams["TID"]) ? $_REQUEST["TID"] : $arParams["TID"]);
	$arParams["MID"] = intVal(empty($arParams["MID"]) ? $_REQUEST["MID"] : $arParams["MID"]);
	
	$arParams["PAGE_NAME"] = (empty($arParams["PAGE_NAME"]) ? $_REQUEST["PAGE_NAME"] : $arParams["PAGE_NAME"]);
	$arParams["MESSAGE_TYPE"] = (in_array(strToUpper($arParams["MESSAGE_TYPE"]), array("REPLY", "EDIT", "NEW")) ? strToUpper($arParams["MESSAGE_TYPE"]):"NEW");
	$arParams["FORUM"] = (!empty($arParams["arForum"]) ? $arParams["arForum"] : (!empty($arParams["FORUM"]) ? $arParams["FORUM"] : array()));
	$arParams["bVarsFromForm"] = ($arParams["bVarsFromForm"] == "Y" || $arParams["bVarsFromForm"] === true ? "Y" : "N");
/***************** URL *********************************************/
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	$URL_NAME_DEFAULT = array(
			"list" => "PAGE_NAME=list&FID=#FID#",
			"message" => "PAGE_NAME=message&FID=#FID#&TID=#TID#&MID=#MID#", 
			"help" =>"PAGE_NAME=help",
			"rules" =>"PAGE_NAME=rules");
	if (empty($arParams["URL_TEMPLATES_MESSAGE"]) && !empty($arParams["URL_TEMPLATES_READ"]))
	{
		$arParams["URL_TEMPLATES_MESSAGE"] = $arParams["URL_TEMPLATES_READ"];
	}
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($arParams["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$arParams["URL_TEMPLATES_".strToUpper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~URL_TEMPLATES_".strToUpper($URL)] = $arParams["URL_TEMPLATES_".strToUpper($URL)];
		$arParams["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($arParams["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
	$arParams["AJAX_CALL"] = (($_REQUEST["AJAX_CALL"] == "Y" && $arParams["AJAX_TYPE"] == "Y") ? "Y" : "N");
	$arParams["SMILE_TABLE_COLS"] = (intval($arParams["SMILE_TABLE_COLS"]) > 0 ? intval($arParams["SMILE_TABLE_COLS"]) : 3);
/***************** STANDART ****************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$arResult["SHOW_SEARCH"] = (IsModuleInstalled("search") ? "Y" : "N");
$arResult["IsAuthorized"] = ($USER->IsAuthorized() ? "Y" : "N");
$arParams["PERMISSION"] = ForumCurrUserPermissions($arParams["FID"]);
$arParams["FORUM"] = CForumNew::GetByID($arParams["FID"]);

$arResult["URL"] = array(
	"LIST" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"])), 
	"~LIST" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_LIST"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"])), 
	"READ" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_MESSAGE"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))), 
	"~READ" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_MESSAGE"], 
		array("FID" => $arParams["FID"], "TID" => $arParams["TID"], "MID"=>((intVal($arParams["MID"]) > 0) ? intVal($arParams["MID"]) : "s"))), 
	"RULES" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_RULES"], array()), 
	"~RULES" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_RULES"], array()), 
	"HELP" => CComponentEngine::MakePathFromTemplate($arParams["URL_TEMPLATES_HELP"], array()), 
	"~HELP" => CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_HELP"], array()));
$_REQUEST["FILES"] = is_array($_REQUEST["FILES"]) ? $_REQUEST["FILES"] : array();
$_REQUEST["FILES_TO_UPLOAD"] = is_array($_REQUEST["FILES_TO_UPLOAD"]) ? $_REQUEST["FILES_TO_UPLOAD"] : array();
$arResult["SHOW_POST_FORM"] = "Y";
$arResult["SHOW_PANEL_GUEST"] = "N";
$arResult["SHOW_PANEL_NEW_TOPIC"] = "N";
$arResult["SHOW_PANEL_EDIT"] = ($arParams["MESSAGE_TYPE"] == "EDIT" ? "Y" : "N");
$arResult["SHOW_PANEL_EDIT_PANEL_GUEST"] = ($USER->IsAuthorized() ? "N" : "Y");
$arResult["SHOW_PANEL_EDIT_ASK"] = ($arParams["PERMISSION"] > "Q" ? "Y" : "N");
$arResult["SHOW_SUBSCRIBE"] = ($USER->IsAuthorized() && $arParams["PERMISSION"] > "E" ? "Y" : "N");
$arResult["SHOW_PANEL_ATTACH_IMG"] = (in_array($arParams["FORUM"]["ALLOW_UPLOAD"], array("Y", "F", "A")) ? "Y" : "N");
$arResult["SHOW_PANEL_TRANSLIT"] = (LANGUAGE_ID == "ru" ? "Y" : "N");
$arResult["TRANSLIT"] = (LANGUAGE_ID == "ru" ? "Y" : "N");
$arResult["ForumPrintIconsList"] = "";
$arResult["ForumPrintSmilesList"] = "";
$arResult["~TOPIC"] = array();
$arResult["MESSAGE"] = array(
	"AUTHOR_ID" => $USER->GetParam("USER_ID"), 
	"USE_SMILES" => "Y", 
	"AUTHOR_NAME" => GetMessage("FPF_GUEST"), 
	"AUTHOR_EMAIL" => "", 
	"POST_MESSAGE" => "", 
	"EDITOR_NAME" => GetMessage("FPF_GUEST"), 
	"EDITOR_EMAIL" => "quest@guest.com", 
	"EDIT_REASON" => "", 
	"FILES" => array()); 
$arResult["TOPIC"] = array(
	"TITLE" => "", 
	"TAGS" => "", 
	"DESCRIPTION" => "", 
	"ICON_ID" => "");
/********************************************************************
				/Default params
********************************************************************/
$bShowForm = false;
if ($arParams["MESSAGE_TYPE"] == "REPLY" && $arParams["TID"] > 0)
	$bShowForm = CForumMessage::CanUserAddMessage($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID());
elseif ($arParams["MESSAGE_TYPE"] == "EDIT" && $arParams["MID"] > 0)
	$bShowForm = CForumMessage::CanUserUpdateMessage($arParams["MID"], $USER->GetUserGroupArray(), intVal($USER->GetID()));
elseif ($arParams["MESSAGE_TYPE"] == "NEW" && $arParams["FID"] > 0)
	$bShowForm = CForumTopic::CanUserAddTopic($arParams["FID"], $USER->GetUserGroupArray(), $USER->GetID());

if (!$bShowForm):
	return 0;
endif;
/********************************************************************
				Data
********************************************************************/
if ($arParams["MESSAGE_TYPE"] == "EDIT")
{
	$arMessage = CForumMessage::GetByID($arParams["MID"]);
	if (empty($arMessage)):
		ShowError(GetMessage("F_ERROR_MESSAGE_NOT_FOUND"));
		return 0;
	endif;
	
	$arResult["MESSAGE"] = $arMessage;
	$arResult["MESSAGE"]["FILES"] = array();
	$db_res = CForumFiles::GetList(array(), array("MESSAGE_ID" => $arParams["MID"]));
	if ($db_res && $res = $db_res->Fetch())
	{
		do 
		{
			$arResult["MESSAGE"]["FILES"][$res["FILE_ID"]] = $res;
		} while ($res = $db_res->Fetch());
	}
	
	$arResult["TOPIC"] = CForumTopic::GetByID(intVal($arMessage["TOPIC_ID"]), array("NoFilter" => 'true'));
	$arResult["~TOPIC"] = $arResult["TOPIC"];
}
if ($arParams["bVarsFromForm"] == "Y")
{
	$arResult["MESSAGE"]["AUTHOR_NAME"] = $_REQUEST["AUTHOR_NAME"];
	$arResult["MESSAGE"]["AUTHOR_EMAIL"] = $_REQUEST["AUTHOR_EMAIL"]; 
	$arResult["MESSAGE"]["POST_MESSAGE"] = $_REQUEST["POST_MESSAGE"]; 
	$arResult["MESSAGE"]["USE_SMILES"] = ($_REQUEST["USE_SMILES"] == "Y" ? "Y" : "N"); 
	$arResult["MESSAGE"]["EDITOR_NAME"] = $_REQUEST["EDITOR_NAME"]; 
	$arResult["MESSAGE"]["EDITOR_EMAIL"] = $_REQUEST["EDITOR_EMAIL"]; 
	$arResult["MESSAGE"]["EDIT_REASON"] = $_REQUEST["EDIT_REASON"]; 
	$arResult["TOPIC"]["TITLE"] = $_REQUEST["TITLE"]; 
	$arResult["TOPIC"]["TAGS"] = $_REQUEST["TAGS"]; 
	$arResult["TOPIC"]["DESCRIPTION"] = $_REQUEST["DESCRIPTION"]; 
	$arResult["TOPIC"]["ICON_ID"] = $_REQUEST["ICON_ID"]; 
	foreach ($_REQUEST["FILES"] as $key => $val):
		if (intVal($val) <= 0)
			return false;
		$arResult["MESSAGE"]["FILES"][$val] = $val;
	endforeach;
}
/*******************************************************************/
if (($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="REPLY") && $arResult["IsAuthorized"] == "N" || 
	$arParams["MESSAGE_TYPE"]=="EDIT" && intVal($arResult["MESSAGE"]["AUTHOR_ID"]) <= 0)
{
	$arResult["SHOW_PANEL_GUEST"] = "Y";
}
	
if ($arParams["MESSAGE_TYPE"]=="NEW" || $arParams["MESSAGE_TYPE"]=="EDIT" && 
	CForumTopic::CanUserUpdateTopic($arParams["TID"], $USER->GetUserGroupArray(), $USER->GetID()))
{
	$arResult["SHOW_PANEL_NEW_TOPIC"] = "Y";
	$arResult["ForumPrintIconsList"] = ForumPrintIconsList(7, "ICON_ID", $arResult["TOPIC"]["ICON_ID"], GetMessage("FPF_NO_ICON"), 
		LANGUAGE_ID, $arParams["PATH_TO_ICON"], $arParams["CACHE_TIME"]);
}

if ($arParams["FORUM"]["ALLOW_SMILES"]=="Y")
{
	$arResult["ForumPrintSmilesList"] = ForumPrintSmilesList($arParams["SMILE_TABLE_COLS"], LANGUAGE_ID, 
		$arParams["PATH_TO_SMILE"], $arParams["CACHE_TIME"]);
	$arResult["SMILES"] = CForumSmile::GetByType("S", LANGUAGE_ID);
}

if ($arResult["SHOW_SUBSCRIBE"] == "Y")
{
	$arFields = array("USER_ID" => $USER->GetID(), "FORUM_ID" => $arParams["FID"], "SITE_ID" => SITE_ID);
	$db_res = CForumSubscribe::GetList(array(), $arFields);
	$arResult["TOPIC_SUBSCRIBE"] = "N";
	$arResult["FORUM_SUBSCRIBE"] = "N";
	if ($db_res)
	{
		while ($res = $db_res->Fetch())
		{
			if (intVal($res["TOPIC_ID"]) <= 0): 
				$arResult["FORUM_SUBSCRIBE"] = "Y";
			elseif($res["TOPIC_ID"] == $arParams["TID"]): 
				$arResult["TOPIC_SUBSCRIBE"] = "Y";
			endif;
		}
	}
}

if ($arResult["SHOW_PANEL_ATTACH_IMG"] == "Y")
{
	foreach ($arResult["MESSAGE"]["FILES"] as $key => $val):
		if (intVal($val) <= 0)
			return false;
		$arResult["MESSAGE"]["FILES"][$key] = CFile::GetFileArray($key);
	endforeach;
/************** For custom component *******************************/
	$arResult["MESSAGE"]["ATTACH_IMG_FILE"] = false;
	if (strlen($arResult["MESSAGE"]["ATTACH_IMG"]) > 0)
	{
		$arResult["MESSAGE"]["ATTACH_IMG_FILE"] = $arResult["MESSAGE"]["FILES"][$arResult["MESSAGE"]["ATTACH_IMG"]];
		if ($arResult["MESSAGE"]["ATTACH_IMG_FILE"])
			$arResult["MESSAGE"]["ATTACH_IMG"] = CFile::ShowImage($arResult["MESSAGE"]["ATTACH_IMG_FILE"]["SRC"], 200, 200, "border=0");
	}
/************** For custom component/*******************************/
}

$arResult["MESSAGE"]["CAPTCHA_CODE"] = "";
if (!$USER->IsAuthorized() && $arParams["FORUM"]["USE_CAPTCHA"]=="Y")
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
	$cpt = new CCaptcha();
	$captchaPass = COption::GetOptionString("main", "captcha_password", "");
	if (strlen($captchaPass) <= 0)
	{
		$captchaPass = randString(10);
		COption::SetOptionString("main", "captcha_password", $captchaPass);
	}
	$cpt->SetCodeCrypt($captchaPass);
	$arResult["CAPTCHA_CODE"] = htmlspecialchars($cpt->GetCodeCrypt());
}
/*******************************************************************/
$arResult["SUBMIT"] = GetMessage("FPF_EDIT");
$arResult["str_HEADER"] = GetMessage("FPF_EDIT_FORM");
if ($arParams["MESSAGE_TYPE"]=="NEW"):
	$arResult["SUBMIT"] = GetMessage("FPF_SEND");
	$arResult["str_HEADER"] = GetMessage("FPF_CREATE_IN_FORUM")." ".$arParams["FORUM"]["NAME"];
elseif ($arParams["MESSAGE_TYPE"]=="REPLY"):
	$arResult["SUBMIT"] = GetMessage("FPF_REPLY");
	$arResult["str_HEADER"] = GetMessage("FPF_REPLY_FORM");
endif;
/************** For custom component *******************************/
foreach ($arResult["MESSAGE"] as $key => $val):
	$arResult["MESSAGE"][$key] = htmlspecialcharsEx($val);
	$arResult["MESSAGE"]["~".$key] = $val;
	$arResult["str_".$key] = htmlspecialcharsEx($val);
	$arResult["~str_".$key] = $val;
endforeach;
foreach ($arResult["TOPIC"] as $key => $val):
	$arResult["TOPIC"][$key] = htmlspecialcharsEx($val);
	$arResult["TOPIC"]["~".$key] = $val;
	$arResult["str_".$key] = htmlspecialchars($val);
	$arResult["~str_".$key] = $val;
endforeach;

$arResult["list"] = $arResult["URL"]["LIST"];
$arResult["read"] = $arResult["URL"]["READ"];
$arResult["UserPermission"] = $arResult["PERMISSION"];
$arResult["FID"] = $arParams["FID"];
$arResult["TID"] = $arParams["TID"];
$arResult["MID"] = $arParams["MID"];
$arResult["FORUM"] = $arParams["FORUM"];
$arResult["MESSAGE_TYPE"] = $arParams["MESSAGE_TYPE"];
$arResult["PAGE_NAME"] = $arParams["PAGE_NAME"];
$arResult["LANGUAGE_ID"] = LANGUAGE_ID;
$arResult["VIEW"] = ($arParams["VIEW"] != "Y" ? "N" : "Y");
$arResult["SHOW_CLOSE_ALL"] = "N";
if ($arResult["FORUM"]["ALLOW_BIU"] == "Y" || $arResult["FORUM"]["ALLOW_FONT"] == "Y" || $arResult["FORUM"]["ALLOW_ANCHOR"] == "Y" || $arResult["FORUM"]["ALLOW_IMG"] == "Y" || $arResult["FORUM"]["ALLOW_QUOTE"] == "Y" || $arResult["FORUM"]["ALLOW_CODE"] == "Y" || $arResult["FORUM"]["ALLOW_LIST"] == "Y")
	$arResult["SHOW_CLOSE_ALL"] = "Y";
$arResult["sessid"] = bitrix_sessid_post();
/********************************************************************
				Data
********************************************************************/
	$this->IncludeComponentTemplate();
?>