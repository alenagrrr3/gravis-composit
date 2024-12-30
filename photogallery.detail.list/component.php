<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")): // !important
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!IsModuleInstalled("iblock")): // !important
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);	
$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");

$arParams["ELEMENTS_LAST_COUNT"] = intVal($arParams["ELEMENTS_LAST_COUNT"]);
$arParams["ELEMENT_LAST_TIME"] = intVal($arParams["ELEMENT_LAST_TIME"]);
$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENT_SORT_FIELD1"] = (empty($arParams["ELEMENT_SORT_FIELD1"]) ? false : strToUpper($arParams["ELEMENT_SORT_FIELD1"]));
$arParams["ELEMENT_SORT_ORDER1"] = (strToUpper($arParams["ELEMENT_SORT_ORDER1"]) != "DESC" ? "ASC" : "DESC");
$arParams["ELEMENT_FILTER"] = (is_array($arParams["ELEMENT_FILTER"]) ? $arParams["ELEMENT_FILTER"] : array());
$arParams["ELEMENT_SELECT_FIELDS"] = (is_array($arParams["ELEMENT_SELECT_FIELDS"]) ? $arParams["ELEMENT_SELECT_FIELDS"] : array());
//***************** URL ********************************************/
$URL_NAME_DEFAULT = array(
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"detail_slide_show" => "PAGE_NAME=detail_slide_show".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"search" => "PAGE_NAME=search");

foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, 
				array("PAGE_NAME", "USER_ALIAS", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "edit", "detail_list_edit", "sessid", 
				"order", "group_by", "tags"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
//***************** ADDITTIONAL ************************************/
$arParams["USE_PERMISSIONS"] = $arParams["USE_PERMISSIONS"]=="Y";
if(!is_array($arParams["GROUP_PERMISSIONS"]))
	$arParams["GROUP_PERMISSIONS"] = array(1);
	
$arParams["USE_DESC_PAGE"] = ($arParams["USE_DESC_PAGE"] == "N" ? "N" : "Y");
$arParams["PAGE_ELEMENTS"] = intVal($arParams["PAGE_ELEMENTS"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);

$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : 
	$DB->DateFormatToPHP(CSite::GetDateFormat("FULL")));
$arParams["COMMENTS_TYPE"] = (strToUpper($arParams["COMMENTS_TYPE"]) == "FORUM" ? "FORUM" : "BLOG"); 
$GLOBALS["COMMENTS_TYPE"] = $arParams["COMMENTS_TYPE"];
// Additional sights
$arParams["PICTURES"] = array();
$arParams["ADDITIONAL_SIGHTS"] = (is_array($arParams["ADDITIONAL_SIGHTS"]) ? $arParams["ADDITIONAL_SIGHTS"] : array());
$arParams["PICTURES_SIGHT"] = strToLower($arParams["PICTURES_SIGHT"]);
$arParams["GALLERY_SIZE"]  = intVal($arParams["GALLERY_SIZE"]);
$arParams["GET_GALLERY_INFO"] = ($arParams["GET_GALLERY_INFO"] == "Y" ? "Y" : "N"); // Hidden param

$arParams["SHOW_RATING"] = ($arParams["SHOW_RATING"] == "Y" ? "Y" : "N");
$arParams["SHOW_COMMENTS"] = ($arParams["SHOW_COMMENTS"] == "Y" ? "Y" : "N");
$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
//***************** STANDART ***************************************/
if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
if (!empty($arParams["ADDITIONAL_SIGHTS"]))
{
	$arParams["PICTURES_INFO"] = @unserialize(COption::GetOptionString("photogallery", "pictures"));
	$arParams["PICTURES_INFO"] = (is_array($arParams["PICTURES_INFO"]) ? $arParams["PICTURES_INFO"] : array());
	
	if (!empty($arParams["PICTURES_INFO"]))
	{
		foreach ($arParams["PICTURES_INFO"] as $key => $val)
		{
			if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
				$arParams["PICTURES"][$val["code"]] = array(
					"size" => $arParams["PICTURES_INFO"][$key]["size"],
					"quality" => $arParams["PICTURES_INFO"][$key]["quality"],
					"title" => $arParams["PICTURES_INFO"][$key]["title"]);
		}
	}

	if (empty($arParams["PICTURES_SIGHT"]) && !empty($arParams["PICTURES"]))
	{
		if ($GLOBALS["USER"]->IsAuthorized())
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
			$arParams["PICTURES_SIGHT"] = CUserOptions::GetOption("photogallery", "PictureSight", "standart");
		}
		else 
		{
			$arParams["PICTURES_SIGHT"] = $_REQUEST["PICTURES_SIGHT"];
		}
	}
	elseif ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail") 
	{
		$arParams["PICTURES_SIGHT"]	= substr($arParams["PICTURES_SIGHT"], 5);
	}
}
if ($arParams["PICTURES_SIGHT"] != "real" && $arParams["PICTURES_SIGHT"] != "detail")
	$arParams["PICTURES_SIGHT"] = (in_array($arParams["PICTURES_SIGHT"], array_keys($arParams["PICTURES"])) ? $arParams["PICTURES_SIGHT"] : "standart");
