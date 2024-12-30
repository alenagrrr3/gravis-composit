<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;

// **************************************************************************************
if(!function_exists("__UnEscape"))
{
	function __UnEscape(&$item, $key)
	{
		if(is_array($item))
			array_walk($item, '__UnEscape');
		else
		{
			if(strpos($item, "%u") !== false)
				$item = $GLOBALS["APPLICATION"]->UnJSEscape($item);
		}
	}
}
// **************************************************************************************
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);	
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	
	$arParams["ACTION"] = (empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);
	
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"sections_top" => "",
			"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
			"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
				"&SECTION_ID=#SECTION_ID#");
		
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array(
				"PAGE_NAME", "USER_ALIAS", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "DROP_PASSWORD", "save_edit", "sessid", "edit"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
	$arParams["DATE_TIME_FORMAT"] = trim(!empty($arParams["DATE_TIME_FORMAT"]) ? $arParams["DATE_TIME_FORMAT"] : $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("SHORT")));
	$arParams["SHOW_PHOTO_USER"] = ($arParams["SHOW_PHOTO_USER"] == "Y" ? "Y" : "N");// hidden params for custom components
	$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
/***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default params
********************************************************************/
	$strWarning = "";
	$arIBTYPE = false;
	$bBadBlock = true;
	$bVarsFromForm = false;
	$arResult["SECTION"] = array();
	$arResult["GALLERY"] = array();
	$arParams["PERMISSION"] = "";
	$arResult["I"] = array();
	if ($arParams["AJAX_CALL"] == "Y")
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
	}
/********************************************************************
				/Default params
********************************************************************/
/********************************************************************
				Get data from cache
********************************************************************/
$cache = new CPHPCache;
/********************************************************************
				GALLERY
********************************************************************/
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
				$image_resize = CFile::ResizeImageGet($res["PICTURE"], 
					array("width" => $arParams["GALLERY_AVATAR_SIZE"], "height" => $arParams["GALLERY_AVATAR_SIZE"]));
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
				/Get data from cache
********************************************************************/
// Check permisstion
$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && $arParams["BEHAVIOUR"] == "USER" && 
	$arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId() && $GLOBALS["USER"]->IsAuthorized())
	$arParams["PERMISSION"] = "W";

if ($arParams["PERMISSION"] < "W"):
	if ($arParams["AJAX_CALL"] == "Y"):
		$APPLICATION->RestartBuffer();
		ShowError(GetMessage("P_ACCESS_DENIED"));
		die();
	else:
		ShowError(GetMessage("P_ACCESS_DENIED"));
	endif;
	
	return 0;
endif;
// IBlockSection
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
if (!$arResult["SECTION"] && ($arParams["ACTION"] != "NEW"))
{
	ShowError(GetMessage("P_SECTION_NOT_FOUND"));
	if ($arParams["AJAX_CALL"] == "Y")
		die();
	return 0;
}
	
$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", 
	$arResult["SECTION"]["ID"], LANGUAGE_ID);
