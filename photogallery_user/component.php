<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
endif;
/********************************************************************
				Get data from cache
********************************************************************/
$cache = new CPHPCache;
/********************************************************************
				PERMISSION
********************************************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
$cache_id = serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"USER_GROUPS" => $GLOBALS["USER"]->GetGroups()));
$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/permission/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arParams["PERMISSION"] = $res["PERMISSION"];
}
if (empty($arParams["PERMISSION"]))
{
	CModule::IncludeModule("iblock");
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(array("PERMISSION" => $arParams["PERMISSION"]));
	}
}
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;
/********************************************************************
				/Get data from cache
********************************************************************/
$arDefaultUrlTemplates404 = array(
	"index" => "",
	
	"galleries" => "galleries/#USER_ID#/",
	"gallery" => "#USER_ALIAS#/",
	"gallery_edit" => "#USER_ALIAS#/action/#ACTION#/",
	
	"section" => "#USER_ALIAS#/#SECTION_ID#/",
	"section_edit" => "#USER_ALIAS#/#SECTION_ID#/action/#ACTION#/",
	"section_edit_icon" => "#USER_ALIAS#/#SECTION_ID#/icon/action/#ACTION#/",
	
	"upload" => "#USER_ALIAS#/#SECTION_ID#/action/upload/",
	"detail" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
	"detail_edit" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
	"detail_slide_show" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
	"detail_list" => "list/",
	
//	"user" => "user/#USER_ID#/",
	"search" => "search/",
	"tags" => "tags/",
	"auth" => "auth");

$arDefaultVariableAliases404 = Array(
	"index" => array("PAGE_NAME" => "PAGE_NAME"),
	
	"galleries" => array("PAGE_NAME" => "PAGE_NAME", "USER_ID" => "USER_ID"),
	"gallery" => array("USER_ALIAS" => "USER_ALIAS"),
	"gallery_edit" => array("USER_ALIAS" => "USER_ALIAS", "ACTION" => "ACTION"),
	
	"section" => array("USER_ALIAS" => "USER_ALIAS", "SECTION_ID" => "SECTION_ID", "PAGE_NAME" => "PAGE_NAME"),
	"section_edit" => array("USER_ALIAS" => "USER_ALIAS", "SECTION_ID" => "SECTION_ID", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"section_edit_icon" => array("USER_ALIAS" => "USER_ALIAS", "SECTION_ID" => "SECTION_ID", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	
	"upload"=>array("USER_ALIAS" => "USER_ALIAS", "SECTION_ID" => "SECTION_ID", "PAGE_NAME" => "PAGE_NAME"),
	"detail"=>array("USER_ALIAS" => "USER_ALIAS", "ELEMENT_ID"=>"ELEMENT_ID", "PAGE_NAME" => "PAGE_NAME"),
	"detail_edit"=>array("USER_ALIAS" => "USER_ALIAS", "ELEMENT_ID"=>"ELEMENT_ID", "ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME"),
	"detail_slide_show"=>array("USER_ALIAS" => "USER_ALIAS", "SECTION_ID" => "SECTION_ID", "ELEMENT_ID"=>"ELEMENT_ID", "PAGE_NAME" => "PAGE_NAME"),
	"detail_list"=>array("PAGE_NAME" => "PAGE_NAME"),
	
//	"user" => array("USER_ID" => "USER_ID", "PAGE_NAME" => "PAGE_NAME"),
	"search" => array("PAGE_NAME" => "PAGE_NAME"),
	"tags" => array("PAGE_NAME" => "PAGE_NAME"));

$arComponentVariables = Array(
	"SECTION_ID", "ELEMENT_ID",
	"ACTION", "PAGE_NAME", 
	"USER_ID", "USER_ALIAS");

$arDefaultVariableAliases = Array(
	"SECTION_ID" => "SECTION_ID", "ELEMENT_ID" => "ELEMENT_ID",
	"ACTION" => "ACTION", "PAGE_NAME" => "PAGE_NAME", 
	"USER_ALIAS" => "USER_ALIAS", "USER_ID" => "USER_ID");

if ((($_REQUEST["auth"]=="yes") || ($_REQUEST["register"] == "yes")) && $USER->IsAuthorized())
	LocalRedirect($APPLICATION->GetCurPageParam("", array("login", "logout", "register", "forgot_password", "change_password", "backurl", "auth")));

if($arParams["SEF_MODE"] == "Y")
{
	$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);
	$componentPage = CComponentEngine::ParseComponentPath(
		$arParams["SEF_FOLDER"],
		$arUrlTemplates,
		$arVariables
	);
	if (empty($componentPage))
		$componentPage = "index";
	elseif ($arVariables["ACTION"] == "upload")
		$componentPage = "upload";

	CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);
	$arResult = array(
			"~URL_TEMPLATES" => $arUrlTemplates,
			"VARIABLES" => $arVariables,
			"ALIASES" => $arVariableAliases);
	
	foreach ($arDefaultUrlTemplates404 as $url => $value)
	{
		if (empty($arUrlTemplates[$url]))
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arDefaultUrlTemplates404[$url];
		elseif (substr($arUrlTemplates[$url], 0, 1) == "/")
			$arResult["URL_TEMPLATES"][$url] = $arUrlTemplates[$url];
		else
			$arResult["URL_TEMPLATES"][$url] = $arParams["SEF_FOLDER"].$arUrlTemplates[$url];
	}
}
else
{
	$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
	CComponentEngine::InitComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

	$componentPage = "";
	if (!empty($arVariables["PAGE_NAME"]))
		$componentPage = $arVariables["PAGE_NAME"];
	else 
		$componentPage = "index";
}
if (!in_array($componentPage, array_keys($arDefaultUrlTemplates404)))
	$componentPage = "index";
