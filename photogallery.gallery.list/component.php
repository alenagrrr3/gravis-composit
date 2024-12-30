<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["USER_ID"] = intVal(intVal($arParams["USER_ID"]) > 0 ? $arParams["USER_ID"] : $_REQUEST["USER_ID"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	
	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"gallery_edit" => "PAGE_NAME=gallery_edit&USER_ALIAS=#USER_ALIAS#&ACTION=#ACTION#",
		"upload" => "PAGE_NAME=upload&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=upload");
	
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, 
			array("PAGE_NAME", "USER_ALIAS", "GALLERY_ID", "ACTION", "AJAX_CALL", "USER_ID", "sessid", "save", "login", "order", "group_by"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
/***************** ADDITIONAL **************************************/
	$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y");
	$arParams["GALLERY_GROUPS"] = (is_array($arParams["GALLERY_GROUPS"]) ? $arParams["GALLERY_GROUPS"] : array());
	$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
/***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
$arResult["USER"] = array();
$arResult["USERS"] = array();
$arResult["GALLERY"] = array();
$arResult["GALLERIES"] = array();
/********************************************************************
				USER Not from cache (!important)
********************************************************************/
if ($arParams["USER_ID"] <= 0):
//	ShowError(GetMessage("P_USER_EMPTY"));
//	return 0;
else:
	$db_res = CUser::GetByID($arParams["USER_ID"]);
	if ($db_res && $res = $db_res->GetNext())
	{
		$arResult["USER"] = $res;
		$arResult["USER"]["SHOW_NAME"] = trim($arResult["USER"]["NAME"]." ".$arResult["USER"]["LAST_NAME"]);
		if (empty($arResult["USER"]["SHOW_NAME"]))
			$arResult["USER"]["SHOW_NAME"] = $arResult["USER"]["LOGIN"];
	}
	
	if (empty($arResult["USER"]))
	{
		ShowError(GetMessage("P_USER_NOT_FOUND"));
		return 0;
	}
endif;
/********************************************************************
				Get data from cache
********************************************************************/
$cache = new CPHPCache;
/********************************************************************
				PERMISSION
********************************************************************/
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
$arParams["ABS_PERMISSION"] = $arParams["PERMISSION"];
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"])):
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
endif;
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;
/********************************************************************
				GALLERY
********************************************************************/
$cache_id = serialize(array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"USER_ID" => $arParams["USER_ID"]));
$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/data/".$arParams["USER_ID"]."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	$arResult["GALLERY"] = $res["MY_GALLERY"];
	$arResult["GALLERIES"] = $res["MY_GALLERIES"];
}
if (!is_array($arResult["GALLERY"]) || empty($arResult["GALLERY"]))
{
	$arResult["MY_GALLERY"] = array();
	$arResult["MY_GALLERIES"] = array();
	CModule::IncludeModule("iblock");
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"SECTION_ID" => 0);
	if ($arParams["USER_ID"] > 0)
		$arFilter["CREATED_BY"] = $arParams["USER_ID"];
	$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), 
		$arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));
	if ($db_res)
	{
		while ($res = $db_res->GetNext())
		{
			if (preg_match("/[^a-z0-9_]/is", $res["~CODE"]))
				$res["CODE"] = "";
			$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], Array("CNT_ALL"=>"Y")));
			if ($arParams["SHOW_PHOTO_USER"] == "Y")
			{
				if (empty($arResult["USERS"][$res["CREATED_BY"]]))
				{
					$db_user = CUser::GetByID($res["CREATED_BY"]);
					$res_user = $db_user->Fetch();
					$arResult["USER"][$res_user["ID"]] = $res_user;
				}
				$res["PICTURE"] = intVal($arResult["USER"][$res["CREATED_BY"]]["PERSONAL_PHOTO"]);
			}
			$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
			if ($arParams["SHOW_PHOTO_USER"] == "Y" && !empty($res["PICTURE"]))
			{
				$image_resize = CFile::ResizeImageGet($res["PICTURE"], 
					array("width" => $arParams["GALLERY_AVATAR_SIZE"], "height" => $arParams["GALLERY_AVATAR_SIZE"]));
				$res["PICTURE"]["SRC"] = $image_resize["src"];
			}

			
			if ($res["UF_DEFAULT"] == "Y")
				$arResult["MY_GALLERY"] = $res;
			$arResult["MY_GALLERIES"][] = $res;
		};
		if (empty($arResult["MY_GALLERY"]))
			$arResult["MY_GALLERY"] = $arResult["MY_GALLERIES"][0];
	}
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(
			array(
				"MY_GALLERY" => $arResult["MY_GALLERY"],
				"MY_GALLERIES" => $arResult["MY_GALLERIES"]));
	}
	$arResult["GALLERY"] = $arResult["MY_GALLERY"];
	$arResult["GALLERIES"] = $arResult["MY_GALLERIES"];
}
/********************************************************************
				/Get data from cache
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
/********************************************************************
				GALLERY
********************************************************************/
if (!is_array($arResult["GALLERIES"]))
	$arResult["GALLERIES"] = array();