//PAGENAVIGATION
$arNavParams = false; $arNavigation = false;
if ($arParams["PAGE_ELEMENTS"] > 0)
{
	CPageOption::SetOptionString("main", "nav_page_in_session", "N");
	$arNavParams = array("nPageSize"=>$arParams["PAGE_ELEMENTS"], "bDescPageNumbering"=>($arParams["USE_DESC_PAGE"] == "N" ? false : true));
	$arNavigation = CDBResult::GetNavParams($arNavParams);
}
$arParams["PERMISSION"] = "";
$arResult["I"] = array();
/********************************************************************
				Actions
********************************************************************/
if ($_REQUEST["detail_list_edit"] == "Y" && !empty($_REQUEST["ACTION"]) && !empty($_REQUEST["items"]))
{
	$arError = array();
	$bVarsFromForm = false;
	$bActionComplete = false;
	$BlockPerm = "D"; $bBadBlock = true;
	$arResult["GALLERY"] = array();
	$arResult["SECTION"] = array();
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"] > 0 ? $arParams["SECTION_ID"] : $_REQUEST["SECTION_ID"]);
	

	if(!check_bitrix_sessid()) // SESSION
	{
		$arError[] = array("code" => "100");
	}
	elseif ($arParams["SECTION_ID"] <= 0) // SECTION_ID must be
	{
		$arError[] = array("code" => "102");
	}
	else // This section is a part of iblock
	{
		CModule::IncludeModule("iblock");
		$db_res = CIBlockSection::GetList(array(), array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"ID" => $arParams["SECTION_ID"]));
		if ($db_res && $res = $db_res->Fetch())
			$arResult["SECTION"] = $res;
		if (empty($arResult["SECTION"]))
			$arError[] = array("code" => 102);
	}
	if (empty($arError) && $arParams["BEHAVIOUR"] == "USER") // Gallery
	{
		// GALLERY INFO
		$db_res = CIBlockSection::GetList(array(), array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]));
		if ($db_res && $res = $db_res->Fetch())
		{
			$arResult["GALLERY"] = $res;
			if ($arResult["GALLERY"]["LEFT_MARGIN"] >= $arResult["SECTION"]["LEFT_MARGIN"] || 
				$arResult["GALLERY"]["RIGHT_MARGIN"] <= $arResult["SECTION"]["RIGHT_MARGIN"]) 
				$arError[] = array("code" => "BAD_SECTION", "title" => GetMessage("P_SECTION_IS_NOT_IN_GALLERY"));
		}
		else
			$arError[] = array("code" => "104");
	}
	
	if (empty($arError)) // Get Permission
	{
		$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
		if ($arParams["PERMISSION"] < "R")
		{
		}
		else
		{
			if ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
				$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
			
			$arResult["I"]["ABS_PERMISSION"] = $arParams["PERMISSION"];
			
			if ($arParams["PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && $arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
				$arParams["PERMISSION"] = "W";
			$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];
		}
		
		if ($arParams["PERMISSION"] < "W")
			$arError[] = array("code" => "111");
	}
	if (empty($arError) && $_REQUEST["ACTION"] == "move")
	{
		$_REQUEST["TO_SECTION_ID"] = intVal($_REQUEST["TO_SECTION_ID"]);
		if ($_REQUEST["TO_SECTION_ID"] <= 0)
		{
			$arError[] = array(
				"code" => "BAD_SECTION_TO_MOVE",
				"title" => GetMessage("P_SECTION_EMPTY_TO_MOVE"));
		}
		elseif ($_REQUEST["TO_SECTION_ID"] == $arParams["SECTION_ID"])
		{
			$arError[] = array(
				"code" => "BAD_SECTION_TO_MOVE",
				"title" => GetMessage("P_SECTION_THIS_TO_MOVE"));
		}
		elseif ($arParams["BEHAVIOUR"] == "USER")
		{
			$arResult["SECTION_TO_MOVE"] = array();
			$db_res = CIBlockSection::GetList(array(), array(
				"ACTIVE" => "Y",
				"GLOBAL_ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_ACTIVE" => "Y",
				"ID" => $_REQUEST["TO_SECTION_ID"]));
			if ($db_res && $res = $db_res->Fetch())
			{
				$arResult["SECTION_TO_MOVE"] = $res;
				if ($arResult["GALLERY"]["LEFT_MARGIN"] >= $arResult["SECTION_TO_MOVE"]["LEFT_MARGIN"] || 
					$arResult["GALLERY"]["RIGHT_MARGIN"] <= $arResult["SECTION_TO_MOVE"]["RIGHT_MARGIN"]) 
					$arError[] = array("code" => "BAD_SECTION_TO_MOVE", "title" => GetMessage("P_SECTION_IS_NOT_IN_GALLERY"));
			}
			else
				$arError[] = array("code" => 102);
		}
	}
	$bClearCacheDetailAll = false;
	if (empty($arError))
	{
		$iFileSize = 0;
		$arErr = array();
		@set_time_limit(0);
		foreach ($_REQUEST["items"] as $itemID):
			$arFilter = array(
				"ID" => $itemID,
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"SECTION_ID" => $arParams["SECTION_ID"],
				"CHECK_PERMISSIONS" => "Y");
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
				"PROPERTY_REAL_PICTURE",
				"PROPERTY_PUBLIC_ELEMENT",
				"PROPERTY_BLOG_POST_ID",
				"PROPERTY_FORUM_TOPIC_ID");

			$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
			if($obElement = $rsElement->GetNextElement())
			{
				$arRes = $obElement->GetFields();
				$arRes["REAL_PICTURE"] = intVal($arRes["PROPERTY_REAL_PICTURE_VALUE"]);
				$arRes["PUBLIC_ELEMENT"] = ($arRes["PROPERTY_PUBLIC_ELEMENT_VALUE"] == "Y" ? "Y" : "N");
				$arRes["BLOG_POST_ID"] = intVal($arRes["PROPERTY_BLOG_POST_ID_VALUE"]);
				$arRes["FORUM_TOPIC_ID"] = intVal($arRes["PROPERTY_FORUM_TOPIC_ID_VALUE"]);
				if ($arRes["PUBLIC_ELEMENT"] == "Y")
				{
					$bClearCacheDetailAll = true;
				}
				if ($_REQUEST["ACTION"] == "drop")
				{
					$arRes["REAL_PICTURE"] = CFile::GetFileArray($arRes["REAL_PICTURE"]);
				}
			}

	   		if(empty($arRes))
	   		{
	   			$arErr[] = array(
	   				"code" => "103",
	   				"data" => array("ID" => $itemID));
	   			continue;
	   		}
	   		elseif ($arRes["IBLOCK_ID"] != $arParams["IBLOCK_ID"])
	   		{
	   			$arErr[] = array(
	   				"title" => GetMessage("P_BAD_IBLOCK_ID"),
	   				"code" => "BAD_IBLOCK_ID",
	   				"DATA" => $arRes);
	   			continue;
	   		}
	   		
			$DB->StartTransaction();
			$APPLICATION->ResetException();
			
			switch ($_REQUEST["ACTION"])
			{
				case "drop":
					if(!CIBlockElement::Delete($itemID))
					{
						$DB->Rollback();
						$sError = GetMessage("P_DELETE_ERROR");
						if($ex = $APPLICATION->GetException())
							$sError = $ex->GetString();
			   			$arErr[] = array(
			   				"code" => "NOT_DELETED",
			   				"title" => $sError,
			   				"DATA" => $arRes);
			   			continue;
					}
					$iFileSize += intVal($arRes["REAL_PICTURE"]["FILE_SIZE"]);
					if ($arRes["BLOG_POST_ID"] > 0)
					{
						CModule::IncludeModule("blog");
						$POST_ID = $arResult["BLOG_POST_ID"];
						$arPost = CBlogPost::GetByID($POST_ID);
						$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
						
						CBlogPost::Delete($POST_ID);
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$POST_ID."/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
						BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$POST_ID."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
					}
					
					if ($arRes["FORUM_TOPIC_ID"] > 0)
					{
						CModule::IncludeModule("forum");
						ForumDeleteTopic($arRes["FORUM_TOPIC_ID"]);
					}
								
					$DB->Commit();
					break;
				case "move":
					$arFields = Array(
						"MODIFIED_BY" => $USER->GetID(),
						"IBLOCK_SECTION" => $_REQUEST["TO_SECTION_ID"]);
					$bs = new CIBlockElement;
					$itemID = $bs->Update($itemID, $arFields);
					if($itemID <= 0)
					{
			   			$arErr[] = array(
			   				"ID" => $itemID,
			   				"code" => "NOT_UPDATED",
			   				"title" => $bs->LAST_ERROR,
			   				"DATA" => $arRes);
			   			continue;
					}
					$DB->Commit();
					break;
				default:
					$DB->Rollback();
					break;
			}
		endforeach;
		
		if ($arParams["BEHAVIOUR"] == "USER" && $arParams["GALLERY_SIZE"] > 0)
		{
			$bs = new CIBlockSection;
			$arFields = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
				"UF_GALLERY_SIZE" => intVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]) - $iFileSize);
			$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
			$res = $bs->Update($arResult["GALLERY"]["ID"], $arFields, false, false);
		}

		$nameSpace = "bitrix";
		$pthToComponent = strToLower(trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/")));
		$tmpPathThis = strToLower(trim(preg_replace("'[\\\\/]+'", "/", __FILE__)));
		$tmpPathThis = str_replace($pthToComponent, "", $tmpPathThis);
		$arPath = explode("/", $tmpPathThis);
		if (!empty($arPath[0]))
			$nameSpace = $arPath[0];

		$arComponentPath = array(
			$nameSpace.":search.page",
			$nameSpace.":search.tags.cloud");
			
		foreach ($arComponentPath as $path)
		{
			$componentRelativePath = CComponentEngine::MakeComponentPath($path);
			if (StrLen($componentRelativePath) > 0)
			{
				$arComponentDescription = CComponentUtil::GetComponentDescr($path);
				if (IsSet($arComponentDescription) && is_array($arComponentDescription))
				{
					if (array_key_exists("CACHE_PATH", $arComponentDescription))
					{
						if ($arComponentDescription["CACHE_PATH"] == "Y")
							$arComponentDescription["CACHE_PATH"] = "/".SITE_ID.$componentRelativePath;
						if (StrLen($arComponentDescription["CACHE_PATH"]) > 0)
							BXClearCache(true, $arComponentDescription["CACHE_PATH"]);
					}
				}
			}
		}
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arParams["SECTION_ID"]."/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/0/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arParams["SECTION_ID"]."/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".intVal($arResult["GALLERY"]["ID"])."/");
		
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arParams["SECTION_ID"]."/");
		BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$arParams["SECTION_ID"]."/");
		if ($_REQUEST["ACTION"] == "move")
			BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$_REQUEST["TO_SECTION_ID"]."/");
		if ($bClearCacheDetailAll || $arParams["BEHAVIOUR"] != "USER")
			BXClearCache(true, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/all/");
	}
	
	if (!empty($arError))
	{
		$bVarsFromForm = true;
		$res = array_pop($arError);
		$arResult["ERROR_MESSAGE"] = PhotoShowError($res);
	}
	elseif (!empty($arErr))
	{
		$bVarsFromForm = true;
		foreach ($arErr as $res)
			$arResult["ERROR_MESSAGE"] .= PhotoShowError($res)."\n";
	}
	elseif (empty($arError) && empty($arErr) && !empty($_REQUEST["REDIRECT_URL"]))
	{
		LocalRedirect($_REQUEST["REDIRECT_URL"]);
	}
	$arResult["bVarsFromForm"] = ($bVarsFromForm ? "Y" : "N");
	$arResult["ERROR_MESSAGE"] = $arResult["ERROR_MESSAGE"];
}
/********************************************************************
				/Actions
********************************************************************/
$arResult["SECTION"] = array();
$arResult["GALLERY"] = array();
$arResult["SECTIONS_CNT"] = 0;
$arParams["PERMISSION"] = "";
$arResult["I"] = array();
/********************************************************************
				Get data from cache
********************************************************************/
/********************************************************************
				SECTION
********************************************************************/
if ($arParams["SECTION_ID"] > 0 || !empty($arParams["SECTION_CODE"]))
{
	$arCacheParams = array(
		"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arParams["SECTION_ID"],
		"SECTION_CODE" => $arParams["SECTION_CODE"],
		"BEHAVIOUR" => $arParams["BEHAVIOUR"],
		"USER_ALIAS" => $arParams["USER_ALIAS"]);
	$cache = new CPHPCache;
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
		$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
		if ($arParams["PERMISSION"] < "R"):
			ShowError(GetMessage("P_DENIED_ACCESS"));
			return 0;
		endif;
		
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y");
		
		// GALLERY INFO
		if ($arParams["BEHAVIOUR"] == "USER")
		{
			$db_res = CIBlockSection::GetList(array(), array(
				"ACTIVE" => "Y",
				"GLOBAL_ACTIVE" => "Y",
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_ACTIVE" => "Y",
				"CODE" => $arParams["USER_ALIAS"],
				"SECTION_ID" => 0), false, array("UF_DATE", "UF_GALLERY_SIZE", "UF_DEFAULT"));
	
			if ($db_res && $res = $db_res->GetNext())
				$arResult["GALLERY"] = $res;
			if (empty($arResult["GALLERY"]))
			{
				ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
				return 0;
			}
			if ($arParams["SECTION_CODE"] != $arResult["GALLERY"]["CODE"] && 
				$arParams["SECTION_ID"] != $arResult["GALLERY"]["ID"])
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
				$arResult["SECTION"]["DATE"] = CIBlockFormatProperties::DateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["SECTION"]["~DATE"]["VALUE"], CSite::GetDateFormat()));
				
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

	$arResult["I"] = array("ABS_PERMISSION" => $arParams["PERMISSION"]);
	if ($arResult["I"]["ABS_PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
		$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
		$arParams["PERMISSION"] = "W";
	$arResult["I"]["PERMISSION"] = $arParams["PERMISSION"];
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
	if (!$arParams["PASSWORD_CHECKED"])
	{
		return 0;
	}
}
/********************************************************************
				ELEMENT LIST
********************************************************************/
$arResult["ELEMENTS_LIST"] = array();
$arCacheParams = array(
	"USER_GROUP" => $GLOBALS["USER"]->GetGroups(),
	"PICTURES_SIGHT" => $arParams["PICTURES_SIGHT"],
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => $arParams["SECTION_ID"],
	"PAGE_ELEMENTS" => $arParams["PAGE_ELEMENTS"],
	"ELEMENTS_LAST_TYPE" => $arParams["ELEMENTS_LAST_TYPE"],
	"ELEMENTS_LAST_COUNT" => $arParams["ELEMENTS_LAST_COUNT"],
	"ELEMENTS_LAST_TIME" => $arParams["ELEMENTS_LAST_TIME"],
	"ELEMENTS_LAST_TIME_FROM" => $arParams["ELEMENTS_LAST_TIME_FROM"],
	"ELEMENTS_LAST_TIME_TO" => $arParams["ELEMENTS_LAST_TIME_TO"],
	"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
	"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
	"ELEMENT_SORT_FIELD1" => $arParams["ELEMENT_SORT_FIELD1"],
	"ELEMENT_SORT_ORDER1" => $arParams["ELEMENT_SORT_ORDER1"],
	"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
	"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
	"NAV1" => $arNavParams,
	"NAV2" => $arNavigation,
	"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
	"BEHAVIOUR" => $arParams["BEHAVIOUR"],
	"USER_ALIAS" => $arParams["USER_ALIAS"],
	"GET_GALLERY_INFO" => $arParams["GET_GALLERY_INFO"]);

$cache = new CPHPCache;
$cache_id = "elements_list_".serialize($arCacheParams);
$cache_path = "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".(intVal($arParams["SECTION_ID"]) > 0 ? $arParams["SECTION_ID"] : "all")."/";
if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$res = $cache->GetVars();
	if (is_array($res["ELEMENTS_LIST"]))
	{
		$arResult["ELEMENTS_LIST"] = $res["ELEMENTS_LIST"];
		$arParams["PERMISSION"] = $res["PERMISSION"];
		$arResult["USER_HAVE_ACCESS"] = $res["USER_HAVE_ACCESS"];
		$arResult["ELEMENTS"]["MAX_WIDTH"] = $res["MAX_WIDTH"];
		$arResult["ELEMENTS"]["MAX_HEIGHT"] = $res["MAX_HEIGHT"];
		$arResult["NAV_STRING"] = $res["NAV_STRING"];
		$arResult["NAV_RESULT"] = $res["NAV_RESULT"];
	}
}
if (!is_array($arResult["ELEMENTS_LIST"]) || empty($arResult["ELEMENTS_LIST"]))
{
	CModule::IncludeModule("iblock");
	
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;

	$bUSER_HAVE_ACCESS = !$arParams["USE_PERMISSIONS"];
	if($arParams["USE_PERMISSIONS"] && isset($GLOBALS["USER"]) && is_object($GLOBALS["USER"]))
	{
		$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
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
	$arResult["USER_HAVE_ACCESS"] = ($bUSER_HAVE_ACCESS ? "Y" : "N");
	
	$arSection = array();
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
		"SHOW_COUNTER");
	if (strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE" != "DETAIL_PICTURE" && 
		strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE" != "PREVIEW_PICTURE" && 
		strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE" != "STANDART_PICTURE")
	{
		$arSelect[] = "PROPERTY_".strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE";
	}
	if ($arParams["SHOW_RATING"] == "Y")
	{
		$arSelect[] = "PROPERTY_vote_count";
		$arSelect[] = "PROPERTY_vote_sum";
		$arSelect[] = "PROPERTY_RATING";
	}
	if ($arParams["SHOW_COMMENTS"] == "Y")
	{
		if ($arParams["COMMENTS_TYPE"] == "FORUM")
			$arSelect[] = "PROPERTY_FORUM_MESSAGE_CNT";
		elseif ($arParams["COMMENTS_TYPE"] == "BLOG")
			$arSelect[] = "PROPERTY_BLOG_COMMENTS_CNT";
	}
	
	//WHERE
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y");

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
		$arSelect[] = $arParams["ELEMENT_SORT_FIELD"];
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
		$arSelect[] = $arParams["ELEMENT_SORT_FIELD1"];
	}
	if ($arParams["ELEMENT_SORT_FIELD"] != "ID")
		$arSort["ID"] = "ASC";

	$maxWidth = 0;
	$maxHeight = 0;
	$arElements = array();
	if ($arParams["SECTION_ID"] > 0)
		$arFilter["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	else
	{
		$arMargin = array();
		
		$arrFilter = $arFilter;
		$res = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION");
		
		if (is_array($res) && !empty($res["UF_PASSWORD"]))
		{
			$arrFilter["!=UF_PASSWORD"] = "";
			$db_res = CIBlockSection::GetList(Array(), $arrFilter);
			if ($db_res && $res = $db_res->Fetch())
			{
				do 
				{
					$arMargin[] = array($res["LEFT_MARGIN"], $res["RIGHT_MARGIN"]);
				}while ($res = $db_res->Fetch());
			}
			if (count($arMargin) > 0)
				$arFilter["!SUBSECTION"] = $arMargin;
		}
	}
	if ($arParams["ELEMENT_LAST_TYPE"] == "count" && $arParams["ELEMENTS_LAST_COUNT"] > 0)
	{
		//EXECUTE
		$rsElement = CIBlockElement::GetList(array("ID" => "DESC"), $arFilter, false, array("nTopCount" => $arParams["ELEMENTS_LAST_COUNT"]), array("ID"));
		
		while($arElement = $rsElement->GetNext())
			$iLastID = intVal($arElement["ID"]);
		if ($iLastID > 0)
			$arFilter[">=ID"] = $iLastID;
	}
	elseif ($arParams["ELEMENT_LAST_TYPE"] == "time" && $arParams["ELEMENTS_LAST_TIME"] > 0)
	{
		$arFilter[">=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), (time()-($arParams["ELEMENTS_LAST_TIME"]*3600*24)));
	}
	elseif ($arParams["ELEMENT_LAST_TYPE"] == "period" && (strLen($arParams["ELEMENTS_LAST_TIME_FROM"]) > 0 || strLen($arParams["ELEMENTS_LAST_TIME_TO"]) > 0))
	{
		if (strLen($arParams["ELEMENTS_LAST_TIME_FROM"]) > 0)
			$arFilter[">=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), MakeTimeStamp($arParams["ELEMENTS_LAST_TIME_FROM"]));
		if (strLen($arParams["ELEMENTS_LAST_TIME_TO"]) > 0)
			$arFilter["<=DATE_CREATE"] = date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)), MakeTimeStamp($arParams["ELEMENTS_LAST_TIME_TO"]));
	}
	
	if (!empty($arParams["ELEMENT_FILTER"]))
		$arFilter = array_merge($arParams["ELEMENT_FILTER"], $arFilter);
		
	// EbK Iblock < 7.0.7
	$arSelect = array_keys(array_flip(array_diff($arSelect, array_keys($arSort))));
	//EXECUTE
	$rsElements = CIBlockElement::GetList($arSort, $arFilter, false, $arNavParams, $arSelect);
	if (!empty($rsElements))
	{
		$arResult["NAV_STRING"] = $rsElements->GetPageNavStringEx($navComponentObject, GetMessage("P_PHOTOS"), $arParams["PAGE_NAVIGATION_TEMPLATE"]);
		$arResult["NAV_RESULT"] = $rsElements;
		
		$arGallery = array();
		$arSection = array();
		
		while($obElement = $rsElements->GetNextElement())
		{
			$arElement = $obElement->GetFields();
			$arElement["PROPERTIES"] = array();
			foreach ($arElement as $key => $val)
			{
				if ((substr($key, 0, 9) == "PROPERTY_" && substr($key, -6, 6) == "_VALUE"))
				{
					$arElement["PROPERTIES"][substr($key, 9, intVal(strLen($key)-15))] = array("VALUE" => $val);
					$arElement["PROPERTIES"][strToLower(substr($key, 9, intVal(strLen($key)-15)))] = array("VALUE" => $val);
				}
			}
			
			if ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"]) && $arParams["GET_GALLERY_INFO"] == "Y")
			{
				$res = array();
				if (empty($arSection[$arElement["IBLOCK_SECTION_ID"]])) // Get Section Info
				{
					$db_res = CIBlockSection::GetList(array(), array(
						"ID" => $arElement["IBLOCK_SECTION_ID"]));
					if ($db_res && $res = $db_res->Fetch())
						$arSection[$arElement["IBLOCK_SECTION_ID"]] = $res;
				}
				if (empty($arGallery[$arElement["IBLOCK_SECTION_ID"]])) // Get Gallery Info
				{
					$db_res = CIBlockSection::GetList(array(), array(
						"ACTIVE" => "Y",
						"GLOBAL_ACTIVE" => "Y",
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"IBLOCK_ACTIVE" => "Y",
						"SECTION_ID" => 0,
						"!LEFT_MARGIN" => $arSection[$arElement["IBLOCK_SECTION_ID"]]["LEFT_MARGIN"],
						"!RIGHT_MARGIN" => $arSection[$arElement["IBLOCK_SECTION_ID"]]["RIGHT_MARGIN"], 
						"!ID" => $arElement["IBLOCK_SECTION_ID"]));
					if ($db_res && $res = $db_res->Fetch())
					{
						if (intVal($res["PICTURE"]) > 0)
						{
							$res["~PICTURE"] = $res["PICTURE"];
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
							$image_resize = CFile::ResizeImageGet($res["PICTURE"], array("width" => 50, "height" => 50));
							$res["PICTURE"]["SRC"] = $image_resize["src"];
						}
						$res["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
							array("USER_ALIAS" => $res["CODE"], "USER_ID" => $res["CREATED_BY"], "GROUP_ID" => $res["SOCNET_GROUP_ID"]));
						$res["URL"] = htmlspecialchars($res["~URL"]);
				
						$arGallery[$arElement["IBLOCK_SECTION_ID"]] = $res;
					}
				}
				$arElement["GALLERY"] = $arGallery[$arElement["IBLOCK_SECTION_ID"]];
			}
			$arElements[] = $arElement;
		}
		
		foreach ($arElements as $key => $arElement)
		{
			//URL
			$arElement["~URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
				array("USER_ALIAS" => (!empty($arElement["GALLERY"]["CODE"]) ? $arElement["GALLERY"]["CODE"] : $arParams["USER_ALIAS"]), 
					"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" =>$arElement["ID"],
					"USER_ID" => $arElement["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arElement["GALLERY"]["SOCNET_GROUP_ID"]));
			$arElement["URL"] = htmlSpecialChars($arElement["~URL"]);
			
			$arElement["~SLIDE_SHOW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"], 
				array("USER_ALIAS" => (!empty($arElement["GALLERY"]["CODE"]) ? $arElement["GALLERY"]["CODE"] : $arParams["USER_ALIAS"]), 
					"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"], "ELEMENT_ID" =>$arElement["ID"], 
					"USER_ID" => $arElement["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arElement["GALLERY"]["SOCNET_GROUP_ID"]));
			$arElement["SLIDE_SHOW_URL"] = htmlSpecialChars($arElement["~SLIDE_SHOW_URL"]);
			
			//PICTURE
			if ($arParams["PICTURES_SIGHT"] == "detail" && !empty($arElement["DETAIL_PICTURE"]))
				$arElement["~PICTURE"] = $arElement["DETAIL_PICTURE"];
			elseif (!empty($arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"]))
				$arElement["~PICTURE"] = $arElement["PROPERTIES"][strToUpper($arParams["PICTURES_SIGHT"])."_PICTURE"]["VALUE"];
			else 
				$arElement["~PICTURE"] = $arElement["PREVIEW_PICTURE"];
			$arElement["PICTURE"] = CFile::GetFileArray($arElement["~PICTURE"]);
			$maxWidth = max($maxWidth, $arElement["PICTURE"]["WIDTH"]);
			$maxHeight = max($maxHeight, $arElement["PICTURE"]["HEIGHT"]);
			//TAGS
			$arElement["TAGS_LIST"] = array();
			if (!empty($arElement["TAGS"]))
			{
				if (CModule::IncludeModule("search"))
				{
					$ar = tags_prepare($arElement["TAGS"], SITE_ID);
					if (!empty($ar))
					{
						foreach ($ar as $name => $tags)
						{
							$arr = array(
								"TAG_NAME" => $tags,
								"~TAGS_URL" => CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()));

							if (strpos($arr["~TAGS_URL"], "?") === false)
								$arr["~TAGS_URL"] .= "?";
							else 
								$arr["~TAGS_URL"] .= "&";
							$arr["~TAGS_URL"] .= "tags=".$tags;
							$arr["TAGS_URL"] = htmlSpecialChars($arr["~TAGS_URL"]);
							$arr["TAGS_NAME"] = $tags;
							$arElement["TAGS_LIST"][] = $arr;
						}
					}
				}
			}
			
			if (!empty($arElement["DATE_CREATE"]))
			{
				$arElement["DATE_CREATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arElement["DATE_CREATE"], CSite::GetDateFormat()));
			}
			
			$arElements[$key] = $arElement;
		}
	}
	
	$arResult["ELEMENTS_LIST"] = $arElements;
	$arResult["ELEMENTS"]["MAX_WIDTH"] = $maxWidth;
	$arResult["ELEMENTS"]["MAX_HEIGHT"] = $maxHeight;
	
	if ($arParams["CACHE_TIME"] > 0)
	{
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		$cache->EndDataCache(
			array(
				"ELEMENTS_LIST" => $arResult["ELEMENTS_LIST"],
				"PERMISSION" => $arParams["PERMISSION"],
				"USER_HAVE_ACCESS" => $arResult["USER_HAVE_ACCESS"],
				"MAX_WIDTH" => $arResult["ELEMENTS"]["MAX_WIDTH"],
				"MAX_HEIGHT" => $arResult["ELEMENTS"]["MAX_HEIGHT"],
				"NAV_STRING" => $arResult["NAV_STRING"], 
				"NAV_RESULT" => $arResult["NAV_RESULT"]));
	}
}
/********************************************************************
				Get data from cache
********************************************************************/
if ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
if ($arParams["PERMISSION"] < "W" && !empty($arResult["I"]))
	$arParams["PERMISSION"] = $arResult["I"]["PERMISSION"];

if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;

$arResult["~SLIDE_SHOW"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_SLIDE_SHOW_URL"], array(
	"USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arParams["SECTION_ID"], "ELEMENT_ID" => 0, 
	"USER_ID" => $arResult["GALLERY"]["CREATED_BY"], "GROUP_ID" => $arResult["GALLERY"]["SOCNET_GROUP_ID"]));
if (strpos($arResult["~SLIDE_SHOW"], "?") === false)
	$arResult["~SLIDE_SHOW"] .= "?";
$arResult["~SLIDE_SHOW"] .= "&BACK_URL=".urlencode($GLOBALS['APPLICATION']->GetCurPageParam());
$arResult["SLIDE_SHOW"] = htmlSpecialChars($arResult["~SLIDE_SHOW"]);
// *****************************************************************************************
IncludeAJAX();

if($arParams["SET_TITLE"] == "Y")
	$APPLICATION->SetTitle(GetMessage("P_LIST_PHOTO"));
$this->IncludeComponentTemplate();
if (count($arResult["ELEMENTS_LIST"]) == 1)
	return $arResult["ELEMENTS_LIST"][0]["ID"];
?>