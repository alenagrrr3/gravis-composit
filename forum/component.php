<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!IsModuleInstalled("forum")): 
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
/***************** URL *********************************************/
/***************** TAGS ********************************************/
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default params
********************************************************************/
$componentPage = "index";
$arResult = array();
$arDefaultUrlTemplates404 = array(
	"active" => "topic/new/",
	"forums" => "group#GID#/",
	"help" => "help/",
	"index" => "index.php",
	"list" => "forum#FID#/",
	"message" => "messages/forum#FID#/topic#TID#/message#MID#/",
	"message_small" => "forum#FID#/topic#TID#/message#MID#/",
	"message_appr" => "messages/approve/forum#FID#/topic#TID#/",
	"message_move" => "messages/move/forum#FID#/topic#TID#/message#MID#/",
	"message_send" => "user/#UID#/send/#TYPE#/",
	"pm_list" => "pm/folder#FID#/",
	"pm_edit" => "pm/folder#FID#/message#MID#/user#UID#/#mode#/",
	"pm_read" => "pm/folder#FID#/message#MID#/",
	"pm_search" => "pm/search/",
	"pm_folder" => "pm/folders/",
	"profile" => "user/#UID#/edit/",
	"profile_view" => "user/#UID#/",
	"read" => "forum#FID#/topic#TID#/",
	"rules" => "rules.php",
	"rss" => "rss/#TYPE#/#MODE#/#IID#/",
	"search" => "search/",
	"subscr_list" => "subscribe/",
	"topic_move" => "topic/move/forum#FID#/topic#TID#/",
	"topic_new" => "topic/add/forum#FID#/",
	"topic_search" => "topic/search/",
	"user_list" => "users/",
	"user_post" => "user/#UID#/post/#mode#/",
);
$arDefaultVariableAliasesForPages = Array(
	"active" => array("PAGE_NAME" => "PAGE_NAME"),
	"forums" => array("PAGE_NAME" => "PAGE_NAME", "GID" => "GID"),
	"help" => array("PAGE_NAME" => "PAGE_NAME"),
	"index" => array("PAGE_NAME" => "PAGE_NAME"),
	"list" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"message" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "MID" => "MID"),
	"message_small" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "MID" => "MID"),
	"message_appr" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID"),
	"message_move" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID", "MID" => "MID"),
	"message_send" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID", "TYPE" => "TYPE"), 
	"pm_list" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"pm_edit" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "MID" => "MID", "UID" => "UID", "mode" => "mode"),
	"pm_read" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "MID" => "MID"),
	"pm_search" => array("PAGE_NAME" => "PAGE_NAME"),
	"pm_folder" => array("PAGE_NAME" => "PAGE_NAME"),
	"profile" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID"),
	"profile_view" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID"),
	"read" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID"),
	"rules" => array("PAGE_NAME" => "PAGE_NAME"),
	"rss" => array("PAGE_NAME" => "PAGE_NAME", "IDD" => "IID", "TYPE" => "TYPE", "MODE" => "MODE"),
	"search" => array("PAGE_NAME" => "PAGE_NAME"),
	"subscr_list" => array("PAGE_NAME" => "PAGE_NAME"),
	"topic_move" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID", "TID" => "TID"),
	"topic_new" => array("PAGE_NAME" => "PAGE_NAME", "FID" => "FID"),
	"topic_search" => array("PAGE_NAME" => "PAGE_NAME"),
	"user_list" => array("PAGE_NAME" => "PAGE_NAME"),
	"user_post" => array("PAGE_NAME" => "PAGE_NAME", "UID" => "UID", "mode" => "mode")
);
$arDefaultVariableAliases404 = Array();
$arDefaultVariableAliases = Array(
	"ACTION" => "ACTION", 
	"COUNT" => "COUNT", 
	"FID" => "FID", 
	"FORUM_RANGE" => "FORUM_RANGE", 
	"GID" => "GID", // Group forums ID
	"IDD" => "IID", 
	"MID" => "MID", 
	"MODE" => "MODE", 
	"PAGE_NAME" => "PAGE_NAME", 
	"TID" => "TID", 
	"TYPE" => "TYPE", 
	"UID" => "UID");
$arComponentVariables = Array(
	"ACTION", 
	"COUNT", 
	"FID", 
	"FORUM_RANGE", 
	"GID", 
	"IID", 
	"MID", 
	"mode", 
	"MODE", 
	"PAGE_NAME", 
	"TID", 
	"TYPE", 
	"UID");
$arVariables = array();
/********************************************************************
				Default params
********************************************************************/

