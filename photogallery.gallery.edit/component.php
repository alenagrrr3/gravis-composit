<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
// To remember: only active user $USER can create new photogallery
if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
elseif (!$GLOBALS["USER"]->IsAuthorized()):
	CModule::IncludeModule("photogallery");
	ShowError(PhotoShowError(array("code" => 110)));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["GALLERY_ID"] = intVal($_REQUEST["GALLERY_ID"]);
	$arParams["SOCNET_GROUP_ID"] = intVal($arParams["SOCNET_GROUP_ID"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = (in_array($arParams["ACTION"], array("CREATE", "EDIT", "DROP")) ? $arParams["ACTION"] : "CREATE");
	$arParams["ACTION"] = (!empty($arParams["USER_ALIAS"]) || !empty($arParams["GALLERY_ID"]) ? $arParams["ACTION"] : "CREATE");
	
	$arParams["USER_ID"] = intVal($GLOBALS["USER"]->GetId());
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
		"index" => "",
		"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"gallery_edit" => "PAGE_NAME=gallery_edit&USER_ALIAS#=#USER_ALIAS#&ACTION=#ACTION#");
	
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $GLOBALS["APPLICATION"]->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "USER_ALIAS", "ACTION", "AJAX_CALL", "sessid", "login"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
}
/***************** ADDITIONAL **************************************/
	$arParams["ONLY_ONE_GALLERY"] = ($arParams["ONLY_ONE_GALLERY"] == "N" ? "N" : "Y");
	$arParams["GALLERY_GROUPS"] = (is_array($arParams["GALLERY_GROUPS"]) ? $arParams["GALLERY_GROUPS"] : array());

	$arParams["GALLERY_AVATAR_SIZE"] = (intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? intVal($arParams["GALLERY_AVATAR_SIZE"]) : 50);
	$arParams["GALLERY_AVATAR"] = array(
		"WIDTH" => $arParams["GALLERY_AVATAR_SIZE"] ,
		"HEIGHT" => $arParams["GALLERY_AVATAR_SIZE"] );
	$arParams["GALLERY_AVATAR_THUMBS_SIZE"] = (intVal($arParams["GALLERY_AVATAR_THUMBS_SIZE"]) > 0 ? intVal($arParams["GALLERY_AVATAR_THUMBS_SIZE"]) : $arParams["GALLERY_AVATAR_SIZE"]);
	$arParams["GALLERY_AVATAR_THUMBS"] = array(
		"WIDTH" => $arParams["GALLERY_AVATAR_THUMBS_SIZE"],
		"HEIGHT" => $arParams["GALLERY_AVATAR_THUMBS_SIZE"]);
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
$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
$arParams["ABS_PERMISSION"] = $arParams["PERMISSION"];
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"])):
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
endif;
if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;

$arResult["I"] = array(
	"ABS_PERMISSION" => $arParams["ABS_PERMISSION"],
	"PERMISSION" => $arParams["PERMISSION"]);
$arResult["FORM"] = array();
$arResult["GALLERY"] = array();
$arResult["GALLERIES"] = array();
$arError = array();
if ($arParams["ACTION"] == "CREATE")
{
	$db_res = CIBlockSection::GetList(array(), 
		array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
		"SECTION_ID" => 0,
		"SOCNET_GROUP_ID" => false,
		"CREATED_BY" => $GLOBALS["USER"]->GetId()));

	if ($db_res && $res = $db_res->Fetch())
	{
		do 
		{
			$arResult["GALLERIES"][$res["ID"]] = $res;
		}while ($res = $db_res->Fetch());
	}
	
	if ($arResult["I"]["ABS_PERMISSION"] < "W")
	{
		$bUserHavePermission = false;
		$arUserGroupArray = $GLOBALS["USER"]->GetUserGroupArray();
		foreach($arParams["GALLERY_GROUPS"] as $PERM)
		{
			if(in_array($PERM, $arUserGroupArray))
			{
				$bUserHavePermission = true;
				break;
			}
		}
		if(!$bUserHavePermission)
		{
			ShowError(GetMessage("P_BAD_PERMISSION_TO_CREATE"));
			return 0;
		}
		if ($arParams["ONLY_ONE_GALLERY"] == "Y" && !empty($arResult["GALLERIES"]))
		{
			ShowError(GetMessage("P_BAD_PERMISSION_TO_CREATE_ONE"));
			return 0;
		}
	}
	$arResult["USER"] = array(
		"SHOW_NAME" => trim($GLOBALS["USER"]->GetFullName()));
	if (empty($arResult["USER"]["SHOW_NAME"]))
		$arResult["USER"]["SHOW_NAME"] = $GLOBALS["USER"]->GetLogin();
}
else
{
	$arFilter = array(
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0);
	
	if (!empty($arParams["USER_ALIAS"]))
		$arFilter["CODE"] = $arParams["USER_ALIAS"];
	else
		$arFilter["ID"] = $arParams["GALLERY_ID"];
	$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("UF_DEFAULT", "UF_GALLERY_SIZE", "UF_DATE"));

	if ($db_res && $res = $db_res->Fetch())
	{
		$arResult["GALLERY"] = $res;
		if ($arParams["PERMISSION"] < "W" && $arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId())
			$arParams["PERMISSION"] = "W";

		if ($arParams["PERMISSION"] < "W"):
			ShowError(GetMessage("P_BAD_PERMISSION"));
			return 0;
		endif;
		
		$arResult["GALLERY"]["~PICTURE"] = $arResult["GALLERY"]["PICTURE"];
		$arResult["GALLERY"]["PICTURE"] = CFile::GetFileArray($arResult["GALLERY"]["~PICTURE"]);
		
		$db_res = CIBlockSection::GetList(array(), 
			array("IBLOCK_ID" => $arParams["IBLOCK_ID"], 
			"SECTION_ID" => 0,
			"CREATED_BY" => $arResult["GALLERY"]["CREATED_BY"]));
		if ($db_res && $res = $db_res->Fetch())
		{
			do 
			{
				$arResult["GALLERIES"][$res["ID"]] = $res;
			} while($res = $db_res->Fetch());
		}
	}
	else
	{
		ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
		return 0;
	}

	$arParams["USER_ID"] = intVal($arResult["GALLERY"]["CREATED_BY"]);
	// 1. Get User info
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
		$arResult["USER"] = array(
			"SHOW_NAME" => GetMessage("P_USER_UNKNOWN"));
	}
	$arResult["USER"]["~SHOW_NAME"] = $arResult["USER"]["SHOW_NAME"];
	$arResult["USER"]["SHOW_NAME"] = htmlspecialcharsEx($arResult["USER"]["~SHOW_NAME"]);
}
/********************************************************************
				Actions
********************************************************************/
if ($arParams["ACTION"] == "DROP")
{
	@set_time_limit(0);
	if(!CIBlockSection::Delete($arResult["GALLERY"]["ID"]))
	{
		if($e = $APPLICATION->GetException())
			$strWarning = $e->GetString();
		else
			$strWarning = GetMessage("IBSEC_A_DELERR_REFERERS");
		$arError = array(
			"code" => "NOT_DROPED",
			"title" => $strWarning);
	}
	else
	{
		if (count($arResult["GALLERIES"]) > 1)
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERIES_URL"], 
				array("USER_ID" => $arResult["GALLERY"]["CREATED_BY"]));
		else
			$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~INDEX_URL"], array());
		
		BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/");
		LocalRedirect($arResult["URL"]);
	}
}
elseif (!empty($_REQUEST["save"]))
{
	$arError = array();
	$_REQUEST["CODE"] = trim($_REQUEST["CODE"]);
	if (!check_bitrix_sessid())
		$arError = array("code" => 100);
	elseif (empty($_REQUEST["CODE"]))
		$arError = array("code" => 201);
	elseif (preg_match("/[^a-z0-9_]/is", $_REQUEST["CODE"]))
	{
		$arError = array(
			"code" => "CODE_BAD",
			"title" => GetMessage("P_ERROR_CODE_BAD"));
	}
	else 
	{
		$ID = intVal($_REQUEST["ID"]);
		if ($arResult["GALLERY"]["CODE"] != $_REQUEST["CODE"])
		{
			$arFilter = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
				"CODE" => $_REQUEST["CODE"],
				"SECTION_ID" => 0);
			if ($ID > 0 && $arResult["GALLERY"]["ID"] == $ID)
				$arFilter["ID!"] = $ID;
			$db_res = CIBlockSection::GetList(array(), $arFilter);
			if ($db_res && $res = $db_res->Fetch())
			{
				$arError = array(
					"code" => "CODE_EXIST",
					"title" => GetMessage("P_ERROR_CODE_EXIST"));
			}
		}
		
		$arFiles = array();
		if (empty($arError) && !empty($_FILES["AVATAR"]) && !empty($_FILES["AVATAR"]["tmp_name"]))
		{
			include_once($_SERVER["DOCUMENT_ROOT"]."/".BX_PERSONAL_ROOT."/components/bitrix/photogallery.upload/functions.php"); 
			$arRealFile = $_FILES["AVATAR"]; 
			$arAlbumSights = array(
				"DETAIL_PICTURE" => array(
					"code" => "album",
					"notes" => "for_album",
					"width" => $arParams["GALLERY_AVATAR"]["WIDTH"],
					"height" => $arParams["GALLERY_AVATAR"]["HEIGHT"]),
				"PICTURE" => array(
					"code" => "album_thumbs",
					"notes" => "for_album",
					"width" => $arParams["GALLERY_AVATAR_THUMBS"]["WIDTH"],
					"height" => $arParams["GALLERY_AVATAR_THUMBS"]["HEIGHT"]));
			foreach ($arAlbumSights as $key => $Sight)
			{
				$File = $arRealFile; 
				
				$File["name"] = "avatar_".$Sight["code"].$arRealFile["name"];
				$File["tmp_name"] = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/".$File["name"];
				__ResizeImage($File, $arRealFile, $Sight, 1);
				$File["MODULE_ID"] = "iblock";
				$arFiles[$key] = $File;
			}
			@imagedestroy($arRealFile["image"]);
		}
		
		if (empty($arError))
		{
			$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", 0, LANGUAGE_ID);
			if (empty($arUserFields) || empty($arUserFields["UF_DEFAULT"]))
			{
				$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_DEFAULT"));
				if (!$db_res || !($res = $db_res->GetNext()))
				{
					$arFields = Array(
						"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
						"FIELD_NAME" => "UF_DEFAULT",
						"USER_TYPE_ID" => "string",
						"MULTIPLE" => "N",
						"MANDATORY" => "N");
					$arFieldName = array();
					$rsLanguage = CLanguage::GetList($by, $order, array());
					while($arLanguage = $rsLanguage->Fetch()):
						if (LANGUAGE_ID == $arLanguage["LID"])
							$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_DEFAULT");
						if (empty($arFieldName[$arLanguage["LID"]]))
							$arFieldName[$arLanguage["LID"]] = "Default gallery";
					endwhile;
					$arFields["EDIT_FORM_LABEL"] = $arFieldName;
					$obUserField  = new CUserTypeEntity;
					$obUserField->Add($arFields);
					$APPLICATION->GetException();
					$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
				}
			}
			if (empty($arUserFields) || empty($arUserFields["UF_GALLERY_SIZE"]))
			{
				$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_GALLERY_SIZE"));
				if (!$db_res || !($res = $db_res->GetNext()))
				{
					$arFields = Array(
						"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
						"FIELD_NAME" => "UF_GALLERY_SIZE",
						"USER_TYPE_ID" => "string",
						"MULTIPLE" => "N",
						"MANDATORY" => "N");
					$arFieldName = array();
					$rsLanguage = CLanguage::GetList($by, $order, array());
					while($arLanguage = $rsLanguage->Fetch()):
						if (LANGUAGE_ID == $arLanguage["LID"])
							$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_GALLERY_SIZE");
						if (empty($arFieldName[$arLanguage["LID"]]))
							$arFieldName[$arLanguage["LID"]] = "Gallery size";
					endwhile;
					$arFields["EDIT_FORM_LABEL"] = $arFieldName;
					$obUserField  = new CUserTypeEntity;
					$obUserField->Add($arFields);
					$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
				}
			}
			if (empty($arUserFields) || empty($arUserFields["UF_GALLERY_RECALC"]))
			{
				$db_res = CUserTypeEntity::GetList(array($by=>$order), array("ENTITY_ID" => "IBLOK_".$arParams["IBLOCK_ID"]."_SECTION", "FIELD_NAME" => "UF_GALLERY_RECALC"));
				if (!$db_res || !($res = $db_res->GetNext()))
				{
					$arFields = Array(
						"ENTITY_ID" => "IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION",
						"FIELD_NAME" => "UF_GALLERY_RECALC",
						"USER_TYPE_ID" => "string",
						"MULTIPLE" => "N",
						"MANDATORY" => "N");
					$arFieldName = array();
					$rsLanguage = CLanguage::GetList($by, $order, array());
					while($arLanguage = $rsLanguage->Fetch()):
						if (LANGUAGE_ID == $arLanguage["LID"])
							$arFieldName[$arLanguage["LID"]] = GetMessage("IBLOCK_GALLERY_RECALC");
						if (empty($arFieldName[$arLanguage["LID"]]))
							$arFieldName[$arLanguage["LID"]] = "Gallery size information";
					endwhile;
					$arFields["EDIT_FORM_LABEL"] = $arFieldName;
					$obUserField  = new CUserTypeEntity;
					$obUserField->Add($arFields);
					$GLOBALS["USER_FIELD_MANAGER"]->arFieldsCache = array();
				}
			}
			
			$bs = new CIBlockSection;
			$_REQUEST["ACTIVE"] = ($_REQUEST["ACTIVE"] == "Y" ? "Y" : "N");
			$_REQUEST["ACTIVE"] = (empty($arResult["GALLERIES"]) ? "Y" : $_REQUEST["ACTIVE"]);
			if ($arParams["ACTION"] == "EDIT")
			{
				if (!empty($arResult["GALLERIES"]) && $_REQUEST["ACTIVE"] == "Y" && $arResult["GALLERY"]["UF_DEFAULT"] != "Y")
				{
					$arr = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "UF_DEFAULT" => "N");
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arr);
					$GLOBALS["UF_DEFAULT"] = "N";
					foreach ($arResult["GALLERIES"] as $res)
					{
						if ($res["ID"] != $ID)
						{
							$res = $bs->Update($res["ID"], $arr, false, false);
						}
					}
				}
				
				$arFields = Array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"NAME" => $_REQUEST["NAME"],
					"CODE" => $_REQUEST["CODE"],
					"DESCRIPTION" => $_REQUEST["DESCRIPTION"],
					"UF_DEFAULT" => $_REQUEST["ACTIVE"]);
				if (!empty($arFiles))
				{
					$arFields["PICTURE"] = $arFiles["PICTURE"];
				}
				
				if ($bs->CheckFields($arFields, $ID))
				{
					if (!empty($arFiles))
					{
						$arFields["DETAIL_PICTURE"] = $arFiles["DETAIL_PICTURE"];
					}
					$GLOBALS["UF_DEFAULT"] = $arFields["UF_DEFAULT"];
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
					$res = $bs->Update($ID, $arFields);
				}
				else 
				{
					$res = false;
				}
			}
			elseif ($arParams["ACTION"] == "CREATE")
			{
				if (!empty($arResult["GALLERIES"]) && $_REQUEST["ACTIVE"] == "Y")
				{
					$arr = array("IBLOCK_ID" => $arParams["IBLOCK_ID"], "UF_DEFAULT" => "N");
					$GLOBALS["UF_DEFAULT"] = "N";
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arr);
					foreach ($arResult["GALLERIES"] as $res)
					{
						$res = $bs->Update($res["ID"], $arr, false, false);
					}
				}
				
				$arFields = Array(
					"ACTIVE" => "Y",
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"NAME" => $_REQUEST["NAME"],
					"CODE" => $_REQUEST["CODE"],
					"SOCNET_GROUP_ID" => ($arParams["SOCNET_GROUP_ID"] > 0 ? $arParams["SOCNET_GROUP_ID"] : false),
					"DESCRIPTION" => $_REQUEST["DESCRIPTION"],
					"UF_DEFAULT" => $_REQUEST["ACTIVE"]);
				if (!empty($arFiles))
				{
					$arFields["PICTURE"] = $arFiles["PICTURE"];
				}
				if ($bs->CheckFields($arFields))
				{
					if (!empty($arFiles))
					{
						$arFields["DETAIL_PICTURE"] = $arFiles["DETAIL_PICTURE"];
					}					
					$GLOBALS["UF_DEFAULT"] = $arFields["UF_DEFAULT"];
					$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
					$res = $bs->Add($arFields);
				}
				else 
				{
					$res = false;
				}
			}
			
			if (!empty($arFiles))
			{
				@unlink($arFiles["PICTURE"]["tmp_name"]);
				@unlink($arFiles["DETAIL_PICTURE"]["tmp_name"]);
			}
			
			if($res <= 0)
			{
				$arError = array(
					"code" => "SECTION_NOT_ADD",
					"title" => $bs->LAST_ERROR);
			}
			else 
			{
				$ID = ($ID > 0 ? $ID : $res);
				if (($arParams["ACTION"] == "CREATE" && $arResult["GALLERIES"] >= 1) || $arResult["GALLERIES"] > 1 || 
					$arResult["I"]["ABS_PERMISSION"] >= "W")
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => $arParams["USER_ID"]));
				else
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["GALLERY_URL"], array("USER_ALIAS" => $_REQUEST["CODE"]));
			}
		}
	}

	if (!empty($arError))
	{
		$arResult["ERROR_MESSAGE"] = PhotoShowError($arError);
		$arResult["FORM"]["ID"] = $_REQUEST["ID"];
		$arResult["FORM"]["CODE"] = $_REQUEST["CODE"];
		$arResult["FORM"]["NAME"] = $_REQUEST["NAME"];
		$arResult["FORM"]["DESCRIPTION"] = $_REQUEST["DESCRIPTION"];
		$arResult["FORM"]["UF_DEFAULT"] = $_REQUEST["ACTIVE"];
	}
	else
	{
		BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/");
		LocalRedirect($arResult["URL"]);
	}
}
elseif (!empty($_REQUEST["cancel"]))
{
	if ($arResult["I"]["ABS_PERMISSION"] >= "W")
		$url = CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => $arParams["USER_ID"]));
	elseif (!empty($arResult["GALLERY"]["CODE"]))
		$url = CComponentEngine::MakePathFromTemplate($arParams["GALLERY_URL"], array("USER_ALIAS" => $arResult["GALLERY"]["CODE"]));
	else 
		$url = CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array());
	LocalRedirect($url);
}
/********************************************************************
				/Actions
********************************************************************/
if (empty($arResult["FORM"]))
{
	if (!empty($arResult["GALLERY"]))
		$arResult["FORM"] = $arResult["GALLERY"];
	else
		$arResult["FORM"] = array(
		"CODE" => $GLOBALS["USER"]->GetLogin(),
		"NAME" => $GLOBALS["USER"]->GetFullName());	
}
		
$arResult["FORM"]["AVATAR"] = $arResult["GALLERY"]["PICTURE"];

$arResult["FORM"]["ID"] = htmlspecialcharsEx($arResult["FORM"]["ID"]);
$arResult["FORM"]["CODE"] = htmlspecialcharsEx($arResult["FORM"]["CODE"]);
$arResult["FORM"]["NAME"] = htmlspecialcharsEx($arResult["FORM"]["NAME"]);
$arResult["FORM"]["DESCRIPTION"] = htmlspecialcharsEx($arResult["FORM"]["DESCRIPTION"]);

$this->IncludeComponentTemplate();
if ($arParams["SET_TITLE"] == "Y")
	$GLOBALS['APPLICATION']->SetTitle($arParams["ACTION"] == "CREATE" ? GetMessage("P_GALLERY_CREATE") : GetMessage("P_GALLERY_EDIT"));
if($arParams["DISPLAY_PANEL"] && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
return $arResult["GALLERY"]["ID"];
?>