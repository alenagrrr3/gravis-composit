<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery")):
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
//	$arParams["ALBUM_PHOTO"]["WIDTH"]
//	$arParams["ALBUM_PHOTO"]["HEIGHT"]
//	$arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"]
//	$arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"]
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
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	
	$arParams["ELEMENT_SORT_FIELD"] = (empty($arParams["ELEMENT_SORT_FIELD"]) ? "ID" : strToUpper($arParams["ELEMENT_SORT_FIELD"]));
	$arParams["ELEMENT_SORT_ORDER"] = (strToUpper($arParams["ELEMENT_SORT_ORDER"]) != "DESC" ? "ASC" : "DESC");
	$arParams["ACTION"] = (empty($arParams["ACTION"]) ? $_REQUEST["ACTION"] : $arParams["ACTION"]);
	$arParams["ACTION"] = strToUpper(empty($arParams["ACTION"]) ? "EDIT" : $arParams["ACTION"]);
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
			"sections_top" => "",
			"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
			"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
				"&SECTION_ID=#SECTION_ID#");
		
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
	$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
	if (empty($arParams[strToUpper($URL)."_URL"]))
		$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL"));
	$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
	$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** ADDITIONAL **************************************/
	$arParams["ALBUM_PHOTO"]["WIDTH"] = (intVal($arParams["ALBUM_PHOTO_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_WIDTH"]) : 150);
	$arParams["ALBUM_PHOTO"]["HEIGHT"] = (intVal($arParams["ALBUM_PHOTO_HEIGHT"]) > 0 ? intVal($arParams["ALBUM_PHOTO_HEIGHT"]) : 150);
	$arParams["ALBUM_PHOTO"]["HEIGHT"] = $arParams["ALBUM_PHOTO"]["WIDTH"];
	$arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) : 70);
	$arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_HEIGHT"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_HEIGHT"]) : 70);
	$arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"] = $arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"];
	
	$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
