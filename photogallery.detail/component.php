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
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
	$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
	$arParams["ELEMENT_CODE"] = trim($arParams["ELEMENT_CODE"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	
	$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
	$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"])!="DESC" ? "ASC" : "DESC");
	$arParams["ELEMENT_SORT_FIELD1"] = (empty($arParams["ELEMENT_SORT_FIELD1"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD1"]));
	$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER1"]) != "DESC" ? "ASC" : "DESC");
	$arParams["ELEMENT_SELECT_FIELDS"] = (is_array($arParams["ELEMENT_SELECT_FIELDS"]) ? $arParams["ELEMENT_SELECT_FIELDS"] : array());
	
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#", 
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"detail_edit" => "PAGE_NAME=detail_edit".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#&ACTION=#ACTION#",
		"detail_slide_show" => "PAGE_NAME=detail_slide_show".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"upload" => "PAGE_NAME=upload".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ACTION=upload",
		"search" => "PAGE_NAME=search");
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, 
			array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "sessid", "edit", "login", "USER_ALIAS"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
//***************** ADDITIONAL **************************************/
	$arParams["PASSWORD_CHECKED"] = true;
	$arParams["COMMENTS_TYPE"] = strToUpper($arParams["COMMENTS_TYPE"]);
	$arParams["USE_PERMISSIONS"] = ($arParams["USE_PERMISSIONS"]=="Y" ? "Y" : "N");
	if(!is_array($arParams["GROUP_PERMISSIONS"]))
		$arParams["GROUP_PERMISSIONS"] = array(1);

	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : 
		$GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
//***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] != "N" ? "Y" : "N"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Get data from cache
********************************************************************/
$arResult["GALLERY"] = array();
$arResult["SECTION"] = array();
$arResult["ELEMENT"] = array();
$arResult["ELEMENTS_LIST"] = array();
$arParams["PERMISSION"] = "";
$arResult["I"] = array();

$arCacheParams = array(
	"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"ELEMENT_ID" => $arParams["ELEMENT_ID"],
	"ELEMENT_CODE" => $arParams["ELEMENT_CODE"],
	"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
	"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
	$arParams["USE_PERMISSIONS"], $arParams["GROUP_PERMISSIONS"],
	"SECTION_CODE" => $arParams["SECTION_CODE"],
	"BEHAVIOUR" => $arParams["BEHAVIOUR"],
	"SELECT" => $arParams["ELEMENT_SELECT_FIELDS"]);
/********************************************************************
				ELEMENT
********************************************************************/
$cache = new CPHPCache;
$cache_id = "element_".serialize($arCacheParams);
$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arParams["SECTION_ID"]."/".$arParams["ELEMENT_ID"]."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["ELEMENT"]))
	{
		$arResult["GALLERY"] = $res["GALLERY"];
		$arResult["SECTION"] = $res["SECTION"];
		$arResult["ELEMENT"] = $res["ELEMENT"];
		$arResult["ELEMENTS_LIST"] = $res["ELEMENTS_LIST"];
		$arParams["PERMISSION"] = $res["PERMISSION"];
	}
}
if (empty($arResult["ELEMENT"]))
{
	CModule::IncludeModule("iblock");
	CModule::IncludeModule("photogallery");

	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;
	// GALLERY INFO
	if ($arParams["BEHAVIOUR"] == "USER" && !empty($arParams["USER_ALIAS"]))
	{
		$db_res = CIBlockSection::GetList(array(), array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]), false, array("UF_GALLERY_SIZE"));
		
		if ($db_res && $res = $db_res->GetNext())
		{
			$arResult["GALLERY"] = $res;
		}
		else
		{
			$this->AbortResultCache();
			ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
			return 0;
		}
	}
	else
	{
		$bUSER_HAVE_ACCESS = ($arParams["USE_PERMISSIONS"] == "Y" ? false : true);
		if($arParams["USE_PERMISSIONS"] == "Y")
		{
			$arUserGroupArray = $USER->GetUserGroupArray();
			foreach($arParams["GROUP_PERMISSIONS"] as $PERM)
			{
				if(in_array($PERM, $arUserGroupArray))
				{
					$bUSER_HAVE_ACCESS = true;
					break;
				}
			}
		}
		$bUSER_HAVE_ACCESS = ($arParams["PERMISSION"] >= "W" ? true : $bUSER_HAVE_ACCESS);
		if(!$bUSER_HAVE_ACCESS)
		{
			ShowError(GetMessage("T_DETAIL_PERM_DEN"));
			return 0;
		}
	}
	
	//Handle case when ELEMENT_CODE used
	if($arParams["ELEMENT_ID"]>0)
		$ELEMENT_ID = $arParams["ELEMENT_ID"];
	elseif(strlen($arParams["ELEMENT_CODE"])>0)
	{
			//WHERE
			$arFilter = array(
				"IBLOCK_ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
				"CODE" => $arParams["ELEMENT_CODE"]);
			if($arParams["SECTION_ID"])
				$arFilter["SECTION_ID"]=$arParams["SECTION_ID"];
			elseif($arParams["SECTION_CODE"])
				$arFilter["SECTION_CODE"]=$arParams["SECTION_CODE"];
			//EXECUTE
			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, array("ID"));
			if($arElement = $rsElement->Fetch())
				$ELEMENT_ID = $arElement["ID"];
			else
				$ELEMENT_ID = 0;
	}
	else
		$ELEMENT_ID = 0;

	if($ELEMENT_ID)
	{
		//SELECT
		$arSelect = array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"SECTION_PAGE_URL",
			"NAME",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"PREVIEW_TEXT",
			"DETAIL_TEXT",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
			"TAGS",
			"DATE_CREATE",
			"CREATED_BY",
			"PROPERTY_REAL_PICTURE");
		if (!empty($arParams["ELEMENT_SELECT_FIELDS"]))
			$arSelect = array_merge($arParams["ELEMENT_SELECT_FIELDS"], $arSelect);
		//WHERE
		$arFilter = array(
			"ID" => $ELEMENT_ID,
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y");

		if($arParams["SECTION_ID"])
			$arFilter["SECTION_ID"]=$arParams["SECTION_ID"];
		elseif($arParams["SECTION_CODE"])
			$arFilter["SECTION_CODE"]=$arParams["SECTION_CODE"];

		//SECTION
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult["ELEMENT"] = $obElement->GetFields();
			$arParams["SECTION_ID"] = $arResult["ELEMENT"]["IBLOCK_SECTION_ID"];
			$arResult["ELEMENT"]["PROPERTIES"] = array();
			foreach ($arResult["ELEMENT"] as $key => $val)
			{
				if ((substr($key, 0, 9) == "PROPERTY_" && substr($key, -6, 6) == "_VALUE"))
				{
					$arResult["ELEMENT"]["PROPERTIES"][substr($key, 9, intVal(strLen($key)-15))] = array("VALUE" => $val);
				}
			}

			$rsSection = CIBlockSection::GetList(array(), array("ID" => $arParams["SECTION_ID"]), false, array("UF_PASSWORD"));
			$arResult["SECTION"] = $rsSection->Fetch();
			
			if ($arParams["BEHAVIOUR"] == "USER" && empty($arResult["GALLERY"]))
			{
				$res = array(
					"ACTIVE" => "Y",
					"GLOBAL_ACTIVE" => "Y",
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"IBLOCK_ACTIVE" => "Y",
					"SECTION_ID" => 0,
					"!LEFT_MARGIN" => $arResult["SECTION"]["LEFT_MARGIN"],
					"!RIGHT_MARGIN" => $arResult["SECTION"]["RIGHT_MARGIN"],
					"!ID" => $arParams["SECTION_ID"]);
				$db_res = CIBlockSection::GetList(array(), $res, false, array("UF_GALLERY_SIZE"));
				
				if ($db_res && $res = $db_res->GetNext())
				{
					$arResult["GALLERY"] = $res;
					$arParams["USER_ALIAS"] = $res["~CODE"];
				}
				else
				{
					ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
					return 0;
				}
			}
			elseif ($arParams["BEHAVIOUR"] == "USER" && 
				(intVal($arResult["SECTION"]["MARGIN_LEFT"]) < intVal($arResult["GALLERY"]["MARGIN_LEFT"]) || 
				intVal($arResult["GALLERY"]["RIGHT_LEFT"]) < intVal($arResult["SECTION"]["RIGHT_LEFT"])))
			{
				ShowError(GetMessage("P_SECTION_IS_NOT_IN_GALLERY"));
				return 0;
			}
			$arResult["SECTION"]["PASSWORD"] = $arResult["SECTION"]["UF_PASSWORD"];
			$arResult["SECTION"]["PATH"] = array();
			$rsPath = GetIBlockSectionPath($arResult["SECTION"]["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
			while($arPath=$rsPath->GetNext())
			{
				$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arPath["ID"], LANGUAGE_ID);
				$arPath["~PASSWORD"] = $arUserFields["UF_PASSWORD"];
				if (is_array($arPath["~PASSWORD"]))
					$arPath["PASSWORD"] = $arPath["~PASSWORD"]["VALUE"];

				$arResult["SECTION"]["PATH"][] = $arPath;
			}
		}
		//ELEMENT
		if(isset($arResult["ELEMENT"]["DETAIL_PICTURE"]))
			$arResult["ELEMENT"]["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["DETAIL_PICTURE"]);
		if(isset($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]))
			$arResult["ELEMENT"]["REAL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);

		if(is_array($arResult["ELEMENT"]["DETAIL_PICTURE"]))
			$arResult["ELEMENT"]["PICTURE"] = $arResult["ELEMENT"]["DETAIL_PICTURE"];
		elseif(isset($arResult["ELEMENT"]["PREVIEW_PICTURE"]))
		{
			$arResult["ELEMENT"]["PREVIEW_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["PREVIEW_PICTURE"]);
			$arResult["ELEMENT"]["PICTURE"] = $arResult["ELEMENT"]["PREVIEW_PICTURE"];
		}

		$arResult["ELEMENT"]["TAGS_LIST"] = array();
		if ($arParams["SHOW_TAGS"] == "Y" && !empty($arResult["ELEMENT"]["TAGS"]))
		{
			if (IsModuleInstalled("search") && CModule::IncludeModule("search"))
			{
				$ar = tags_prepare($arResult["ELEMENT"]["TAGS"], SITE_ID);
				if (!empty($ar))
				{
					foreach ($ar as $name => $tags)
					{
						$ar["~TAGS_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array());
						if (strpos($ar["~TAGS_URL"], "?") === false)
							$ar["~TAGS_URL"] .= "?";
						else
							$ar["~TAGS_URL"] .= "&";
						$ar["~TAGS_URL"] .= "tags=".$tags;
						$ar["TAGS_URL"] = htmlSpecialChars($ar["~TAGS_URL"]);
						$ar["TAGS_NAME"] = $tags;
						$arResult["ELEMENT"]["TAGS_LIST"][] = $ar;
					}
				}
			}
		}

		if (!empty($arResult["ELEMENT"]["DATE_CREATE"]))
		{
			$arResult["ELEMENT"]["DATE_CREATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["ELEMENT"]["DATE_CREATE"], CSite::GetDateFormat()));
		}
	}

	if(isset($arResult["ELEMENT"]["ID"]))
	{
		//SELECT
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"DETAIL_PAGE_URL",
			"NAME",
			"PREVIEW_PICTURE",
			"PREVIEW_TEXT");
		//WHERE
		$arFilter = array(
			"IBLOCK_ID" => $arResult["ELEMENT"]["IBLOCK_ID"],
			"SECTION_ID" => $arResult["SECTION"]["ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y");
		//ORDER BY
		$arSort = array();
		if (!empty($arParams["ELEMENT_SORT_FIELD"]))
		{
			if ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" || $arParams["ELEMENT_SORT_FIELD"] == "RATING")
			{
				if ($arParams["ELEMENT_SORT_FIELD"] == "RATING")
					$arParams["ELEMENT_SORT_FIELD"] = "RATING";
				elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM")
					$arParams["ELEMENT_SORT_FIELD"] = "FORUM_MESSAGE_CNT";
				elseif ($arParams["ELEMENT_SORT_FIELD"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG")
					$arParams["ELEMENT_SORT_FIELD"] = "BLOG_COMMENTS_CNT";
				$arParams["ELEMENT_SORT_FIELD"] = "PROPERTY_".$arParams["ELEMENT_SORT_FIELD"];
			}
			elseif ($arParams["ELEMENT_SORT_FIELD"] == "SHOWS")
			{
				$arParams["ELEMENT_SORT_FIELD"] = "SHOW_COUNTER";
			}
			$arSort[$arParams["ELEMENT_SORT_FIELD"]] = $arParams["ELEMENT_SORT_ORDER"];
		}
		if (!empty($arParams["ELEMENT_SORT_FIELD1"]))
		{
			if ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" || $arParams["ELEMENT_SORT_FIELD1"] == "RATING")
			{
				if ($arParams["ELEMENT_SORT_FIELD1"] == "RATING")
					$arParams["ELEMENT_SORT_FIELD1"] = "RATING";
				elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "FORUM")
					$arParams["ELEMENT_SORT_FIELD1"] = "FORUM_MESSAGE_CNT";
				elseif ($arParams["ELEMENT_SORT_FIELD1"] == "COMMENTS" && $arParams["COMMENTS_TYPE"] == "BLOG")
					$arParams["ELEMENT_SORT_FIELD1"] = "BLOG_COMMENTS_CNT";
				$arParams["ELEMENT_SORT_FIELD1"] = "PROPERTY_".$arParams["ELEMENT_SORT_FIELD1"];
			}
			elseif ($arParams["ELEMENT_SORT_FIELD1"] == "SHOWS")
			{
				$arParams["ELEMENT_SORT_FIELD1"] = "SHOW_COUNTER";
			}
			$arSort[$arParams["ELEMENT_SORT_FIELD1"]] = $arParams["ELEMENT_SORT_ORDER1"];
		}
		if ($arParams["ELEMENT_SORT_FIELD"] != "ID")
			$arSort["ID"] = "ASC";

		// EbK Iblock < 7.0.7
		$arSelect = array_keys(array_flip(array_diff($arSelect, array_keys($arSort))));
		
		//EXECUTE
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		$ii = 0; $bCount = true;
		while($arElement = $rsElement->GetNext())
		{
			if ($bCount)
				$ii++;
			if (intVal($arElement["ID"]) == intVal($arResult["ELEMENT"]["ID"]))
				$bCount = false;

			$arResult["ELEMENTS_LIST"][] = $arElement;
		}
		$arResult["ELEMENT"]["CURRENT"]["NO"] = $ii;
		$arResult["ELEMENT"]["CURRENT"]["COUNT"] = count($arResult["ELEMENTS_LIST"]);

		if ($arParams["CACHE_TIME"] > 0)
		{
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
			$cache->EndDataCache(
				array(
				"GALLERY" => $arResult["GALLERY"],
				"SECTION" => $arResult["SECTION"],
				"ELEMENT" => $arResult["ELEMENT"],
				"ELEMENTS_LIST" => $arResult["ELEMENTS_LIST"],
				"PERMISSION" => $arParams["PERMISSION"]));
		}
	}
}
if (empty($arResult["ELEMENT"]))
{
	ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
	@define("ERROR_404", "Y");
	return 0;
}
/********************************************************************
				/Get data from cache
********************************************************************/
/********************************************************************
				Prepare Data
********************************************************************/
// Check permission
if ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;
$arResult["I"]["ABS_PERMISSION"] = $arParams["PERMISSION"];
if ($arParams["PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
	$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
	$arParams["PERMISSION"] = "W";
$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];

if (is_array($arResult["SECTION"]["PATH"]))
{
	foreach ($arResult["SECTION"]["PATH"] as $key => $res)
	{
		$arResult["SECTION"]["PATH"][$key]["~SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
			array("USER_ALIAS" => "empty", "SECTION_ID" => $res["ID"]));
		if ($arParams["BEHAVIOUR"] == "USER" && $res["ID"] == $arResult["GALLERY"]["ID"])
		{
			$arResult["SECTION"]["PATH"][$key]["~SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
				array("USER_ALIAS" => "empty", "SECTION_ID" => $res["ID"]));
		}
		$arResult["SECTION"]["PATH"][$key]["SECTION_PAGE_URL"] = htmlSpecialChars($arResult["SECTION"]["PATH"][$key]["~SECTION_PAGE_URL"]);
		if (empty($res["PASSWORD"]) || $arParams["PERMISSION"] >= "W")
			continue;

		$arParams["PASSWORD_CHECKED"] = false;

		if ($res["PASSWORD"] != $_SESSION['PHOTOGALLERY']['SECTION'][$res["ID"]])
		{
			$arParams["PASSWORD_CHECKED"] = false;

			ShowError(GetMessage("T_DETAIL_PERM_DEN"));
			return 0;
			break;
		}
	}
}

$arResult["SECTION"]["~BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
$arResult["SECTION"]["BACK_LINK"] = htmlspecialchars($arResult["SECTION"]["~BACK_LINK"]);

if (!is_array($arResult["ELEMENTS_LIST"]))
	$arResult["ELEMENTS_LIST"] = array();
foreach ($arResult["ELEMENTS_LIST"] as $key => $res)
{
	if ($res["ID"] != $arResult["ELEMENT"]["ID"])
	{
		$arResult["ELEMENTS_LIST"][$key]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" =>$res["ID"]));
		$arResult["ELEMENTS_LIST"][$key]["DETAIL_PAGE_URL"] = htmlSpecialChars($arResult["ELEMENTS_LIST"][$key]["~DETAIL_PAGE_URL"]);
	}
	else
	{
		$arResult["ELEMENTS_LIST"][$key]["~DETAIL_PAGE_URL"] = "";
		$arResult["ELEMENTS_LIST"][$key]["DETAIL_PAGE_URL"] = "";
	}
}

if ($arParams["PERMISSION"] >= "W")
{
	$arResult["SECTION"]["~UPLOAD_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~UPLOAD_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
	$arResult["SECTION"]["UPLOAD_LINK"] = htmlSpecialChars($arResult["SECTION"]["~UPLOAD_LINK"]);

	$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
	$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlSpecialChars($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);

	$arResult["ELEMENT"]["~EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"], "ACTION" => "edit"));
	$arResult["ELEMENT"]["EDIT_URL"] = htmlSpecialChars($arResult["ELEMENT"]["~EDIT_URL"]);

	$arResult["ELEMENT"]["~DROP_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_EDIT_URL"],
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"], "ACTION" => "drop"));
	if (strpos($arResult["ELEMENT"]["~DROP_URL"], "?") === false)
		$arResult["ELEMENT"]["~DROP_URL"] .= "?";
	else
		$arResult["ELEMENT"]["~DROP_URL"] .= "&";
	$arResult["ELEMENT"]["~DROP_URL"] .= bitrix_sessid_get();
	$arResult["ELEMENT"]["DROP_URL"] = htmlSpecialChars($arResult["ELEMENT"]["~DROP_URL"]);
}

$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"],
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlSpecialChars($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);

$arResult["~SLIDE_SHOW"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"], 
	array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
$arResult["SLIDE_SHOW"] = htmlspecialchars($arResult["~SLIDE_SHOW"]);
/*************************************************************************
			/Prepare data
*************************************************************************/
$this->IncludeComponentTemplate();
/*************************************************************************
	Any actions without cache (if there was some to display)
*************************************************************************/
if(isset($arResult["ELEMENT"]["ID"]))
{
	if(CModule::IncludeModule("iblock"))
	{
		CIBlockElement::CounterInc($arResult["ELEMENT"]["ID"]);
		if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized())
		{
			CIBlock::ShowPanel($arParams["IBLOCK_ID"], $arResult["ELEMENT"]["ID"], $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
		}
	}

	if($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);

	if(is_array($arResult["SECTION"]["PATH"]))
	{
		foreach($arResult["SECTION"]["PATH"] as $arPath)
		{
			if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
				continue;
			if (!empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
				$APPLICATION->AddChainItem($arPath["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"],
					array("USER_ALIAS" => $arResult["GALLERY"]["CODE"])));
			else
				$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		}
	}
	return $arResult["ELEMENT"]["ID"];
}
?>