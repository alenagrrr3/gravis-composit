<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
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
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim(!empty($arParams["IBLOCK_TYPE"]) ? $arParams["IBLOCK_TYPE"] : $_REQUEST["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intVal(intVal($arParams["IBLOCK_ID"]) > 0 ? $arParams["IBLOCK_ID"] : $_REQUEST["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal(intVal($arParams["SECTION_ID"]) > 0 ? $arParams["SECTION_ID"] : $_REQUEST["SECTION_ID"]);
	$arParams["ELEMENT_ID"] = intVal(intVal($arParams["ELEMENT_ID"]) > 0 ? $arParams["ELEMENT_ID"] : $_REQUEST["ELEMENT_ID"]);
	$arParams["USER_ALIAS"] = preg_replace("/[^a-z0-9\_]+/is" , "", $arParams["USER_ALIAS"]);	
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["ACTION"] = trim(empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);
	
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#",
		"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#");

	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, 
				array("PAGE_NAME", "USER_ALIAS", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "edit", "detail_list_edit", "sessid"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITTIONAL ************************************/
	$arParams["DATE_TIME_FORMAT"] = trim($arParams["DATE_TIME_FORMAT"]);
	if(strlen($arParams["DATE_TIME_FORMAT"])<=0)
		$arParams["DATE_TIME_FORMAT"] = $DB->DateFormatToPHP(CSite::GetDateFormat("FULL"));
	$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"]);
//***************** STANDART ***************************************/
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N"); //Turn off by default
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y"); //Turn on by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
	$strWarning = "";
	$arIBTYPE = false;
	$bBadBlock = true;
	$bVarsFromForm = false;
	if ($arParams["AJAX_CALL"] == "Y")
	{
		$GLOBALS['APPLICATION']->RestartBuffer();
	}
	$arResult["GALLERY"] = array();
	$arResult["SECTION"] = array();
	$arResult["ELEMENT"] = array();
/********************************************************************
				/Default values
********************************************************************/
	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		return 0;
	elseif ($arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"])):
		$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
	endif;
	if ($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("P_DENIED_ACCESS"));
		return 0;
	endif;
	
	$arResult["I"] = array(
		"ABS_PERMISSION" => $arParams["PERMISSION"]);
	
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"ID" => $arParams["SECTION_ID"]);

	if ($arParams["BEHAVIOUR"] == "USER") // Gallery
	{
		// GALLERY INFO
		$db_res = CIBlockSection::GetList(array(), array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]), false, array("UF_GALLERY_SIZE"));
		if ($db_res && $res = $db_res->Fetch())
		{
			$arResult["GALLERY"] = $res;
			if ($arParams["PERMISSION"] < "W" && intVal($arResult["GALLERY"]["CREATED_BY"]) == intVal($GLOBALS["USER"]->GetId()))
				$arParams["PERMISSION"] = "W";
			if ($arResult["GALLERY"]["ID"] != $arParams["SECTION_ID"])
				$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
			$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
			$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
		}
		else
		{
			$this->AbortResultCache();
			ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
			return 0;
		}
	}
	if ($arParams["PERMISSION"] < "W")
	{
		ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		return 0;
	}
	$arResult["I"] = array("PERMISSION" => $arParams["PERMISSION"]);
	
	// IBlockSection
	$rsSection = CIBlockSection::GetList(Array(), $arFilter);
	$arResult["SECTION"] = $rsSection->GetNext();
	if (!$arResult["SECTION"])
	{
		ShowError("BAD SECTION");
		return 0;
	}
	
	
	// IBlockElement
	//SELECT
	$arSelect = array(
		"ID",
		"IBLOCK_ID",
		"IBLOCK_SECTION_ID",
		"NAME",
		"PREVIEW_TEXT",
		"DETAIL_TEXT",
		"PREVIEW_TEXT_TYPE",
		"DETAIL_TEXT_TYPE",
		"TAGS",
		"DATE_CREATE",
		"CREATED_BY",
		"PROPERTY_REAL_PICTURE",
		"PROPERTY_PUBLIC_ELEMENT",
		"PROPERTY_APPROVE_ELEMENT"
	);
	//WHERE
	$arFilter = array(
		"ID" => $arParams["ELEMENT_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ACTIVE_DATE" => "Y",
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "Y",
		"SECTION_ID" => $arParams["SECTION_ID"]
	);
	
	//EXECUTE
	$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
	$obElement = $rsElement->GetNextElement();
	if (empty($obElement))
	{
		ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
		return 0;
	}
	
	$arResult["ELEMENT"] = $obElement->GetFields();
	$arResult["ELEMENT"]["PROPERTIES"] = array();
	foreach ($arResult["ELEMENT"] as $key => $val)
	{
		if ((substr($key, 0, 9) == "PROPERTY_" && substr($key, -6, 6) == "_VALUE"))
		{
			$arResult["ELEMENT"]["PROPERTIES"][substr($key, 9, intVal(strLen($key)-15))] = array("VALUE" => $val);
		}
	}
	
	// URL`s
	$arResult["~SECTION_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
	$arResult["~DETAIL_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
		array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"], "ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
	$arResult["DETAIL_LINK"] = htmlspecialcharsEx($arResult["~DETAIL_LINK"]);
/***********************************************************************************/	
/***************** POST ************************************************************/	
/***********************************************************************************/	
	if($_REQUEST["edit"] == "Y" || $arParams["ACTION"] == "DROP")
	{
		array_walk($_REQUEST, '__UnEscape');
		
		if(!(check_bitrix_sessid()))
		{
			$strWarning = GetMessage("IBLOCK_WRONG_SESSION")."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			if ($arParams["ACTION"] == "DROP")
			{
				$arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]);
				$iFileSize = intVal($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]["FILE_SIZE"]);
				@set_time_limit(0);
				$DB->StartTransaction();
				$APPLICATION->ResetException();
				if(!CIBlockElement::Delete($arParams["ELEMENT_ID"]))
				{
					$DB->Rollback();
					$bVarsFromForm = true;
					if($ex = $APPLICATION->GetException())
						$strWarning = $ex->GetString();
				}
				else 
				{
					$DB->Commit();
					$result = array("url" => $arResult["~SECTION_LINK"]);
					$arResult["URL"] = $arResult["~SECTION_LINK"];
				}
				
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
			}
			else
			{
				$arFields = Array(
					"MODIFIED_BY" => $USER->GetID(),
					"IBLOCK_SECTION" => $_REQUEST["TO_SECTION_ID"],
					"TAGS" => $_REQUEST["TAGS"],
					"NAME" => $_REQUEST["TITLE"],
					"PREVIEW_TEXT" => $_REQUEST["DESCRIPTION"],
					"DETAIL_TEXT" => $_REQUEST["DESCRIPTION"],
					"DATE_CREATE" => $_REQUEST["DATE_CREATE"],
					"DETAIL_TEXT_TYPE" => "text",
					"PREVIEW_TEXT_TYPE" => "text");
				$bs = new CIBlockElement;
				$ID = $bs->Update($arParams["ELEMENT_ID"], $arFields);
				if($ID <= 0)
				{
					$strWarning .= $bs->LAST_ERROR."<br>";
				}
				elseif ($arParams["BEHAVIOUR"] == "USER")
				{
					CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], ($_REQUEST["PUBLIC_ELEMENT"] == "Y" ? "Y" : "N"), "PUBLIC_ELEMENT"); 
					if ($arResult["I"]["ABS_PERMISSION"] >= "W")
					{
						CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], ($_REQUEST["APPROVE_ELEMENT"] == "Y" ? "Y" : "N"), "APPROVE_ELEMENT");
					}
				}
				
				if(strlen($strWarning)>0)
				{
					$bVarsFromForm = true;
					$DB->Rollback();
				}
				else
				{
					$DB->Commit();
					
					if ($arParams["SECTION_ID"] != $_REQUEST["TO_SECTION_ID"])
					{
						CIBlockElement::RecalcSections($arParams["SECTION_ID"]);
						CIBlockElement::RecalcSections($_REQUEST["TO_SECTION_ID"]);
					}
					
					if ($arParams["AJAX_CALL"] == "Y")
					{
						if ($arParams["SECTION_ID"] != $_REQUEST["TO_SECTION_ID"])
						{
							$result = array(
								"url" => CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
									array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $_REQUEST["TO_SECTION_ID"], 
										"ELEMENT_ID" => $arResult["ELEMENT"]["ID"])));
						}
						else 
						{
							
							$arSelect = array(
								"ID",
								"NAME",
								"DETAIL_TEXT",
								"DETAIL_TEXT_TYPE",
								"TAGS",
								"DATE_CREATE",
								"CREATED_BY"
							);
							//WHERE
							$arFilter = array("ID" => $arParams["ELEMENT_ID"]);
							
							//EXECUTE
							$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
							$_obElement = $rsElement->GetNextElement();
							if (!empty($_obElement))
							{
								$obElement = $_obElement->GetFields();
								$result = array(
								"TAGS" => $obElement["TAGS"],
								"TITLE" => $obElement["NAME"],
								"DESCRIPTION" => $obElement["DETAIL_TEXT"],
								"DATE" => $obElement["DATE_CREATE"]);
							}
							else 
							{
								$result = array(
									"TAGS" => htmlspecialcharsEx($_REQUEST["TAGS"]),
									"TITLE" => htmlspecialcharsEx($_REQUEST["TITLE"]),
									"DESCRIPTION" => htmlspecialcharsEx($_REQUEST["DESCRIPTION"]),
									"DATE" => htmlspecialcharsEx($_REQUEST["DATE_CREATE"]));
							}
						}
					}
					
					$arResult["URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
						array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $_REQUEST["TO_SECTION_ID"], 	
							"ELEMENT_ID" => $arResult["ELEMENT"]["ID"]));
				}
			}
			
			if (!$bVarsFromForm)
			{
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
				if ($arParams["ACTION"] == "DROP")
				{
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arResult["SECTION"]["ID"]."/");
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arResult["SECTION"]["IBLOCK_SECTION_ID"]."/");
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/0/".$arParams["ELEMENT_ID"]);
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arResult["SECTION"]["ID"]."/");
				}
				else 
				{
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arResult["SECTION"]["ID"]."/");
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arResult["SECTION"]["IBLOCK_SECTION_ID"]."/");
					if ($_REQUEST["TO_SECTION_ID"] != $arResult["SECTION"]["ID"])
					{
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$_REQUEST["TO_SECTION_ID"]."/");
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$_REQUEST["TO_SECTION_ID"]."/");
						
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$_REQUEST["TO_SECTION_ID"]."/");
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/0/".$arParams["ELEMENT_ID"]);
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arResult["SECTION"]["ID"]."/");
					}
					else 
					{
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/0/".$arParams["ELEMENT_ID"]);
						BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail/".$arResult["SECTION"]["ID"]."/".$arParams["ELEMENT_ID"]);
					}
				}
				
				
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/all/");
				BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$arResult["SECTION"]["ID"]."/");
				
				if ($arParams["AJAX_CALL"] == "Y")
				{
					$APPLICATION->RestartBuffer();
					$result["DATE"] = PhotoDateFormat($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($result["DATE"], CSite::GetDateFormat()));
					?><?=CUtil::PhpToJSObject($result);?><?
					die();
				}
				else 
				{
					LocalRedirect($arResult["URL"]);
				}
			}
		}
	}
	elseif ($_REQUEST["edit"] == "cancel")
	{
		LocalRedirect($arResult["~DETAIL_LINK"]);
	}
	
	$arResult["ERROR_MESSAGE"] = $strWarning;
	$arResult["ELEMENT"]["NAME"] = htmlspecialcharsEx($arResult["ELEMENT"]["~NAME"]);
	$arResult["ELEMENT"]["DETAIL_TEXT"] = htmlspecialcharsEx($arResult["ELEMENT"]["~DETAIL_TEXT"]);
	$arResult["ELEMENT"]["TAGS"] = htmlspecialcharsEx($arResult["ELEMENT"]["~TAGS"]);
	if ($bVarsFromForm)
	{
		$arResult["ELEMENT"]["NAME"] = htmlspecialcharsEx($_REQUEST["TITLE"]);
		$arResult["ELEMENT"]["DETAIL_TEXT"] = htmlspecialcharsEx($_REQUEST["DESCRIPTION"]);
		$arResult["ELEMENT"]["TAGS"] = htmlspecialcharsEx($_REQUEST["TAGS"]);
		$arResult["ELEMENT"]["IBLOCK_SECTION_ID"] = htmlspecialcharsEx($_REQUEST["TO_SECTION_ID"]);
		$arResult["ELEMENT"]["DATE_CREATE"] = htmlspecialcharsEx($_REQUEST["DATE"]);
	}
	
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
	);
	if ($arParams["BEHAVIOUR"] == "USER")
	{
		$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
		$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
		$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
	}
	
	$arResult["SECTION_LIST"] = array();
	$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
	while ($arSection = $rsIBlockSectionList->GetNext())
	{
		$arSection["NAME"] = str_repeat(" . ", ($arSection["DEPTH_LEVEL"] - 1)).$arSection["NAME"];
		$arResult["SECTION_LIST"][$arSection["ID"]] = $arSection["NAME"];
	}
/*******************************************************************/	
	if($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["ELEMENT"]["NAME"]);
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
		CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arParams["SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
	
	$rsPath = GetIBlockSectionPath($arResult["SECTION"]["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
	while($arPath=$rsPath->GetNext())
	{
		if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
			continue;
		
		$arPath["~SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"]));
		$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
	}
	$APPLICATION->AddChainItem($arResult["ELEMENT"]["NAME"], $arResult["~DETAIL_LINK"]);
	
/*******************************************************************/	
	$this->IncludeComponentTemplate();
?>