elseif (($_REQUEST["auth"]=="yes") || ($_REQUEST["register"] == "yes"))
	$componentPage = "auth";

/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
//$arParams["IBLOCK_TYPE"]
//$arParams["IBLOCK_ID"]
//$arParams["SECTION_ID"]
//$arParams["SECTION_CODE"]
//$arParams["ELEMENT_ID"]
//$arParams["ELEMENT_CODE"]
//$arParams["USER_ALIAS"]
//$arParams["BEHAVIOUR"]
//$arParams["GALLERY_ID"]
//$arParams["USER_ID"]

$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y"); // only one gallery for user
//$arParams["GALLERY_GROUPS"] - user groups who can create gallery
$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"]); // size gallery in Mb
$arParams["GALLERY_SIZE"] = 0;


//$arParams["ACTION"]
//$arParams["AJAX_CALL"]
// Page
//$arParams["ELEMENTS_USE_DESC_PAGE"] => $arParams["USE_DESC_PAGE"]
//$arParams["SECTION_PAGE_ELEMENTS"] => $arParams["PAGE_ELEMENTS"]
//$arParams["ELEMENTS_PAGE_ELEMENTS"] => $arParams["PAGE_ELEMENTS"]
//$arParams["PAGE_NAVIGATION_TEMPLATE"]

//$arParams["SECTION_SORT_BY"] => $arParams["SORT_BY"]
//$arParams["SECTION_SORT_ORD"] => $arParams["SORT_ORD"]
//$arParams["ELEMENT_SORT_FIELD"]
//$arParams["ELEMENT_SORT_ORDER"]
//$arParams["ELEMENT_SORT_FIELD1"]
//$arParams["ELEMENT_SORT_ORDER1"]

//$arParams["ELEMENTS_LAST_COUNT"]
//$arParams["ELEMENT_LAST_TIME"]
//$arParams["ELEMENT_FILTER"]
//$arParams["ELEMENTS_LAST_TYPE"]
//$arParams["ELEMENTS_LAST_TIME"]
//$arParams["ELEMENTS_LAST_TIME_FROM"]
//$arParams["ELEMENTS_LAST_TIME_TO"]
//$arParams["ELEMENT_LAST_TYPE"]