//***************** STANDART ****************************************/
	$arParams["SET_TITLE"] = $arParams["SET_TITLE"]!="N"; //Turn on by default
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = $arParams["DISPLAY_PANEL"]=="Y"; //Turn off by default
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
	$arResult["GALLERY"] = array();
	$strWarning = "";
	$arIBTYPE = false;
	$bBadBlock = true;
	$bVarsFromForm = false;
	$bGD2 = false;
	if (function_exists("gd_info"))
	{
		$arGDInfo = gd_info();
		$bGD2 = ((strpos($arGDInfo['GD Version'], "2.") !== false) ? true : false);
	}
	if ($arParams["AJAX_CALL"] == "Y")
		$GLOBALS['APPLICATION']->RestartBuffer();

	$arResult["ELEMENTS"] = array(
		"MAX_WIDTH" => 0,
		"MAX_HEIGHT" => 0);

	$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
	if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
		$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];
	if($arParams["PERMISSION"] < "R"):
		ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		return 0;
	endif;
	// GALLERY INFO
	if ($arParams["BEHAVIOUR"] == "USER")
	{
		$db_res = CIBlockSection::GetList(array(), array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"SECTION_ID" => 0,
			"CODE" => $arParams["USER_ALIAS"]), false, array("UF_GALLERY_SIZE"));
		
		if ($db_res && $res = $db_res->GetNext())
			$arResult["GALLERY"] = $res;
		if (empty($arResult["GALLERY"]))
		{
			ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
			return 0;
		}
		if ($arParams["PERMISSION"] < "W" && intVal($arResult["GALLERY"]["CREATED_BY"]) == intVal($GLOBALS["USER"]->GetId()))
			$arParams["PERMISSION"] = "W";
	}
	if($arParams["PERMISSION"] < "W")
	{
		ShowError(GetMessage("IBLOCK_BAD_IBLOCK"));
		return 0;
	}
	
	// IBlockSection
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");
	if (!empty($arResult["GALLERY"]))
	{
		if (intVal($arResult["GALLERY"]) != $arParams["SECTION_ID"])
			$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
		$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
		$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
	}
	if(strlen($arParams["SECTION_CODE"]) > 0)
		$arFilter["CODE"]=$arParams["SECTION_CODE"];
	else
		$arFilter["ID"]=$arParams["SECTION_ID"];

	$rsSection = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
	$arResult["SECTION"] = $rsSection->GetNext();
	if (!$arResult["SECTION"])
	{
		ShowError(GetMessage("P_SECTION_NOT_FOUND"));
		return 0;
	}
	
	if (intVal($arResult["SECTION"]["IBLOCK_SECTION_ID"]) > 0)
	{
		if ($arResult["SECTION"]["IBLOCK_SECTION_ID"] == $arResult["GALLERY"]["ID"])
			$arResult["SECTION"]["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
				array("USER_ALIAS" => $arParams["USER_ALIAS"]));
		else
			$arResult["SECTION"]["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
				array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["IBLOCK_SECTION_ID"]));
	}
	else
	{
		$arResult["SECTION"]["BACK_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTIONS_TOP_URL"], 
				array());
	}
	$arResult["SECTION"]["SECTION_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"]));
	$arResult["SECTION"]["PATH"] = array();
	$arResult["ITEMS"] = array();
	
	$rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
	while($arPath=$rsPath->GetNext())
	{
		$arPath["~SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"]));
		$arPath["SECTION_PAGE_URL"] = htmlSpecialChars($arPath["~SECTION_PAGE_URL"]);
		$arResult["SECTION"]["PATH"][] = $arPath;
	}
	
	// ITEMS INFO
	if($arResult["SECTION"]["ID"])
	{
		//SELECT
		$arSelect =array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"NAME",
			"PREVIEW_PICTURE",
			"DETAIL_PICTURE",
			"PROPERTY_REAL_PICTURE");
		
		//WHERE
		$arrFilter["SECTION_ID"] = $arResult["SECTION"]["ID"];
		$arrFilter["INCLUDE_SUBSECTIONS"] = "Y";
		$arrFilter["ACTIVE"] = "Y";
		$arrFilter["ACTIVE_DATE"] = "Y";
		$arrFilter["CHECK_PERMISSIONS"] = "Y";
		$arrFilter["IBLOCK_ID"] = $arParams["IBLOCK_ID"];
		//EXECUTE
		$rsElements = CIBlockElement::GetList(array($arParams["ELEMENT_SORT_FIELD"] => $arParams["ELEMENT_SORT_ORDER"]), $arrFilter, false, false, $arSelect);
		while($obElement = $rsElements->GetNextElement())
		{
			$arItem = $obElement->GetFields();
			$arItem["PICTURE"] = CFile::GetFileArray($arItem["PREVIEW_PICTURE"]);
			$arResult["ELEMENTS"]["MAX_WIDTH"]	= max($arResult["ELEMENTS"]["MAX_WIDTH"], $arItem["PICTURE"]["WIDTH"]);
			$arResult["ELEMENTS"]["MAX_HEIGHT"]	= max($arResult["ELEMENTS"]["MAX_HEIGHT"], $arItem["PICTURE"]["HEIGHT"]);
			
			$arItem["PICTURE_FOR_ALBUM"] = array();
			if($_REQUEST["save_edit"] == "Y" || $_REQUEST["edit"] == "Y")
			{
				if (intVal($arItem["PROPERTY_REAL_PICTURE_VALUE"]) > 0)
					$arItem["PICTURE_FOR_ALBUM"] = CFile::GetFileArray($arItem["PROPERTY_REAL_PICTURE_VALUE"]);
				if (empty($arItem["PICTURE_FOR_ALBUM"]) && (intVal($arItem["DETAIL_PICTURE"]) > 0))
					$arItem["PICTURE_FOR_ALBUM"] = CFile::GetFileArray($arItem["DETAIL_PICTURE"]);
				if (empty($arItem["PICTURE_FOR_ALBUM"]))
					$arItem["PICTURE_FOR_ALBUM"] = $arItem["PICTURE"];
			}
			
			$arResult["ITEMS"][$arItem["ID"]]=$arItem;
		}
	}
