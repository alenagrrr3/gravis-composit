<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return false;
elseif (!IsModuleInstalled("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return false;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["ELEMENT_ID"] = intVal($arParams["ELEMENT_ID"]);
	$arParams["ANALIZE_SOCNET_PERMISSION"] = ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y" ? "Y" : "N");
	
	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"gallery_edit" => "PAGE_NAME=gallery_edit&USER_ALIAS=#USER_ALIAS#&ACTION=#ACTION#",
		"upload" => "PAGE_NAME=upload&USER_ALIAS=#USER_ALIAS#&SECTION_ID=#SECTION_ID#&ACTION=upload");
	
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, array("PAGE_NAME", 
			"USER_ALIAS", "SECTION_ID", "ACTION", "AJAX_CALL", "USER_ID", "sessid", "save", "login", "edit", "action"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
/***************** ADDITIONAL **************************************/
	$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y");
	$arParams["GALLERY_GROUPS"] = (is_array($arParams["GALLERY_GROUPS"]) ? $arParams["GALLERY_GROUPS"] : array());
	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"])*1024*1024;
	$arParams["RETURN_ARRAY"] = ($arParams["RETURN_ARRAY"] == "Y" ? "Y" : "N");// hidden params for custom components
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
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"]=="Y"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
$arResult["GALLERY"] = array();
$arResult["MY_GALLERY"] = array();
$arResult["MY_GALLERIES"] = array();
$arParams["PERMISSION"] = "";
$arResult["USERS"] = array();
$arParams["ABS_PERMISSION"] = "";
$arResult["I"] = array(
	"ABS_PERMITTION" => "",
	"PERMISSION" => "",
	"ACTION" => array(
		"CREATE_GALLERY" => "N",
		"EDIT_GALLERY" => "N"));
/********************************************************************
				Get data from cache
********************************************************************/
$cache = new CPHPCache;
/********************************************************************
				MY GALLERY
********************************************************************/
$arParams["CACHE_TIME"] = 0;
if ($GLOBALS["USER"]->IsAuthorized())
{
	$cache_id = serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ID" => $GLOBALS["USER"]->GetId()));
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/data/".$GLOBALS["USER"]->GetId()."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["MY_GALLERY"] = $res["MY_GALLERY"];
		$arResult["MY_GALLERIES"] = $res["MY_GALLERIES"];
	}
	if (!is_array($arResult["MY_GALLERY"]) || empty($arResult["MY_GALLERY"]))
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"CREATED_BY" => $GLOBALS["USER"]->GetId(),
			"SOCNET_GROUP_ID" => false,
			"SECTION_ID" => 0);
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
			}
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
	}
}
/********************************************************************
				GALLERY
********************************************************************/
if ($arParams["ELEMENT_ID"] > 0)
{
	CModule::IncludeModule("iblock");
	$db_res = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "ID" => $arParams["ELEMENT_ID"]), false, false, array("IBLOCK_SECTION_ID"));
	if ($db_res && $res = $db_res->Fetch())
		$arParams["SECTION_ID"] = $res["IBLOCK_SECTION_ID"];
}
if (!empty($arParams["USER_ALIAS"]) || $arParams["SECTION_ID"] > 0)
{
	$cache_id = serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $arParams["USER_ALIAS"],
		"SECTION_ID" => $arParams["SECTION_ID"]));
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$arParams["USER_ALIAS"]."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["GALLERY"] = $res["GALLERY"];
	}
	if ((!is_array($arResult["GALLERY"]) || empty($arResult["GALLERY"])) && !empty($arResult["MY_GALLERIES"])) 
	{
		foreach ($arResult["MY_GALLERIES"] as $key => $res)
		{
			if ($res["~CODE"] == $arParams["USER_ALIAS"])
			{
				$arResult["GALLERY"] = $res;
				break;
			}
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("GALLERY" => $arResult["GALLERY"]));
		}
	}
	
	if (!is_array($arResult["GALLERY"]) || empty($arResult["GALLERY"]))
	{
		CModule::IncludeModule("iblock");
		if (empty($arParams["USER_ALIAS"]) || $arParams["USER_ALIAS"] == "empty")
		{
			$arSection = array();
			$db_res = CIBlockSection::GetList(array(), array(
				"ID" => $arParams["SECTION_ID"]));
			if ($db_res && $res = $db_res->Fetch())
				$arSection = $res;
			$arFilter = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_ACTIVE" => "Y",
				"SECTION_ID" => 0,
				"!LEFT_MARGIN" => $arSection["LEFT_MARGIN"],
				"!RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"], 
				"!ID" => $arParams["SECTION_ID"]);
		}
		else
		{
			$arFilter = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_ACTIVE" => "Y",
				"SECTION_ID" => 0,
				"CODE" => $arParams["USER_ALIAS"]);
		}
		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), 
			$arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));
		if ($db_res && $res = $db_res->GetNext())
		{
			$res["ELEMENTS_CNT"] = intVal(CIBlockSection::GetSectionElementsCount($res["ID"], array("CNT_ALL"=>"Y")));
			
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
			$arResult["GALLERY"] = $res;
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("GALLERY" => $arResult["GALLERY"]));
		}
	}
}

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
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W")
{
	if (!empty($arParams["PERMISSION_EXTERNAL"]))
	{
		$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
	}
	elseif ($arParams["ANALIZE_SOCNET_PERMISSION"] == "Y" && !empty($arResult["GALLERY"]) && CModule::IncludeModule("socialnetwork"))
	{
		if (intVal($arResult["GALLERY"]["SOCNET_GROUP_ID"]) > 0)
		{
			if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, $arResult["GALLERY"]["SOCNET_GROUP_ID"], "photo", "write"))
				$arParams["PERMISSION"]	= "W";
			elseif (!CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_GROUP, 
					$arResult["GALLERY"]["SOCNET_GROUP_ID"], "photo", "view"))
				$arParams["PERMISSION"]	= "D";
		}
		else 
		{
			if (CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, $arResult["GALLERY"]["CREATED_BY"], "photo", "write"))
				$arParams["PERMISSION"]	= "W";
			elseif (!CSocNetFeaturesPerms::CanPerformOperation($USER->GetID(), SONET_ENTITY_USER, 
					$arResult["GALLERY"]["CREATED_BY"], "photo", "view"))
				$arParams["PERMISSION"]	= "D";
		}
	}
}