/****************** URL ********************************************/
//$arParams["GALLERIES_URL"]
//$arParams["GALLERY_URL"]
//$arParams["INDEX_URL"]
//$arParams["GALLERY_EDIT_URL"]
//$arParams["SECTION_URL"]
//$arParams["SECTIONS_TOP_URL"]
/****************** ADDITIONAL *************************************/
// Permissions
$arParams["ANALIZE_SOCNET_PERMISSION"] = ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y" ? "Y" : "N");
$arParams["USE_PERMISSIONS"] = "N";
$arParams["GROUP_PERMISSIONS"] = array();
//$arParams["PERMISSION"] // in component
//$arParams["PASSWORD_CHECKED"] // in component

// Visual
//$arParams["DATE_TIME_FORMAT_DETAIL"] => $arParams["DATE_TIME_FORMAT"]
//$arParams["DATE_TIME_FORMAT_SECTION"] => $arParams["DATE_TIME_FORMAT"]

$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
$arParams["GALLERY_AVATAR_THUMBS_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_THUMBS_SIZE"]) > 0 ? 
	$arParams["GALLERY_AVATAR_THUMBS_SIZE"] : $arParams["GALLERY_AVATAR_SIZE"]);
//$arParams["GALLERY_AVATAR"] // in component
//$arParams["GALLERY_AVATAR_THUMBS"] // in component

//$arParams["THUMBS_SIZE"] // thumbs
//$arParams["PREVIEW_SIZE"] // detail
//$arParams["ALBUM_PHOTO_SIZE"] // album cover
//$arParams["ALBUM_PHOTO_THUMBS_SIZE"] album cover (thumbs)

//$arParams["ADDITIONAL_SIGHTS"]
//$arParams["PICTURES_SIGHT"]
//$arParams["PICTURES_INFO"]
//$arParams["PICTURES"]
//$arParams["SHOW_TAGS"]

// Comments
$arParams["USE_COMMENTS"] = ($arParams["USE_COMMENTS"] == "Y" ? "Y" : "N"); 
$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "forum" || $arParams["COMMENTS_TYPE"] == "blog" ? 
	$arParams["COMMENTS_TYPE"] : "none");
if ($arParams["USE_COMMENTS"] == "Y" && (
	($arParams["COMMENTS_TYPE"] == "forum" && !IsModuleInstalled("forum")) || 
	($arParams["COMMENTS_TYPE"] == "blog" && !IsModuleInstalled("blog"))))
{
	$arParams["USE_COMMENTS"] = "N";
}

//$arParams["BLOG_URL"]
//$arParams["COMMENTS_COUNT"]
//$arParams["PATH_TO_BLOG"]
//$arParams["PATH_TO_USER"]
//$arParams["USE_CAPTCHA"]
//$arParams["PREORDER"]
//$arParams["FORUM_ID"]
//$arParams["PATH_TO_SMILE"]
//$arParams["URL_TEMPLATES_READ"]
//$arParams["SHOW_LINK_TO_FORUM"]

// Rating
//$arParams["USE_RATING"]
//$arParams["MAX_VOTE"]
//$arParams["VOTE_NAMES"]


// Gallery
//$arParams["GET_GALLERY_INFO"] - need info about gallery - use only in photogallery.detail.list
/****************** STANDART ***************************************/
//$arParams["CACHE_TYPE"]
//$arParams["CACHE_TIME"]
//$arParams["DISPLAY_PANEL"]
//$arParams["SET_TITLE"]
// 
/****************** COMPONENTS *************************************/
// Upload
//$arParams["UPLOAD_MAX_FILE"]
//$arParams["UPLOAD_MAX_FILE_SIZE"]
//$arParams["JPEG_QUALITY1"]
//$arParams["JPEG_QUALITY2"]
//$arParams["JPEG_QUALITY"]
//$arParams["WATERMARK"]
//$arParams["WATERMARK_MIN_PICTURE_SIZE"]
//$arParams["WATERMARK_COLORS"]