if (empty($arUserFields) || empty($arUserFields["UF_DATE"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_DATE"));
	if (!$db_res || !($res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
			"FIELD_NAME" => "UF_DATE",
			"USER_TYPE_ID" => "datetime",
			"MULTIPLE" => "N",
			"MANDATORY" => "N",
		);
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = "Date";
			if ($arLanguage["LID"] == 'ru')
				$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_DATE");
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arResult["SECTION"]["ID"], LANGUAGE_ID);
}
$arResult["SECTION"]["~DATE"] = $arUserFields["UF_DATE"];

if (empty($arUserFields) || empty($arUserFields["UF_PASSWORD"]))
{
	$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_PASSWORD"));
	if (!$db_res || !($res = $db_res->GetNext()))
	{
		$arFields = Array(
			"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
			"FIELD_NAME" => "UF_PASSWORD",
			"USER_TYPE_ID" => "string",
			"MULTIPLE" => "N",
			"MANDATORY" => "N",
		);
		$arFieldName = array();
		$rsLanguage = CLanguage::GetList($by, $order, array());
		while($arLanguage = $rsLanguage->Fetch()):
			$arFieldName[$arLanguage["LID"]] = "Password";
			if ($arLanguage["LID"] == 'ru')
				$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_PASSWORD");
		endwhile;
		$arFields["EDIT_FORM_LABEL"] = $arFieldName;
		$obUserField  = new CUserTypeEntity;
		$obUserField->Add($arFields);
		$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
	}
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arResult["SECTION"]["ID"], LANGUAGE_ID);
}
$arResult["SECTION"]["~PASSWORD"] = $arUserFields["UF_PASSWORD"];

/********************************************************************
				Actions
********************************************************************/
if ($_REQUEST["edit"] == "cancel")
{
	LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"])));
}
elseif($_REQUEST["save_edit"] == "Y" || $_REQUEST["edit"] == "Y")
{
	array_walk($_REQUEST, '__UnEscape');
	
	if(!(check_bitrix_sessid()))
	{
		$strWarning = GetMessage("IBLOCK_WRONG_SESSION")."<br>";
		$bVarsFromForm = true;
	}
	else
	{
		if (($arParams["ACTION"] != "NEW") && ($arParams["ACTION"] != "DROP")):
			$arFields = Array(
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"]);

			if (isset($_REQUEST["UF_DATE"]))
			{
				$arFields["UF_DATE"] = $_REQUEST["UF_DATE"];
				$arFields["DATE"] = $_REQUEST["UF_DATE"];
			}
			
			if (isset($_REQUEST["NAME"]))
				$arFields["NAME"] = $_REQUEST["NAME"];

			if (isset($_REQUEST["DESCRIPTION"]))
				$arFields["DESCRIPTION"] = $_REQUEST["DESCRIPTION"];

			if (isset($_REQUEST["ACTIVE"]))
				$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];
				
			if ($_REQUEST["DROP_PASSWORD"] == "Y")
			{
				$arFields["UF_PASSWORD"] = "";
				$GLOBALS["UF_PASSWORD"] = "";
			}
			elseif ($_REQUEST["USE_PASSWORD"] == "Y")
			{
				$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
				$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			}
			else 
			{
				$arFields["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
				$GLOBALS["UF_PASSWORD"] = $arResult["SECTION"]["~PASSWORD"]["VALUE"];
			}
			foreach ($_REQUEST as $key => $val)
			{
				if (substr($key, 0, 3) == "UF_")
				{
					$GLOBALS[$key] = $val;
				}
			}
			
			$bs = new CIBlockSection;
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
			$res = $bs->Update($arResult["SECTION"]["ID"], $arFields);
			if(!$res)
			{
				$strWarning .= $bs->LAST_ERROR;
				if (empty($strWarning))
				{
					$err = $GLOBALS['APPLICATION']->GetException();
					if ($err)
						$strWarning .= $err->GetString();
				}
				$bVarsFromForm = true;
			}
			else
			{
				$rsSection = CIBlockSection::GetList(Array(), $arFilter, false, array("UF_DATE", "UF_PASSWORD"));
				$arResultSection = $rsSection->GetNext();
				$arResultFields = Array(
					"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
					"DATE"=>PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResultSection["UF_DATE"], CSite::GetDateFormat())),
					"PASSWORD" => $arResultSection["UF_PASSWORD"],
					"NAME"=>$arResultSection["NAME"],
					"DESCRIPTION"=>$arResultSection["DESCRIPTION"],
					"ID" => $arResult["SECTION"]["ID"],
					"error" => "");
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
			}
		elseif ($arParams["ACTION"] == "NEW"):
			$arFields = Array(
				"ACTIVE" => "Y",
				"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
				"DATE"=>$_REQUEST["UF_DATE"],
				"UF_DATE"=>$_REQUEST["UF_DATE"],
				"NAME"=>$_REQUEST["NAME"],
				"DESCRIPTION"=>$_REQUEST["DESCRIPTION"]);
			if (isset($_REQUEST["ACTIVE"]))
				$arFields["ACTIVE"] = $_REQUEST["ACTIVE"];
				
			if ($arParams["BEHAVIOUR"] == "USER")
			{
				if ($_REQUEST["IBLOCK_SECTION_ID"] > 0)
				{
					$db_res = CIBlockSection::GetByID($_REQUEST["IBLOCK_SECTION_ID"]);
					if ($db_res && $res = $db_res->Fetch())
					{
						if ($res["LEFT_MARGIN"] > $arResult["GALLERY"]["LEFT_MARGIN"] && 
							$res["RIGHT_MARGIN"] < $arResult["GALLERY"]["RIGHT_MARGIN"])
						$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
					}
				}
				if (empty($arFields["IBLOCK_SECTION_ID"]))
				{
					$arFields["IBLOCK_SECTION_ID"] = $arResult["GALLERY"]["ID"];
				}
			}
			elseif (intVal($_REQUEST["IBLOCK_SECTION_ID"]) > 0)
			{
				$arFields["IBLOCK_SECTION_ID"] = $_REQUEST["IBLOCK_SECTION_ID"];
			}

			if (!empty($_REQUEST["PASSWORD"]))
			{
				$arFields["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
				$GLOBALS["UF_PASSWORD"] = md5($_REQUEST["PASSWORD"]);
			}

			$bs = new CIBlockSection();
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
			$ID = $bs->Add($arFields);
			if($ID <= 0)
			{
				$strWarning .= $bs->LAST_ERROR;
				if (empty($strWarning))
				{
					$err = $GLOBALS['APPLICATION']->GetException();
					if ($err)
						$strWarning .= $err->GetString();
				}
				$bVarsFromForm = true;
			}
			else
			{
				$rsSection = CIBlockSection::GetList(Array(), array("ID" => $ID), false);
				$arResultSection = $rsSection->GetNext();
				$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $ID));
				$arResultFields = Array(
					"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
					"DATE"=>PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($_REQUEST["UF_DATE"], CSite::GetDateFormat())),
					"NAME"=>$arResultSection["NAME"],
					"DESCRIPTION"=>$arResultSection["DESCRIPTION"],
					"PASSWORD" => $arResultSection["UF_PASSWORD"],
					"ID" => $ID,
					"error" => "",
					"url" => $arResult["URL"]);
				
			}
		elseif ($arParams["ACTION"] == "DROP"):
			@set_time_limit(0);
			if ($arParams["BEHAVIOUR"] == "USER")
			{
				$arFilesID = array();
				$iFileSize = 0;
				$arFilter = array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"SUBSECTION" => array(array($arResult["SECTION"]["LEFT_MARGIN"], $arResult["SECTION"]["RIGHT_MARGIN"])));
					
				$db_res = CIBlockElement::GetList(array("ID" => "ASC"), $arFilter, false, false, array("PROPERTY_REAL_PICTURE"));
				if ($db_res && $res_element = $db_res->GetNextElement())
				{
					do
					{
						$res = $res_element->GetFields();
						if (intVal($res["PROPERTY_REAL_PICTURE_VALUE"]) > 0)
							$arFilesID[] = $res["PROPERTY_REAL_PICTURE_VALUE"];
					}while ($res_element = $db_res->GetNextElement());
				}
				if (!empty($arFilesID))
				{
					$db_res = CFile::GetList(array(), array("@ID" => implode(",", $arFilesID)));
					while ($res = $db_res->Fetch()) 
					{
						$iFileSize += doubleVal($res["FILE_SIZE"]);
					}
				}
				
				$arFields = array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
					"UF_GALLERY_SIZE" => (doubleVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]) - $iFileSize));
				$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];
				$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
				$bs = new CIBlockSection;
				$bs->Update($arResult["GALLERY"]["ID"], $arFields);
			}
			
			if(!CIBlockSection::Delete($arResult["SECTION"]["ID"]))
			{
				if($e = $APPLICATION->GetException())
					$strWarning .= $e->GetString();
				else
					$strWarning .= GetMessage("IBSEC_A_DELERR_REFERERS");
			}
			else
			{
				// Must Be deleted
				CIBlockSection::ReSort($arParams["IBLOCK_ID"]);
				// /Must Be deleted
				if ($arParams["BEHAVIOUR"] == "USER" && intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) == intVal($arResult["GALLERY"]["ID"]))
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"]));
				elseif (intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
				else
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTIONS_TOP_URL"], 
					array());
				$arResultFields = Array(
					"ID" => $arResult["SECTION"]["ID"],
					"error" => "",
					"url" => $arResult["URL"]);
				
			}
		endif;
		
		if (!$bVarsFromForm)
		{
			// Must Be deleted
			CIBlockSection::ReSort($arParams["IBLOCK_ID"]);
			// /Must Be deleted
			
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

			if ($arParams["BEHAVIOUR"] == "USER" && ($arParams["ACTION"] == "DROP" || $arParams["ACTION"] == "NEW"))
			{
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/");
			}
			else 
			{
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/user/data/".$arResult["GALLERY"]["CREATED_BY"]."/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/gallery/".$arParams["USER_ALIAS"]."/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arSection["ID"]."/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/0/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arSection["ID"]."/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arSection["ID"]."/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/all/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$arSection["ID"]."/");
			}
			
			if ($arParams["AJAX_CALL"] == "Y")
			{
				$APPLICATION->RestartBuffer();
				?><?=CUtil::PhpToJSObject($arResultFields);?><?
				die();
			}
			else 
			{
				LocalRedirect($arResult["URL"]);
			}
		}
	}
}
	$arResult["bVarsFromForm"] = false;
	if ($arParams["ACTION"] != "NEW")
	{
		$arResult["FORM"]["ACTIVE"] = $arResult["SECTION"]["ACTIVE"];
		$arResult["FORM"]["NAME"] = htmlspecialcharsEx($arResult["SECTION"]["~NAME"]);
		$arResult["FORM"]["DESCRIPTION"] = htmlspecialcharsEx($arResult["SECTION"]["~DESCRIPTION"]);
		$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
		$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
	}
	else 
	{
		$arResult["FORM"]["ACTIVE"] = "";
		$arResult["FORM"]["NAME"] = "";
		$arResult["FORM"]["DESCRIPTION"] = "";
		$arResult["FORM"]["IBLOCK_SECTION_ID"] = $arResult["SECTION"]["ID"];
		$arResult["FORM"]["~DATE"] = $arResult["SECTION"]["~DATE"];
		$arResult["FORM"]["~DATE"]["VALUE"] = GetTime(time());
		$arResult["FORM"]["~PASSWORD"] = $arResult["SECTION"]["~PASSWORD"];
		$arResult["FORM"]["~PASSWORD"]["VALUE"] = "";
	}
	
	if ($bVarsFromForm)
	{
		$arResult["bVarsFromForm"] = true;
		$arResult["FORM"]["ACTIVE"] = ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N");
		$arResult["FORM"]["NAME"] = htmlSpecialChars($_REQUEST["NAME"]);
		$arResult["FORM"]["DESCRIPTION"] = htmlSpecialChars($_REQUEST["DESCRIPTION"]);
		$arResult["FORM"]["DATE"] = $arResult["SECTION"]["~DATE"];
		$arResult["FORM"]["DATE"]["VALUE"] =  htmlSpecialChars($_REQUEST["UF_DATE"]);
	}
	
	$arResult["ERROR_MESSAGE"] = $strWarning;
	
	if($arParams["SET_TITLE"] == "Y")
	{
		if ($arParams["ACTION"] == "NEW")
			$APPLICATION->SetTitle(GetMessage("IBLOCK_NEW"));
		else
			$APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);
	}
	
	$rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arParams["SECTION_ID"]);
	while($arPath=$rsPath->GetNext())
	{
		if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
			continue;
		
		$arPath["SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"]));

		if ($arPath["ID"] != $arParams["SECTION_ID"])
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"], $arPath["SECTION_PAGE_URL"]);
		else
			$GLOBALS["APPLICATION"]->AddChainItem($arPath["NAME"]);
	}
	
	if (intVal($arResult["SECTION"]["ID"]) > 0)
	{
		$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
	}
	elseif ($arParams["BEHAVIOUR"] == "USER")
	{
		$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["GALLERY_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"]));
	}
	else 
	{
		$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["SECTIONS_TOP_URL"], array());
	}
	$this->IncludeComponentTemplate();
	
if ($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());

?>