$bNeedReturn = false;
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	$bNeedReturn = true;
elseif (!empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ACTIVE"] != "Y"):
	ShowError(GetMessage("P_GALLERY_IS_BLOCKED"));
	if ($arParams["ABS_PERMISSION"] < "W")
		$bNeedReturn = true;
endif;

if ($bNeedReturn == true && !empty($arResult["GALLERY"]))
{
	$APPLICATION->AddChainItem($arResult["GALLERY"]["NAME"], "");
	if ($arParams["SET_TITLE"] == "Y")
		$GLOBALS["APPLICATION"]->SetTitle($arResult["GALLERY"]["NAME"]);
	return false;
}


/********************************************************************
				/Get data from cache
********************************************************************/
/********************************************************************
				Action
********************************************************************/
if ($_REQUEST["action"] == "recalc")
{
	include_once("recalc.php");
}
if ($arParams["GALLERY_SIZE"] > 0 && !empty($arResult["GALLERY"]))
{
	$arResult["GALLERY"]["UF_GALLERY_SIZE_PERCENT"] = intVal(doubleVal($arResult["GALLERY"]["UF_GALLERY_SIZE"])/$arParams["GALLERY_SIZE"]*100);
	$arResult["GALLERY"]["RECALC_INFO"] = @unserialize($arResult["GALLERY"]["~UF_GALLERY_RECALC"]);
	if (empty($arResult["GALLERY"]["RECALC_INFO"]) || !is_array($arResult["GALLERY"]["RECALC_INFO"]))
	{
		$arResult["GALLERY"]["RECALC_INFO"] = array(
			"STATUS" => "BEGIN");
	}
}
/********************************************************************
				/Action
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
/********************************************************************
				MY GALLERY
********************************************************************/
if (!empty($arResult["MY_GALLERY"])):
	$arResult["MY_GALLERY"]["LINK"] = array(
		"~VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
			array("USER_ALIAS" => $arResult["MY_GALLERY"]["~CODE"], 
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
		"~EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
			array("USER_ALIAS" => $arResult["MY_GALLERY"]["~CODE"], "ACTION" => "EDIT",
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
		"~DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
			array("USER_ALIAS" => $arResult["MY_GALLERY"]["~CODE"], "ACTION" => "DROP", 
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
		"~UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
			array("USER_ALIAS" => $arResult["MY_GALLERY"]["~CODE"], "SECTION_ID" => "0", 
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])));
	$arResult["MY_GALLERY"]["LINK"]["VIEW"] = htmlspecialchars($arResult["MY_GALLERY"]["LINK"]["~VIEW"]);
	$arResult["MY_GALLERY"]["LINK"]["EDIT"] = htmlSpecialChars($arResult["MY_GALLERY"]["LINK"]["~EDIT"]);
	$arResult["MY_GALLERY"]["LINK"]["~DROP"] .= (strpos($arResult["MY_GALLERY"]["LINK"]["~DROP"], "?") === false ? "?" : "&").bitrix_sessid_get();
	$arResult["MY_GALLERY"]["LINK"]["DROP"] = htmlSpecialChars($arResult["MY_GALLERY"]["LINK"]["~DROP"]);
	$arResult["MY_GALLERY"]["LINK"]["UPLOAD"] = htmlSpecialChars($arResult["MY_GALLERY"]["LINK"]["~UPLOAD"]);
endif;
/********************************************************************
				GALLERY
********************************************************************/
if (!empty($arResult["GALLERY"])):
	$arResult["GALLERY"]["LINK"] = array(
		"~VIEW" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["~CODE"], 
				"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])),
		"~EDIT" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["~CODE"], "ACTION" => "EDIT", 
				"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])),
		"~DROP" => CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_EDIT_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["~CODE"], "ACTION" => "DROP", 
				"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])),
		"~UPLOAD" => CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
			array("USER_ALIAS" => $arResult["GALLERY"]["~CODE"], "SECTION_ID" => "0", 
				"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"])));
	$arResult["GALLERY"]["LINK"]["VIEW"] = htmlspecialchars($arResult["GALLERY"]["LINK"]["~VIEW"]);
	$arResult["GALLERY"]["LINK"]["EDIT"] = htmlSpecialChars($arResult["GALLERY"]["LINK"]["~EDIT"]);
	$arResult["GALLERY"]["LINK"]["~DROP"] .= (strpos($arResult["GALLERY"]["LINK"]["~DROP"], "?") === false ? "?" : "&").bitrix_sessid_get();
	$arResult["GALLERY"]["LINK"]["DROP"] = htmlSpecialChars($arResult["GALLERY"]["LINK"]["~DROP"]);
	$arResult["GALLERY"]["LINK"]["UPLOAD"] = htmlSpecialChars($arResult["GALLERY"]["LINK"]["~UPLOAD"]);
endif;
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
	
	if ($arParams["ONLY_ONE_GALLERY"] == "Y" &&  !empty($arResult["MY_GALLERIES"]))
		$arResult["I"]["ACTIONS"]["CREATE_GALLERY"] = "N";

	if ($arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
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
/********************************************************************
				/Prepare Data
********************************************************************/

$arResult["LINK"] = array(
	"INDEX" => CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array( 
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
	"NEW" => CComponentEngine::MakePathFromTemplate($arParams["GALLERY_EDIT_URL"], array("USER_ALIAS" => "NEW_ALIAS", "ACTION" => "CREATE", 
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])),
	"GALLERIES" =>  CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array(
				"USER_ID" => $arResult["MY_GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["MY_GALLERY"]["SOCNET_GROUP_ID"])));

$this->IncludeComponentTemplate();
if ($arParams["RETURN_ARRAY"] == "N") // For custom component
	return $arResult["GALLERY"]["CODE"];
else 
	return array(
		"USER_ALIAS" => $arResult["GALLERY"]["CODE"], 
		"ACTIVE" => $arResult["GALLERY"]["ACTIVE"],
		"PERMISSION" => $arParams["PERMISSION"]);
?>