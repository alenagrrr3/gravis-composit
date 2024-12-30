<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
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
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);	
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);

	$arParams["SORT_BY"] = (!empty($arParams["SORT_BY"]) ? $arParams["SORT_BY"] : "ID");
	$arParams["SORT_ORD"] = ($arParams["SORT_ORD"] != "ASC" ? "DESC" : "ASC");
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
			array("PAGE_NAME", "USER_ALIAS", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "sessid", "edit", "login", "order", "group_by"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
//***************** ADDITIONAL **************************************/
	$arParams["PASSWORD_CHECKED"] = true;
	
	$arParams["ALBUM_PHOTO_SIZE"] = (intVal($arParams["ALBUM_PHOTO_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_SIZE"]) : 150);
	$arParams["ALBUM_PHOTO_THUMBS_SIZE"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_SIZE"]) : 70);

	$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
	$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
	
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : 
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
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
$arResult["SECTIONS"] = array();
$arResult["SECTIONS_CNT"] = 0;
$arParams["PERMISSION"] = "";
$arResult["I"] = array();
$arCacheParams = array(
	"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"SECTION_CODE" => $arParams["SECTION_CODE"],
	"BEHAVIOUR" => $arParams["BEHAVIOUR"],
	"USER_ALIAS" => $arParams["USER_ALIAS"]);
$cache = new CPHPCache;
$sUserAlias = ($arParams["BEHAVIOUR"] == "USER" && !empty($arParams["USER_ALIAS"]) ? $arParams["USER_ALIAS"] : "!simple!");
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
/*************************************************************************
				SECTION
*************************************************************************/
if ($arParams["SECTION_ID"] > 0 || !empty($arParams["SECTION_CODE"])):
	$cache_id = "section_".serialize($arCacheParams);
	$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arParams["SECTION_ID"]."/";
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["SECTION"]))
		{
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
		$arSelect = array();
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y");
		
		if ($arParams["BEHAVIOUR"] == "USER")
		{
			if ($arParams["SECTION_CODE"] != $arResult["GALLERY"]["~CODE"] && 
				$arParams["SECTION_ID"] != $arResult["GALLERY"]["ID"])
				$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
			$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
			$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
		}

		if(strlen($arParams["SECTION_CODE"]) > 0)
			$arFilter["CODE"]=$arParams["SECTION_CODE"];
		else
			$arFilter["ID"]=$arParams["SECTION_ID"];
	
		$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, $arSelect);
		$arResult["SECTION"] = $rsSection->GetNext();
		if (empty($arResult["SECTION"]))
		{
			if (strlen($arParams["SECTION_CODE"]) < 1 && !$arParams["SECTION_ID"])
			{
				$arResult["SECTION"] = array(
					"ID" => $arParams["SECTION_ID"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"]);
			}
			else 
			{
				ShowError(GetMessage("P_SECTION_NOT_FOUND"));
				return 0;
				@define("ERROR_404", "Y");
			}
		}
		elseif (!empty($arResult["SECTION"]))
		{
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
			if (is_array($arResult["SECTION"]["~DATE"]))
				$arResult["SECTION"]["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["SECTION"]["~DATE"]["VALUE"], CSite::GetDateFormat()));
	
			$arResult["SECTION"]["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
			if (is_array($arResult["SECTION"]["~PASSWORD"]))
				$arResult["SECTION"]["PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
		}
		$arParams["SECTION_ID"] = intVal($arResult["SECTION"]["ID"]);
	
		$arResult["SECTION"]["ELEMENTS_CNT"] = intVal(
			CIBlockSection::GetSectionElementsCount(
				$arParams["SECTION_ID"], Array("CNT_ALL"=>"Y")));
	
		$arResult["SECTIONS_CNT"] = intVal(
			CIBlockSection::GetCount(array(
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
				"SECTION_ID"=>$arParams["SECTION_ID"])));
	
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
	if ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
		$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;
	// Check Permission
	$arResult["I"] = array(
		"ABS_PERMISSION" => $arParams["PERMISSION"],
		"PERMISSION" => $arParams["PERMISSION"]);
	if ($arResult["I"]["ABS_PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
		$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
		$arParams["PERMISSION"] = "W";
	// Check Password
	$arParams["PASSWORD_CHECKED"] = true;
	if (is_array($arResult["SECTION"]["PATH"]) && $arParams["PERMISSION"] < "W")
	{
		foreach ($arResult["SECTION"]["PATH"] as $key => $res)
		{
			if (!empty($res["PASSWORD"]) &&
				($res["PASSWORD"] != $_SESSION['PHOTOGALLERY']['SECTION'][$res["ID"]]))
			{
				$arParams["PASSWORD_CHECKED"] = false;
				break;
			}
		}
	}
endif;
/*************************************************************************
				SECTION LIST
*************************************************************************/
$arResult["SECTIONS"] = array();
$arParams["SECTION_ID"] = intVal($arResult["SECTION"]["ID"]);
if ($arParams["PASSWORD_CHECKED"] && ($arParams["SECTION_ID"] <= 0 || $arResult["SECTIONS_CNT"] > 0))
{
	if ($arParams["SECTION_ID"] <= 0 && $arParams["BEHAVIOUR"] == "USER")
		$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arResult["GALLERY"]["ID"]."/";
	else
		$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arParams["SECTION_ID"]."/";
	$cache_id = "section_list_".serialize($arCacheParams);
	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$res = $cache->GetVars();
		if (is_array($res["SECTIONS"]))
		{
			$arResult["SECTIONS"] = $res["SECTIONS"];
			$arParams["PERMISSION"] = $res["PERMISSION"];
		}
	}
	if (!is_array($arResult["SECTIONS"]) || empty($arResult["SECTIONS"]))
	{
		CModule::IncludeModule("iblock");
		CModule::IncludeModule("photogallery");
		
		$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
		if ($arParams["PERMISSION"] < "R"):
			ShowError(GetMessage("P_DENIED_ACCESS"));
			return 0;
		endif;
		
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => intVal($arParams["SECTION_ID"]));

		// GALLERY INFO
		if ($arParams["BEHAVIOUR"] == "USER")
		{
			if ($arFilter["SECTION_ID"] <= 0)
				$arFilter["SECTION_ID"] = $arResult["GALLERY"]["ID"];
			else
			{
				if ($arParams["SECTION_CODE"] != $arResult["GALLERY"]["CODE"] && 
					$arParams["SECTION_ID"] != $arResult["GALLERY"]["ID"])
					$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
				$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
				$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
			}
		}

		$db_res = CIBlockSection::GetList(array($arParams["SORT_BY"] => $arParams["SORT_ORD"], "ID" => "DESC"), $arFilter, false, array("UF_DATE", "UF_PASSWORD"));

		if ($db_res && $res = $db_res->GetNext())
		{
			do
			{
				$res["DATE"] = $res["UF_DATE"];
				$res["~DATE"] = $res["~UF_DATE"];
				if (!empty($res["~DATE"]))
				{
					$res["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($res["~DATE"], CSite::GetDateFormat()));
				}

				$res["PASSWORD"] = $res["UF_PASSWORD"];
				$res["~PASSWORD"] = $res["~UF_PASSWORD"];

				$res["PICTURE"] = CFile::GetFileArray($res["PICTURE"]);
				$res["DETAIL_PICTURE"] = CFile::GetFileArray($res["DETAIL_PICTURE"]);

				$res["SECTIONS_CNT"] = intVal(
					CIBlockSection::GetCount(array(
						"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
						"SECTION_ID"=>$res["ID"])));
				$res["ELEMENTS_CNT"] = intVal(
					CIBlockSection::GetSectionElementsCount(
						$res["ID"], Array("CNT_ALL"=>"Y")));
				$arResult["SECTIONS"][] = $res;
			}
			while ($res = $db_res->GetNext());
		}
		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(
				array(
					"SECTIONS" => $arResult["SECTIONS"],
					"PERMISSION" => $arParams["PERMISSION"]));
		}
	}
}
/********************************************************************
				/Get data from cache
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
if ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;
if (empty($arResult["I"]))
{
	$arResult["I"] = array(
		"ABS_PERMISSION" => $arParams["PERMISSION"],
		"PERMISSION" => $arParams["PERMISSION"]);
	if ($arResult["I"]["ABS_PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
		$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
	{
		$arParams["PERMISSION"] = "W";
		$arResult["I"]["PERMISSION"] = "W";
	}
}
if ($arParams["PERMISSION"] >= "W")
{
	$arResult["SECTION"]["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ACTION" => "new"));
	$arResult["SECTION"]["NEW_LINK"] = htmlSpecialChars($arResult["SECTION"]["~NEW_LINK"]);
	if ($arParams["SECTION_ID"] == $arResult["GALLERY"]["ID"])
		$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => 0));
	else 
		$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
	$arResult["SECTION"]["UPLOAD_LINK"] = htmlSpecialChars($arResult["SECTION"]["~UPLOAD_LINK"]);
}
if ($arParams["SECTION_ID"] > 0 && $arResult["GALLERY"]["ID"] != $arParams["SECTION_ID"])
{
	$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"]));
	$arResult["SECTION"]["BACK_LINK"] = htmlspecialchars($arResult["SECTION"]["~BACK_LINK"]);
}

if (!is_array($arResult["SECTIONS"]))
	$arResult["SECTIONS"] = array();

$db_res = new CDBResult;
$db_res->InitFromArray($arResult["SECTIONS"]);
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$db_res->NavStart($arParams["PAGE_ELEMENTS"], false);
	$arResult["NAV_STRING"] = $db_res->GetPageNavStringEx($navComponentObject, GetMessage("P_ALBUMS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
	$arResult["NAV_RESULT"] = $db_res;
}

$arResult["SECTIONS"] = array();
if(intVal($db_res->SelectedRowsCount())>0)
{
	while ($res = $db_res->Fetch())
	{
		$res["~LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"]));
		$res["LINK"] = htmlspecialchars($res["~LINK"]);
		if ($arParams["PERMISSION"] >= "W")
		{
			$res["~NEW_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "new"));
			$res["NEW_LINK"] = htmlSpecialChars($res["~NEW_LINK"]);
			$res["~EDIT_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
			$res["EDIT_LINK"] = htmlSpecialChars($res["~EDIT_LINK"]);
			if ($res["ELEMENTS_CNT"] > 0)
			{
				$res["~EDIT_ICON_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_ICON_URL"],
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "edit"));
				$res["EDIT_ICON_LINK"] = htmlSpecialChars($res["~EDIT_ICON_LINK"]);
			}
			$res["~DROP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_EDIT_URL"],
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $res["ID"], "ACTION" => "drop"));
			if (strpos($res["~DROP_LINK"], "?") === false)
				$res["~DROP_LINK"] .= "?";
			$res["~DROP_LINK"] .= "&".bitrix_sessid_get()."&edit=Y";
			$res["DROP_LINK"] = htmlSpecialChars($res["~DROP_LINK"]);
		}
		$arResult["SECTIONS"][] = $res;
	}
}
/********************************************************************
				/Prepare Data
********************************************************************/
$this->IncludeComponentTemplate();
IncludeAJAX();
if ($arParams["SET_TITLE"] == "Y")
{
	if (!empty($arResult["SECTION"]["NAME"]))
		$APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);
	elseif (!empty($arResult["GALLERY"]["NAME"]))
		$APPLICATION->SetTitle($arResult["GALLERY"]["NAME"]);
	else 
		$APPLICATION->SetTitle(getMessage("P_ALBUMS"));
}
if ($arParams["ADD_CHAIN_ITEM"] == "Y" && empty($arResult["SECTION"]["NAME"]) && !empty($arResult["GALLERY"]["NAME"]))
	$GLOBALS["APPLICATION"]->AddChainItem($arResult["GALLERY"]["NAME"]);
if ($arParams["DISPLAY_PANEL"] == "Y" && $GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("iblock"))
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
?>