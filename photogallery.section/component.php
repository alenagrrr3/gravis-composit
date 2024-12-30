<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
elseif (empty($arParams["SECTION_CODE"]) && intVal($arParams["SECTION_ID"]) <= 0):
	ShowError(GetMessage("P_SECTION_EMPTY"));
	return 0;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"sections_top" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#",
		"section_edit" => "PAGE_NAME=section_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"section_edit_icon" => "PAGE_NAME=section_edit_icon".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=#ACTION#",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"upload" => "PAGE_NAME=upload".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=upload");

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, 
			array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "sessid", "edit", "login", "USER_ALIAS", "order", "group_by"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
//***************** ADDITIONAL **************************************/
	$arParams["PASSWORD_CHECKED"] = true;
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : 
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["ALBUM_PHOTO_SIZE"] = (intVal($arParams["ALBUM_PHOTO_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_SIZE"]) : 150);
	$arParams["ALBUM_PHOTO_THUMBS_SIZE"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) : 70);
	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"]);
//***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Get data from cache
********************************************************************/
$arResult["GALLERY"] = array();
$arResult["SECTION"] = array();
$arResult["SECTIONS_CNT"] = 0;
$arParams["PERMISSION"] = "";
$arResult["I"] = array();
	
$arCacheParams = array(
	"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"SECTION_CODE" => $arParams["SECTION_CODE"],
	"BEHAVIOUR" => $arParams["BEHAVIOUR"]);