$arAuthPageParams = array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth");
if (($_REQUEST["auth"]=="yes" || $_REQUEST["register"] == "yes" ||  $_REQUEST["login"] == "yes") && 
	$USER->IsAuthorized() || $_REQUEST["logout"] == "yes")
{
	LocalRedirect($APPLICATION->GetCurPageParam("", $arAuthPageParams));
}
else
{
	foreach ($arAuthPageParams as $key):
		if (is_set($_REQUEST, $key)):
			$this->IncludeComponentTemplate("auth");
			return false;
		endif;
	endforeach;
}

/********************************************************************
				Data
********************************************************************/
if ($arParams["SEF_MODE"] == "Y")
{
	if (!function_exists("CheckPathParams")):
		function CheckPathParams($url, $params, $Aliases)
		{
			$params = (is_array($params) ? $params : array());
			foreach ($params as $key => $val):
				if ($val == "PAGE_NAME")
					continue;
				$val = (!empty($Aliases[$val]) ? $Aliases[$val] : $val);
				if (strpos($url, "#".$val."#") === false):
					return false;
				endif;
			endforeach;
			return true;
		}
	endif;
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	if ($arParams["CHECK_CORRECT_TEMPLATES"] != "N"):
		foreach ($arUrlTemplates as $url => $value)
		{
			if (!CheckPathParams($arUrlTemplates[$url], $arDefaultVariableAliasesForPages[$url], $arVariableAliases[$url]))
				$arUrlTemplates[$url] = $arDefaultUrlTemplates404[$url];
		}
	endif;
	$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);
	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	foreach ($arUrlTemplates as $url => $value)
	{
		if (empty($arUrlTemplates[$url]))
		{
			$arResult["URL_TEMPLATES_".strToUpper($url)] = $arParams["SEF_FOLDER"].$arDefaultUrlTemplates404[$url];
		}
		elseif (substr($arUrlTemplates[$url], 0, 1) == "/")
			$arResult["URL_TEMPLATES_".strToUpper($url)] = $arUrlTemplates[$url];
		else
			$arResult["URL_TEMPLATES_".strToUpper($url)] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);
	$componentPage = strToLower($arVariables["PAGE_NAME"]);
	foreach ($arDefaultUrlTemplates404 as $key => $value)
	{
		$arResult["URL_TEMPLATES_".strToUpper($url)] = "";
	}
}
$componentPage = (in_array($componentPage, array("message", "message_small")) ? "read" : $componentPage);
$componentPage = (in_array($componentPage, array("forums")) ? "index" : $componentPage);
$arVariables["PAGE_NAME"] = ($componentPage && array_key_exists($componentPage, $arDefaultUrlTemplates404) ? $componentPage : "index");