$db_res = new CDBResult;
$db_res->InitFromArray($arResult["GALLERIES"]);
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$db_res->NavStart($arParams["PAGE_ELEMENTS"], false);
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("P_GALLERIES"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["NAV_RESULT"] = $db_res;
}
$arResult["GALLERIES"] = array();
if(intVal($db_res->SelectedRowsCount())>0)
{
	while ($res = $db_res->Fetch())
	{
		$res["LINK"] = array(
			"~VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
				array("USER_ALIAS" => $res["~CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
			"~EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
				array("USER_ALIAS" => $res["~CODE"], "ACTION" => "EDIT", 
					"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
			"~DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
				array("USER_ALIAS" => $res["~CODE"], "ACTION" => "DROP", 
					"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])),
			"~UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
				array("USER_ALIAS" => $res["~CODE"], "SECTION_ID" => "0", 
					"USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"])));
		$res["LINK"]["VIEW"] = htmlspecialchars($res["LINK"]["~VIEW"]);
		if (empty($res["CODE"]))
		{
			$res["LINK"]["~EDIT"] .= (strpos($res["LINK"]["~EDIT"], "?") === false ? "?" : "&")."GALLERY_ID=".$res["ID"];
			$res["LINK"]["~DROP"] .= (strpos($res["LINK"]["~DROP"], "?") === false ? "?" : "&")."GALLERY_ID=".$res["ID"];
		}
		$res["LINK"]["EDIT"] = htmlSpecialChars($res["LINK"]["~EDIT"]);
		$res["LINK"]["~DROP"] .= (strpos($res["LINK"]["~DROP"], "?") === false ? "?" : "&").bitrix_sessid_get();
		$res["LINK"]["DROP"] = htmlSpecialChars($res["LINK"]["~DROP"]);
		$res["LINK"]["UPLOAD"] = htmlSpecialChars($res["LINK"]["~UPLOAD"]);
		$arResult["GALLERIES"][] = $res;
	}
}
/********************************************************************
				PERMISSION
********************************************************************/
$arResult["I"]["ABS_PERMISSION"] = $arParams["ABS_PERMISSION"];
$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];
if ($arResult["I"]["ABS_PERMISSION"] < "W" && $GLOBALS["USER"]->IsAuthorized())
{
	$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";
	$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
	foreach($arParams["GALLERY_GROUPS"] as $PERM)
	{
		if(in_array($PERM, $arUserGroupArray))
		{
			$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "Y";
			break;
		}
	}
	if ($arParams["ONLY_ONE_GALLERY"] == "Y" && !empty($arResult["GALLERIES"]))
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";
	if ($arParams["USER_ID"] == $GLOBALS["USER"]->GetId())
	{
		$arParams["PERMISSION"] = "W";
		$arResult["I"]["ACTIONS"]["EDIT_GALLERY"] = "Y";
		$arResult["I"]["ACTIONS"]["UPLOAD"] = "Y";
	}
}
else
{
	$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "Y";
	$arResult["I"]["ACTIONS"]["EDIT_GALLERY"] = "Y";
	$arResult["I"]["ACTIONS"]["UPLOAD"] = "Y";
}
$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];
/********************************************************************
				/Prepare Data
********************************************************************/
$arResult["LINK"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array()),
	"NEW" => CComponentEngine::MakePathFromTemplate($arParams["GALLERY_EDIT_URL"], array("USER_ALIAS" => "NEW_ALIAS", "ACTION" => "CREATE", 
		"USER_ID" => $USER->GetID(), "GROUP_ID" => 0)),
	"GALLERIES" =>  CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => $arParams["USER_ID"])));
$this->IncludeComponentTemplate();
if ($arParams["SET_TITLE"] == "Y")
	$GLOBALS['APPLICATION']->SetTitle(GetMessage("P_GALLERIES"));
if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
return $arResult["SECTION"]["ID"];
?>