/***********************************************************************************/	
/***************** POST ************************************************************/	
/***********************************************************************************/	
	if (empty($arResult["ITEMS"]))
	{
		$strWarning = GetMessage("P_EMPTY_PHOTO")."<br>";
	}
	elseif($_REQUEST["save_edit"] == "Y" || $_REQUEST["edit"] == "Y")
	{
		array_walk($_REQUEST, '__UnEscape');
		
		if(!(check_bitrix_sessid()))
		{
			$strWarning = GetMessage("IBLOCK_WRONG_SESSION")."<br>";
			$bVarsFromForm = true;
		}
		elseif (count($_REQUEST["photos"]) <= 0)
		{
			$strWarning = GetMessage("P_NO_PHOTO")."<br>";
			$bVarsFromForm = true;
		}
		else
		{
			$arImages = array();
			foreach ($_REQUEST["photos"] as $key => $iImage)
			{
				if (intVal($iImage) > 0)
				{
					$arImage = $arResult["ITEMS"][$iImage]["PICTURE_FOR_ALBUM"];
					if ($arImage && $arImage["WIDTH"] > 0 && $arImage["HEIGHT"] > 0)
						$arImages[] = $arImage;
				}
			}
			
			if (empty($arImages))
			{
				$strWarning = GetMessage("P_NO_PHOTO")."<br>";
				$bVarsFromForm = true;
			}
			else 
			{
				$iCount = ceil(sqrt(count($arImages)));
				
				$arPhoto = array(
					"w" => ($arParams["ALBUM_PHOTO"]["WIDTH"]), 
					"h" => ($arParams["ALBUM_PHOTO"]["HEIGHT"]), 
					"width" => ($arParams["ALBUM_PHOTO"]["WIDTH"] / $iCount),
					"height" => ($arParams["ALBUM_PHOTO"]["HEIGHT"] / $iCount),
					);
					
				$row = 0;
				$cell = 0;
				$count = 1;
				
				if ($bGD2)
					$picture = ImageCreateTrueColor($arPhoto["w"], $arPhoto["h"]);
				else
					$picture = ImageCreate($arPhoto["w"], $arPhoto["h"]);
				
				foreach ($arImages as $key => $arImage)
				{
					if ($cell >= $iCount)
					{
						$cell = 0;
						$row++;
					}
					$dst = array(
						"width" => $arPhoto["width"], 
						"height" => $arPhoto["height"],
						"x" => ($cell * $arPhoto["width"]),
						"y" => ($row * $arPhoto["height"]));
					$cell++;
					$src = array(
						"x" => 0,
						"y" => 0, 
						"width" => $dst["width"],
						"height" => $dst["height"]);
					
					$iResizeCoeff = 1;
					if ($arImage["WIDTH"] > 0 && $arImage["HEIGHT"] > 0)
						$iResizeCoeff = max(
							($dst["width"] / $arImage["WIDTH"]), 
							($dst["height"] / $arImage["HEIGHT"]));

					if ($iResizeCoeff > 0)
					{
						$src["x"] = ((($arImage["WIDTH"]*$iResizeCoeff - $dst["width"])/2)/$iResizeCoeff);
						$src["y"] = ((($arImage["HEIGHT"]*$iResizeCoeff - $dst["height"])/2)/$iResizeCoeff);
						$src["width"] = $dst["width"] / $iResizeCoeff;
						$src["height"] = $dst["height"] / $iResizeCoeff;
					}
					
					$src["pathinfo"] = pathinfo($arImage["SRC"]);
					$src["SRC"] = $_SERVER['DOCUMENT_ROOT']."/".$arImage["SRC"];
					$src["SRC"] = str_replace("//", "/", $src["SRC"]);
					$imageInput = false;
					switch (strToLower($src["pathinfo"]["extension"]))
					{
						case 'gif':
							$imageInput = imagecreatefromgif($src["SRC"]);
						break;
						case 'png':
							$imageInput = imagecreatefrompng($src["SRC"]);
						break;
						case 'bmp':
							$imageInput = imagecreatefromgif($src["SRC"]);
						break;
						default:
							$imageInput = imagecreatefromjpeg($src["SRC"]);
						break;
					}
					$src["image"] = $imageInput;
					if ($bGD2)
						imagecopyresampled($picture, $src["image"], 
							$dst["x"], $dst["y"], $src["x"], $src["y"], 
							$dst["width"], $dst["height"], $src["width"], $src["height"]);
					else
						imagecopyresized($picture, $src["image"], 
							$dst["x"], $dst["y"], $src["x"], $src["y"], 
							$dst["width"], $dst["height"], $src["width"], $src["height"]);
				}
				
				if ($bGD2)
					$thumbnail = ImageCreateTrueColor($arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"], $arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"]);
				else
					$thumbnail = ImageCreate($arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"], $arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"]);
				
				if ($bGD2)
					imagecopyresampled($thumbnail, $picture, 
						0, 0, 0, 0, 
						$arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"], $arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"], 
						$arParams["ALBUM_PHOTO"]["HEIGHT"], $arParams["ALBUM_PHOTO"]["HEIGHT"]);
				else
					imagecopyresized($thumbnail, $picture, 
						0, 0, 0, 0, 
						$arParams["ALBUM_PHOTO_THUMBS"]["WIDTH"], $arParams["ALBUM_PHOTO_THUMBS"]["HEIGHT"], 
						$arParams["ALBUM_PHOTO"]["HEIGHT"], $arParams["ALBUM_PHOTO"]["HEIGHT"]);
				
				CheckDirPath($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");
				imagejpeg($picture, $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_".$arResult["SECTION"]["ID"].".jpg");
				imagejpeg($thumbnail, $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_thumbnail_".$arResult["SECTION"]["ID"].".jpg");
				imagedestroy($picture);
				imagedestroy($thumbnail);
				
				$arFields = Array(
					"PICTURE" => array(
						"name" => "iblock_section_thumbnail_".$arResult["SECTION"]["ID"].".jpg",
			            "type" => "image/jpeg",
			            "tmp_name" => $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_thumbnail_".$arResult["SECTION"]["ID"].".jpg",
			            "size" => filesize($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_thumbnail_".$arResult["SECTION"]["ID"].".jpg"),
			            "MODULE_ID" => "iblock"),
					"DETAIL_PICTURE" => array(
						"name" => "iblock_section_".$arResult["SECTION"]["ID"].".jpg",
			            "type" => "image/jpeg",
			            "tmp_name" => $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_".$arResult["SECTION"]["ID"].".jpg",
			            "size" => filesize($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/iblock_section_".$arResult["SECTION"]["ID"].".jpg"),
			            "MODULE_ID" => "iblock"),
			            );
				
				$DB->StartTransaction();
				$bs = new CIBlockSection;
				$res = $bs->Update($arResult["SECTION"]["ID"], $arFields);
				if(!$res)
				{
					$strWarning .= $bs->LAST_ERROR;
					$bVarsFromForm = true;
					$DB->Rollback();
				}
				else
				{
					$DB->Commit();
					
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
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arResult["SECTION"]["ID"]."/");
					BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$arResult["SECTION"]["IBLOCK_SECTION_ID"]."/");
					if ($arParams["AJAX_CALL"] == "Y")
					{
						$rsSection = CIBlockSection::GetList(Array(), array("ID" => $arResult["SECTION"]["ID"]));
						$arResult["SECTION"] = $rsSection->GetNext();
						$arResult["SECTION"]["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["SECTION"]["DETAIL_PICTURE"]);

						$arFields = array(
							"ID" => $arResult["SECTION"]["ID"],
							"SRC" => $arResult["SECTION"]["DETAIL_PICTURE"]["SRC"],
							"error" => "");
						$APPLICATION->RestartBuffer();
						?><?=CUtil::PhpToJSObject($arFields);?><?
						die();
					}
					else 
					{
						LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["SECTION_URL"], 
							array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"])));
					}
				}
				
			}
		}
	}
	elseif ($_REQUEST["edit"] == "cancel")
	{
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["SECTION_URL"], 
			array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arResult["SECTION"]["ID"])));
	}
	
	$arResult["ERROR_MESSAGE"] = $strWarning;
	$arResult["PAGE_TITLE"] = $arResult["SECTION"]["NAME"].GetMessage("P_TITLE");
	if($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["PAGE_TITLE"]);
		
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
			continue;
		if ($arPath["ID"] != $arResult["SECTION"]["ID"])
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["~SECTION_PAGE_URL"]);
		else 
			$APPLICATION->AddChainItem($arPath["NAME"]);
	}
	if($arParams["DISPLAY_PANEL"] && $USER->IsAuthorized() && CModule::IncludeModule("iblock"))
		CIBlock::ShowPanel($arParams["IBLOCK_ID"], 0, $arResult["SECTION"]["ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());
	$this->IncludeComponentTemplate();
?>