$arResult = array_merge(
	array(
		"SEF_MODE" => $arParams["SEF_MODE"],
		"SEF_FOLDER" => $arParams["SEF_FOLDER"],
		"URL_TEMPLATES" => $arUrlTemplates, 
		"VARIABLES" => $arVariables, 
		"ALIASES" => $arVariableAliases,
		"PAGE_NAME" => $arVariables["PAGE_NAME"],
		"FID" => ($arVariables["PAGE_NAME"] == "index") ? $arParams["FID"] : $arVariables["FID"],
		"GID" => $arVariables["GID"],
		"TID" => $arVariables["TID"],
		"MID" => $arVariables["MID"],
		"UID" => $arVariables["UID"],
		"IID" => $arVariables["IID"],
		"ACTION" => $arVariables["ACTION"],
		"TYPE" => $arVariables["TYPE"],
		"mode" => $arVariables["mode"],
		"MODE" => $arVariables["MODE"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"SET_NAVIGATION" => $arParams["SET_NAVIGATION"],
		"DATE_FORMAT" => $arParams["DATE_FORMAT"],
		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
		"FORUMS_PER_PAGE" => $arParams["FORUMS_PER_PAGE"],
		"TOPICS_PER_PAGE" => $arParams["TOPICS_PER_PAGE"],
		"MESSAGES_PER_PAGE" => $arParams["MESSAGES_PER_PAGE"],
		"PATH_TO_AUTH_FORM" => $arParams["PATH_TO_AUTH_FORM"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		"PATH_TO_ICON" => $arParams["PATH_TO_ICON"],
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"],
		"SHOW_FORUMS_LIST" => $arParams["SHOW_FORUMS_LIST"],
		"HELP_CONTENT" => $arParams["HELP_CONTENT"],
		"RULES_CONTENT" => $arParams["RULES_CONTENT"],
		),
	$arResult);
// BASE 
//$arParams["FID"]
//$arParams["TID"] - topic id
//$arParams["MID"] - message id || message id (pm)
//$arParams["UID"] - user id
//$arParams["HELP_CONTENT"]
//$arParams["RULES_CONTENT"]
$arParams["TIME_INTERVAL_FOR_USER_STAT"] = intVal($arParams["TIME_INTERVAL_FOR_USER_STAT"]/60);
$arParams["USE_DESC_PAGE_TOPIC"] = ($arParams["USE_DESC_PAGE_TOPIC"] == "N" ? "N" : "Y");
$arParams["RSS_FID_RANGE"] = (!is_array($arParams["RSS_FID_RANGE"]) ? array() : $arParams["RSS_FID_RANGE"]);
$arParams["RSS_FID_RANGE"] = (empty($arParams["RSS_FID_RANGE"]) && !empty($arParams["FID"]) ? $arParams["FID"] : array());
//
// URL
//$arParams["SEF_MODE"]
//$arParams["SEF_FOLDER"]

// ADDITIONAL
// Serch page 
//$arParams["CHECK_DATES"]
//$arParams["TAGS_SORT"]
//$arParams["TAGS_INHERIT"]
//$arParams["FONT_MAX"]
//$arParams["FONT_MIN"]
//$arParams["COLOR_NEW"]
//$arParams["COLOR_OLD"]
//$arParams["PERIOD_NEW_TAGS"]
//$arParams["SHOW_CHAIN"]
//$arParams["COLOR_TYPE"]
//$arParams["WIDTH"]
//$arParams["RESTART"]




//$arParams["DATE_FORMAT"],
//$arParams["DATE_TIME_FORMAT"],
//$arParams["FORUMS_PER_PAGE"],
//$arParams["TOPICS_PER_PAGE"],
//$arParams["MESSAGES_PER_PAGE"],
$arParams["FILES_COUNT"] = intVal(intVal($arParams["FILES_COUNT"]) > 0 ? $arParams["FILES_COUNT"] : 2);
$arParams["IMAGE_SIZE"] = intVal(intVal($arParams["IMAGE_SIZE"]) > 0 ? $arParams["IMAGE_SIZE"] : 500);
//$arParams["PATH_TO_SMILE"]
//$arParams["PATH_TO_ICON"]
//$arParams["PATH_TO_AUTH_FORM"]


//$arParams["USER_PROPERTY"] - user property
//$arParams["SHOW_FORUM_ANOTHER_SITE"]
//$arParams["SHOW_FORUMS_LIST"]
$arParams["SHOW_TAGS"] = (is_set($arParams["SHOW_TAGS"]) ? $arParams["SHOW_TAGS"] : "Y");


$arParams["SEND_MAIL"] = (in_array($arParams["SEND_MAIL"], array("A", "E", "U", "Y")) ? $arParams["SEND_MAIL"] : "E");
$arParams["SEND_ICQ"] = (in_array($arParams["SEND_ICQ"], array("A", "E", "U", "Y")) ? $arParams["SEND_ICQ"] : "A");

//$arParams["SHOW_FORUM_ANOTHER_SITE"]

//$arParams["SHOW_FORUMS_LIST"]
//$arParams["SHOW_USER_STATUS"]
//$arParams["FORUMS_ANOTHER"]

$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y"); // add items into chain item
$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); // add buttons unto top panel
$arParams["CACHE_TIME_USER_STAT"] = (intVal($arParams["CACHE_TIME_USER_STAT"]) > 0 ? $arParams["CACHE_TIME_USER_STAT"] : 360);

$arParams["USE_RSS"] = ($arParams["USE_RSS"] == "N" ? "N" : "Y"); 
$arParams["AJAX_MODE"] = ($arParams["AJAX_MODE"] == "Y" ? "Y" : "N"); 
$arParams["AJAX_TYPE"] = (($arParams["AJAX_TYPE"] == "Y" && $arParams["AJAX_MODE"] == "N") ? "Y" : "N"); 
// CACHE & TITLE
//$arParams["CACHE_TIME"]
//$arParams["CACHE_TYPE"]
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");

$arParams["SHOW_ADD_MENU"] = ($arParams["TMPLT_SHOW_BOTTOM"] == "SET_BE_READ" ? "N" : "Y");
if (!$GLOBALS["USER"]->IsAuthorized() && COption::GetOptionString("forum", "USE_COOKIE", "N") == "N")
{
	$arParams["SHOW_ADD_MENU"] = "N";
	$arParams["TMPLT_SHOW_BOTTOM"] = "";
}

$this->IncludeComponentTemplate($arVariables["PAGE_NAME"]);
?>