$cache = new CPHPCache;
/*************************************************************************
				GALLERY
*************************************************************************/
if ($arParams["BEHAVIOUR"] == "USER")
{
	$cache_id = serialize(array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $arParams["USER_ALIAS"]));
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$arParams["USER_ALIAS"]."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		$arResult["GALLERY"] = $res["GALLERY"];
	}
	
	if (!is_array($arResult["GALLERY"]) || empty($arResult["GALLERY"]))
	{
		CModule::IncludeModule("iblock");
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]);
		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), 
			$arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_GALLERY_RECALC", "UF_DATE"));
		if ($db_res && $res = $db_res->GetNext())
		{
			$res["NAME"] = $res["~NAME"];
			$res["DESCRIPTION"] = $res["~DESCRIPTION"];
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
				$image_resize = CFile::ResizeImageGet($res["PICTURE"], array("width" => 50, "height" => 50));
				$res["PICTURE"]["SRC"] = $image_resize["src"];
			}
			$arResult["GALLERY"] = $res;
		}
		else 
		{
			ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
			return 0;
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(array("GALLERY" => $arResult["GALLERY"]));
		}
	}
}
/********************************************************************
				SECTION
********************************************************************/
$cache_id = "section_".serialize($arCacheParams);
$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arParams["SECTION_ID"]."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["SECTION"]))
	{
		$arResult["GALLERY"] = $res["GALLERY"];
		$arResult["SECTION"] = $res["SECTION"];
		$arResult["SECTIONS_CNT"] = $res["SECTIONS_CNT"];
		$arParams["PERMISSION"] = $res["PERMISSION"];
	}
}
if (!is_array($arResult["SECTION"]) || empty($arResult["SECTION"]))
{
	CModule::IncludeModule("iblock");
	CModule::IncludeModule("photogallery");

	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;
	// SECTION INFO
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");

	if ($arParams["BEHAVIOUR"] == "USER")
	{
		$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
		$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
		$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
	}
	
	if(strlen($arParams["SECTION_CODE"]) > 0)
		$arFilter["CODE"]=$arParams["SECTION_CODE"];
	else
		$arFilter["ID"]=$arParams["SECTION_ID"];
	$rsSection = CIBlockSection::GetList(Array(), $arFilter);
	$arResult["SECTION"] = $rsSection->GetNext();
	if (empty($arResult["SECTION"]))
	{
		ShowError(GetMessage("P_SECTION_NOT_FOUND"));
		return 0;
		@define("ERROR_404", "Y");
	}
	
	$rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arParams["SECTION_ID"]);
	while($arPath=$rsPath->GetNext())
	{
		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arPath["ID"], LANGUAGE_ID);
		$arPath["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
		if (is_array($arPath["~PASSWORD"]))
			$arPath["PASSWORD"] = $arPath["~PASSWORD"]["VALUE"];
			
		$arResult["SECTION"]["PATH"][] = $arPath;
	}
	
	$arResult["SECTION"]["PICTURE"] = CFile::GetFileArray($arResult["SECTION"]["PICTURE"]);
	$arResult["SECTION"]["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["SECTION"]["DETAIL_PICTURE"]);
	
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arParams["SECTION_ID"], LANGUAGE_ID);
	
	$arResult["SECTION"]["~DATE"] = $arUserFields["UF_DATE"];
	$arResult["SECTION"]["DATE"] = $arResult["SECTION"]["~DATE"];
	if (is_array($arResult["SECTION"]["~DATE"]))
		$arResult["SECTION"]["DATE"]["VALUE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["SECTION"]["~DATE"]["VALUE"], CSite::GetDateFormat()));
		
	$arResult["SECTION"]["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
	if (is_array($arResult["SECTION"]["~PASSWORD"]))
		$arResult["SECTION"]["PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
	
	$arResult["SECTION"]["ELEMENTS_CNT"] = intVal(
		CIBlockSection::GetSectionElementsCount(
			$arParams["SECTION_ID"], Array("CNT_ALL"=>"Y")));
	
	
	$arResult["SECTIONS_CNT"] = intVal(
		CIBlockSection::GetCount(array(
			"IBLOCK_ID"=>$arParams["IBLOCK_ID"], 
			"SECTION_ID"=>$arParams["SECTION_ID"])));
	// /SECTION INFO

	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(
			array(
				"GALLERY" => $arResult["GALLERY"],
				"SECTION" => $arResult["SECTION"],
				"SECTIONS_CNT" => $arResult["SECTIONS_CNT"],
				"PERMISSION" => $arParams["PERMISSION"]));
	}
}
/********************************************************************
				/Get data from cache
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;
	
$arParams["SECTION_ID"] = $arResult["SECTION"]["ID"];
if (is_array($arResult["SECTION"]["PATH"]))
{
	foreach ($arResult["SECTION"]["PATH"] as $key => $res)
	{
		if ($arParams["BEHAVIOUR"] == "USER" && $res["ID"] == $arResult["GALLERY"]["ID"])
			$arResult["SECTION"]["PATH"][$key]["SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
				array("USER_ALIAS" => $arParams["USER_ALIAS"]));
		else
			$arResult["SECTION"]["PATH"][$key]["SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"]));
	}
}

if ($arParams["BEHAVIOUR"] == "USER" && $arResult["SECTION"]["IBLOCK_SECTION_ID"] == $arResult["GALLERY"]["ID"])
	$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
			array("USER_ALIAS" => $arResult["GALLERY"]["~CODE"]));
elseif (intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
	$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
else
	$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTIONS_TOP_URL"], 
			array());
$arResult["SECTION"]["BACK_LINK"] = htmlspecialchars($arResult["SECTION"]["~BACK_LINK"]);

// Check permission
$arResult["I"]["ABS_PERMISSION"] = $arParams["PERMISSION"];
if ($arParams["PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
	$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
	$arParams["PERMISSION"] = "W";
$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];
if ($arParams["PERMISSION"] >= "W")
{
	$arResult["SECTION"]["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "new"));
	$arResult["SECTION"]["NEW_LINK"] = htmlSpecialChars($arResult["SECTION"]["~NEW_LINK"]);
	$arResult["SECTION"]["~EDIT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "edit"));
	$arResult["SECTION"]["EDIT_LINK"] = htmlSpecialChars($arResult["SECTION"]["~EDIT_LINK"]);
	if ($arResult["SECTION"]["ELEMENTS_CNT"] > 0)
	{
		$arResult["SECTION"]["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_ICON_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "edit"));
		$arResult["SECTION"]["EDIT_ICON_LINK"] = htmlSpecialChars($arResult["SECTION"]["~EDIT_ICON_LINK"]);
	}
	$arResult["SECTION"]["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "drop"));
	if (strpos($arResult["SECTION"]["~DROP_LINK"], "?") === false)
		$arResult["SECTION"]["~DROP_LINK"] .= "?";
	$arResult["SECTION"]["~DROP_LINK"] .= "&".bitrix_sessid_get()."&edit=Y";
	$arResult["SECTION"]["DROP_LINK"] = htmlSpecialChars($arResult["SECTION"]["~DROP_LINK"]);
	
	if ($arParams["BEHAVIOUR"] != "USER" || $arParams["GALLERY_SIZE"] <= 0 || $arParams["GALLERY_SIZE"] > $arResult["GALLERY"]["UF_GALLERY_SIZE"])
	{
		$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
		$arResult["SECTION"]["UPLOAD_LINK"] = htmlSpecialChars($arResult["SECTION"]["~UPLOAD_LINK"]);
	}
}

// Check Password
$arParams["PASSWORD_CHECKED"] = true;
if (is_array($arResult["SECTION"]["PATH"]) && $arParams["PERMISSION"] < "W")
{
	foreach ($arResult["SECTION"]["PATH"] as $key => $res) 
	{
		if (empty($res["PASSWORD"]))
			continue;
		$arParams["PASSWORD_CHECKED"] = false;
		
		if ($res["ID"] != $arParams["SECTION_ID"])
		{
			if ($res["PASSWORD"] != $_SESSION['PHOTOGALLERY']['SECTION'][$res["ID"]])
			{
				$arParams["PASSWORD_CHECKED"] = false;
				
				ShowError(GetMessage("P_SECTION_NOT_RIGTH"));
				return 0;
				@define("ERROR_404", "Y");
				break;
			}
		}
		else 
		{
			if ($arResult["SECTION"]["PASSWORD"] == md5($_REQUEST["password_".$arParams["SECTION_ID"]]))
			{
				$arParams["PASSWORD_CHECKED"] = true;
				$_SESSION['PHOTOGALLERY']['SECTION'][$arParams["SECTION_ID"]] = md5($_REQUEST["password_".$arParams["SECTION_ID"]]);
			}
			elseif ($arResult["SECTION"]["PASSWORD"] == $_SESSION['PHOTOGALLERY']['SECTION'][$arParams["SECTION_ID"]])
			{
				$arParams["PASSWORD_CHECKED"] = true;
			}
		}
	}
}
/********************************************************************
				/Prepare Data
********************************************************************/
IncludeAJAX();
$this->IncludeComponentTemplate();

if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());

if ($arParams["SET_TITLE"] == "Y")
{
	if (!empty($arResult["SECTION"]["NAME"]))
		$GLOBALS["APPLICATION"]->SetTitle($arResult["SECTION"]["NAME"]);
	else
		$GLOBALS["APPLICATION"]->SetTitle(GetMessage($arResult["SECTION"]["ID"]));
}

if (is_array($arResult["SECTION"]["PATH"]) && count($arResult["SECTION"]["PATH"]) > 0)
{
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
			continue;
		if ($arPath["ID"] != $arParams["SECTION_ID"])
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"], $arPath["SECTION_PAGE_URL"]);
		else
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"]);
	}
}
return $arResult["SECTION"]["ID"];
?>