// Tags cloud
//$arParams["TAGS_PAGE_ELEMENTS"]
//$arParams["TAGS_PERIOD"]
//$arParams["TAGS_INHERIT"]
//$arParams["FONT_MAX"]
//$arParams["FONT_MIN"]
//$arParams["COLOR_NEW"]
//$arParams["COLOR_OLD"]
//$arParams["TAGS_SHOW_CHAIN"]
//$arParams["TEMPLATE_LIST"]
//$arParams["ELEMENTS_PAGE_ELEMENTS"]

/****************** TEMPLATES **************************************/
//$arParams["SHOW_CONTROLS"]
//$arParams["DetailListViewMode"]
//$arParams["SHOW_PAGE_NAVIGATION"]
//$arParams["SHOW_RATING"]
//$arParams["SHOW_SHOWS"]
//$arParams["SHOW_COMMENTS"]
//$arParams["SQUARE"]
//$arParams["PERCENT"]
//$arParams["SLIDER_COUNT_CELL"]
//$arParams["B_ACTIVE_IS_FINED"]
//$arParams["SHOW_DESCRIPTION"]
//$arParams["DETAIL_URL_FOR_JS"]
//$arParams["BACK_URL"]
//$arParams["CELL_COUNT"]
//$arParams["WORD_LENGTH"]
// Main
$arParams["MODERATE"] = ($arParams["MODERATE"] == "Y" ? "Y" : "N");
$arParams["SHOW_ONLY_PUBLIC"] = ($arParams["SHOW_ONLY_PUBLIC"] == "N" ? "N" : "Y");
if ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y")
{
	if (!IsModuleInstalled("socialnetwork"))
	{
		ShowError("module socialnetwork is not installed.");
		return 0;
	}
	$arParams["SHOW_TAGS"] = "N";
	$arParams["SHOW_ONLY_PUBLIC"] = "Y";
	$arParams["GALLERY_GROUPS"] = array(2); 
	$arParams["ONLY_ONE_GALLERY"] = "Y"; 
	$arParams["ELEMENTS_USE_DESC_PAGE"] = "Y";
	$arParams["DATE_TIME_FORMAT_SECTION"] = ""; 
	$arParams["DATE_TIME_FORMAT_DETAIL"] = "";
	$arParams["USE_PERMISSIONS"] = "N";
	$arParams["GROUP_PERMISSIONS"] = array();
	$arParams["ADDITIONAL_SIGHTS"] = array(); 
	$arParams["SHOW_TAGS"] = "N";
	$arParams["SHOW_PHOTO_USER"] = "Y";
	
//	$arParams["ADD_CHAIN_ITEM"] = "N";
	if ($componentPage == "search" || $componentPage == "tags")
	{
		$componentPage = "index";
	}
}
/********************************************************************
				/Input params
********************************************************************/

$arResult = array(
		"~URL_TEMPLATES" =>  $arUrlTemplates,
		"URL_TEMPLATES" => $arResult["URL_TEMPLATES"],
		"VARIABLES" => $arVariables,
		"ALIASES" => $arVariableAliases);

/********************************************************************
				Actions
********************************************************************/
if ($_REQUEST["ACTION"] == "public" && $arParams["PERMISSION"] >= "W" && check_bitrix_sessid() && is_array($_REQUEST["items"]))
{
	CModule::IncludeModule("iblock");
	$bs = new CIBlockElement;
	foreach ($_REQUEST["items"] as $res):
		$ID = $bs->Update($res, array(
			"PROPERTY_VALUES" => array(
				"APPROVE_ELEMENT" => array(
					"n0" => "Y"),
				"PUBLIC_ELEMENT" => array(
					"n0" => "Y"))));
		BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/0/".$res);
	endforeach;
	BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/all/");
	$url = $arParams["DETAIL_LIST_URL"];
	if (empty($url))
	{
		$url = $APPLICATION->GetCurPageParam("PAGE_NAME=detail_list", 
			array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit"));
	}
	$url = CComponentEngine::MakePathFromTemplate($url, array());
	if (strpos($url, "?") === false)
		$url .= "?";
	$url .= "&moderate=Y";
	LocalRedirect($url);
}
/********************************************************************
				/Actions
********************************************************************/
$this->IncludeComponentTemplate($componentPage);

?>