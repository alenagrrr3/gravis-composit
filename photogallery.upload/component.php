<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arParams["WATERMARK_MIN_PICTURE_SIZE"] = intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]);
if (!IsModuleInstalled("photogallery")):
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
elseif(!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return 0;
elseif ($arParams["BEHAVIOUR"] == "USER" && empty($arParams["USER_ALIAS"])):
	ShowError(GetMessage("P_GALLERY_EMPTY"));
	return 0;
endif;
include_once("functions.php"); 
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intVal($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intVal($arParams["SECTION_ID"]);
	$arParams["SECTION_CODE"] = trim($arParams["SECTION_CODE"]);
	$arParams["USER_ALIAS"] = trim($arParams["USER_ALIAS"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	$arParams["PERMISSION_EXTERNAL"] = trim($arParams["PERMISSION"]);
	
	$arParams["IMAGE_UPLOADER_ACTIVEX_CLSID"] = "718B3D1E-FF0C-4EE6-9F3B-0166A5D1C1B9";
	$arParams["IMAGE_UPLOADER_ACTIVEX_CONTROL_VERSION"] = "5,1,5,0";
	$arParams["IMAGE_UPLOADER_JAVAAPPLET_VERSION"] = "5.0.10.0";
	
	$arParams["THUMBNAIL_ACTIVEX_CLSID"] = "58C8ACD5-D8A6-4AC8-9494-2E6CCF6DD2F8";
	$arParams["THUMBNAIL_ACTIVEX_CONTROL_VERSION"] = "3,5,204,0";
	$arParams["THUMBNAIL_JAVAAPPLET_VERSION"] = "1.1.81.0";
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
			"sections_top" => "",
			"gallery" => "PAGE_NAME=gallery&USER_ALIAS=#USER_ALIAS#",
			"section" => "PAGE_NAME=section".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" )."&SECTION_ID=#SECTION_ID#");
		
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, 
				array("PAGE_NAME", "USER_ALIAS", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL", "login"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
/***************** ADDITIONAL **************************************/
$arParams["ALBUM_PHOTO"]["SIZE"] = (intVal($arParams["ALBUM_PHOTO_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_WIDTH"]) : 150);
$arParams["ALBUM_PHOTO_THUMBS"]["SIZE"] = (intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) > 0 ? intVal($arParams["ALBUM_PHOTO_THUMBS_WIDTH"]) : 70);
$arParams["THUMBS_SIZE"] = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($arParams["THUMBS_SIZE"]["WIDTH"], $arParams["THUMBS_SIZE"]["HEIGHT"]) = explode("/", $arParams["THUMBS_SIZE"]["STRING"]);
$arParams["THUMBS_SIZE"]["SIZE"] = (intVal($arParams["THUMBS_SIZE"]["WIDTH"]) > 0 ? intVal($arParams["THUMBS_SIZE"]["WIDTH"]) : 120);
$arParams["PREVIEW_SIZE"] = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["PREVIEW_SIZE"]));
list($arParams["PREVIEW_SIZE"]["WIDTH"], $arParams["PREVIEW_SIZE"]["HEIGHT"]) = explode("/", $arParams["PREVIEW_SIZE"]["STRING"]);
$arParams["PREVIEW_SIZE"]["SIZE"] = (intVal($arParams["PREVIEW_SIZE"]["WIDTH"]) > 0 ? intVal($arParams["PREVIEW_SIZE"]["WIDTH"]) : 300);
// Additional sights
$arParams["PICTURES_INFO"] = @unserialize(COption::GetOptionString("photogallery", "pictures"));
$arParams["PICTURES_INFO"] = (is_array($arParams["PICTURES_INFO"]) ? $arParams["PICTURES_INFO"] : array());
$arParams["PICTURES"] = array();
if (!empty($arParams["PICTURES_INFO"]) && is_array($arParams["ADDITIONAL_SIGHTS"]) && !empty($arParams["ADDITIONAL_SIGHTS"]))
{
	foreach ($arParams["PICTURES_INFO"] as $key => $val)
	{
		if (in_array(str_pad($key, 5, "_").$val["code"], $arParams["ADDITIONAL_SIGHTS"]))
			$arParams["PICTURES"][$val["code"]] = array(
				"size" => $arParams["PICTURES_INFO"][$key]["size"],
				"quality" => $arParams["PICTURES_INFO"][$key]["quality"]);
	}
}
$arParams["WATERMARK"] = ($arParams["WATERMARK"] == "N" ? "N" : "Y");
// min size for copyright
$arParams["WATERMARK_MIN_PICTURE_SIZE"] = (intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) > 0 ? intVal($arParams["WATERMARK_MIN_PICTURE_SIZE"]) : 100);
$arParams["UPLOAD_MAX_FILE"] = ((intVal($arParams["UPLOAD_MAX_FILE"]) <= 0 || intVal($arParams["UPLOAD_MAX_FILE"]) > 10) ? 10 : intVal($arParams["UPLOAD_MAX_FILE"])); 
$arParams["UPLOAD_MAX_FILE_SIZE"] = intVal($arParams["UPLOAD_MAX_FILE_SIZE"]);
$arParams["PATH_TO_FONT"] = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/").trim($arParams["PATH_TO_FONT"]);
$arParams["PATH_TO_FONT"] = (file_exists($arParams["PATH_TO_FONT"]) ? $arParams["PATH_TO_FONT"] : "");
$arParams["GALLERY_SIZE"] = intVal($arParams["GALLERY_SIZE"])*1024*1024;
$arParams["PATH_TO_TMP"] = preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/tmp/");
/***************** STANDART ****************************************/
	if(!isset($arParams["CACHE_TIME"]))
		$arParams["CACHE_TIME"] = 3600;
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
	$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
	$arParams["ADD_CHAIN_ITEM"] = ($arParams["ADD_CHAIN_ITEM"] == "N" ? "N" : "Y"); //Turn on by default
	$arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"]=="Y" ? "Y" : "N"); //Turn off by default
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default params
********************************************************************/
// 1. Permission
$arResult["SECTION"] = array();
$arResult["GALLERY"] = array();
$arResult["RETURN_DATA"] = array();
$arParams["PERMISSION"] = CIBlock::GetPermission($arParams["IBLOCK_ID"]);
if ("R" <= $arParams["PERMISSION"] && $arParams["PERMISSION"] < "W" && !empty($arParams["PERMISSION_EXTERNAL"]))
	$arParams["PERMISSION"] = $arParams["PERMISSION_EXTERNAL"];

if ($arParams["PERMISSION"] < "R"):
	ShowError(GetMessage("P_DENIED_ACCESS"));
	return 0;
endif;

$arResult["I"] = array(
	"ABS_PERMISSION" => $arParams["PERMISSION"]);
if ($arParams["BEHAVIOUR"] == "USER" && $GLOBALS["USER"]->IsAuthorized())
{
	$arResult["I"]["PERMISSION"] = $arResult["I"]["ABS_PERMISSION"];
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y",
		"CODE" => $arParams["USER_ALIAS"],
		"SECTION_ID" => 0);
	$db_res = CIBlockSection::GetList(array(), $arFilter, false, array("UF_GALLERY_SIZE"));
	if ($db_res && $res = $db_res->GetNext())
	{
		$arResult["GALLERY"] = $res;
		if (($arResult["I"]["ABS_PERMISSION"] < "W") && ($res["CREATED_BY"] == $GLOBALS["USER"]->GetId()))
		{
			$arResult["I"]["PERMISSION"] = "W";
			$arParams["PERMISSION"] = "W";
			
		}
	}
// 1.1 Fatal Error!
if (empty($arResult["GALLERY"]))
{
	ShowError(GetMessage("P_GALLERY_NOT_FOUND"));
	return 0;
}
elseif ($arResult["I"]["ABS_PERMISSION"] < "W" && 0 < $arParams["GALLERY_SIZE"] && 
	$arParams["GALLERY_SIZE"] < intVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]))
{
	ShowError(GetMessage("P_GALLERY_NOT_SIZE"));
	return 0;
}
}
// 1.2 Fatal Error!
if($arParams["PERMISSION"] < "W")
{
	ShowError(GetMessage("T_DETAIL_PERM_DEN"));
	return 0;
}
// 2. GD info
if (function_exists("gd_info"))
{
	$arGDInfo = gd_info();
	$bGD2 = ((strpos($arGDInfo['GD Version'], "2.") !== false) ? true : false);
}
$arResult["UPLOAD_MAX_FILE_SIZE"] = $arParams["UPLOAD_MAX_FILE_SIZE"]*1024*1024;
$arResult["SHOW_SIZE"] = ($bGD2 ? "Y" : "N");
$arResult["SHOW"]["TAGS"] = (IsModuleInstalled("search") ? "Y" : "N");

$bNeedClearCache = false;
$arError = array();

$arResult["~SECTION_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array(
	"USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => $arParams["SECTION_ID"]));
$arResult["~SECTION_LINK_EMTY"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], array(
	"USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => "#SECTION_ID#"));
$arResult["SECTION_LINK"] = htmlspecialchars($arResult["~SECTION_LINK"]);
$arResult["~GALLERY_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], array(
	"USER_ALIAS" => $arResult["GALLERY"]["CODE"]));
$arResult["GALLERY_LINK"] = htmlspecialchars($arResult["~GALLERY_LINK"]);

$arResult["~SECTIONS_TOP_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTIONS_TOP_URL"], array());
$arResult["SECTIONS_TOP_LINK"] = htmlspecialchars($arResult["~SECTIONS_TOP_LINK"]);

$cache = new CPHPCache;
/********************************************************************
				/Default params
********************************************************************/
/********************************************************************
				Action
********************************************************************/
if ($_REQUEST["save_upload"] == "Y" || ($_SERVER['REQUEST_METHOD'] == "POST" && empty($_POST)))
{
	$bPictureNumber = false;
	$result = array("FILE" => array(), "FILE_INFO" => array());
	$arProperties = array();
	$arPropertiesNeed = array();
	$arFiles = array();
	$arProp = array();
	$arWaterMark = array();
	$arSection = array();
	$bIblockSectionWasCreate = false;
	if ($_REQUEST["AJAX_CALL"] == "Y")
	{
		array_walk($_REQUEST, '__UnEscape');
		array_walk($_FILES, '__UnEscape');
	}
	if ($arParams["WATERMARK"] == "Y")
	{
		$arWaterMark = array(
			"text" => trim($_REQUEST["watermark"]), 
			"color" => $_REQUEST["watermark_color"], 
			"size" => $_REQUEST["watermark_size"],
			"position" =>  $_REQUEST["watermark_position"],
			"min_size_picture" => $arParams["WATERMARK_MIN_PICTURE_SIZE"],
			"use_copyright" => ($_REQUEST["watermark_copyright"] == "hide" ? "N" : "Y"),
			"path_to_font" => $arParams["PATH_TO_FONT"]);
	}
	
	if ($_SERVER['REQUEST_METHOD'] == "POST" && empty($_POST))
	{
		$arError["bad_post"] = array(
			"code" => "bad_post",
			"title" => str_replace("#SIZE#", intVal(ini_get('post_max_size')), GetMessage("P_ERROR_BAD_POST")));
	}
	elseif(!(check_bitrix_sessid()))
	{
		$arError["bad_sessid"] = array(
			"code" => "bad_sessid",
			"title" => GetMessage("IBLOCK_WRONG_SESSION"));
	}
	else 
	{
/************** Section ********************************************/
		if ($_REQUEST["photo_album_id"] == "new")
		{
			$cache_id = preg_replace("/[^a-z0-9]+/is", "_", $_REQUEST["PackageGuid"]);
			$cache_id = "image_uploader_".$cache_id."_create_new_album";
			$cache_path = "/bitrix/photogallery/image_uploader/";
			$tmp = array("clear_cache" => $_GET["clear_cache"],
				"SESS_CLEAR_CACHE" => $_SESSION["SESS_CLEAR_CACHE"],
				"clear_cache_session" => $_GET["clear_cache_session"]);
			$_GET["clear_cache"] = "N";
			$_SESSION["SESS_CLEAR_CACHE"] = "N";
			$_GET["clear_cache_session"] = "N";
			
			if ($cache->InitCache(3600, $cache_id, $cache_path))
				$res = $cache->GetVars();
			$res = (!is_array($res) ? array() : $res);
			$arParams["SECTION_ID"] = intVal($res["SECTION_ID"]);
			if ($arParams["SECTION_ID"] <= 0)
			{
				$arFields = Array(
					"ACTIVE" => "Y",
					"IBLOCK_ID"=>$arParams["IBLOCK_ID"],
					"IBLOCK_SECTION_ID" => ($arParams["BEHAVIOUR"] == "USER" ? $arResult["GALLERY"]["ID"] : 0),
					"DATE"=>ConvertTimeStamp(time()),
					"UF_DATE"=>ConvertTimeStamp(time()),
					"NAME"=>(strLen(GetMessage("P_NEW_ALBUM")) <= 0 ? "New album" : GetMessage("P_NEW_ALBUM")));
				$GLOBALS["UF_DATE"] = $arFields["UF_DATE"];

				$bs = new CIBlockSection;
				$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
				$ID = $bs->Add($arFields);
				if($ID <= 0)
				{
					$arError["bad_section"] = array(
						"code" => "bad_section", 
						"title" => $bs->LAST_ERROR);
				}
				else
				{
					$arParams["SECTION_ID"] = $ID;
					$bIblockSectionWasCreate = true;
					$cache->StartDataCache(3600, $cache_id, $cache_path);
					$cache->EndDataCache(array("SECTION_ID"=>$ID));
				}
				$_GET["clear_cache"] = $tmp["clear_cache"];
				$_SESSION["SESS_CLEAR_CACHE"] = $tmp["SESS_CLEAR_CACHE"];
				$_GET["clear_cache_session"] = $tmp["clear_cache_session"];
			}
		}
		else 
		{
			$arParams["SECTION_ID"] = intVal($_REQUEST["photo_album_id"]);
		}
		
		$arFilter = array(
			"ACTIVE" => "Y",
			"GLOBAL_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"ID" => intVal($arParams["SECTION_ID"]));
		if ($arParams["BEHAVIOUR"] == "USER" && !$bIblockSectionWasCreate)
		{
			$arFilter["!ID"] = $arResult["GALLERY"]["ID"];
			$arFilter["RIGHT_MARGIN"] = $arResult["GALLERY"]["RIGHT_MARGIN"];
			$arFilter["LEFT_MARGIN"] = $arResult["GALLERY"]["LEFT_MARGIN"];
		}
		$db_res = CIBlockSection::GetList(Array(), $arFilter);
		if ($db_res && $res = $db_res->GetNext())
			$arSection = $res;
	}
	
	if (empty($arSection))
	{
		$arError["bad_section"] = array(
			"code" => "bad_section",
			"title" => "BAD SECTION");
	}
	if (empty($arError))
	{
/************** Iblock Properties **********************************/
		$arPictureSights = array(
			"REAL_PICTURE" => array(
				"object_name" => "Thumbnail3_",
				"code" => "real",
				"width" => false,
				"height" => false),
			"THUMBNAIL_PUCTURE" => array(
				"object_name" => "Thumbnail1_",
				"code" => "thumbnail",
				"width" => $arParams["THUMBS_SIZE"]["SIZE"],
				"height" => $arParams["THUMBS_SIZE"]["SIZE"]),
			"PREVIEW_PUCTURE" => array(
				"object_name" => "Thumbnail2_",
				"code" => "preview",
				"width" => $arParams["PREVIEW_SIZE"]["SIZE"],
				"height" => $arParams["PREVIEW_SIZE"]["SIZE"]));
			
		if (is_array($arParams["PICTURES"]) && !empty($arParams["PICTURES"]))
		{
			$counter = 3; 
			foreach ($arParams["PICTURES"] as $key => $val)
			{
				$counter++;
				$arPictureSights[strToUpper($key)."_PICTURE"] = array(
					"object_name" => "Thumbnail".$counter."_",
					"code" => $key,
					"width" => $arParams["PICTURES"][$key]["size"],
					"height" => $arParams["PICTURES"][$key]["size"]);
			}
		}
			
		foreach ($arPictureSights as $key => $val)
		{
			if ($key == "THUMBNAIL_PUCTURE" || $key == "PREVIEW_PUCTURE")
				continue;
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!$db_res || !($res = $db_res->Fetch()))
				$arPropertiesNeed[] = $key;
		}
		if (!empty($arPropertiesNeed))
		{
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $Property)
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "F",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($Property))) > 0 ? GetMessage("P_".strToUpper($Property)) : strToUpper($Property)),
					"CODE" => strToUpper($Property),
					"FILE_TYPE" => "jpg, gif, bmp, png, jpeg"));
			}
		}
		// Check Public and Moderate property
		$arPropertiesNeed = array();
		foreach (array("PUBLIC_ELEMENT", "APPROVE_ELEMENT") as $key)
		{
			$db_res = CIBlock::GetProperties($arParams["IBLOCK_ID"], array(), array("CODE" => $key));
			if (!$db_res || !($res = $db_res->Fetch()))
				$arPropertiesNeed[] = $key;
		}
		if (!empty($arPropertiesNeed))
		{
			$obProperty = new CIBlockProperty;
			foreach ($arPropertiesNeed as $Property)
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "S",
					"MULTIPLE" => "N",
					"NAME" => (strLen(GetMessage("P_".strToUpper($Property))) > 0 ? GetMessage("P_".$Property) : $Property),
					"CODE" => $Property));
			}
		}
			
/************** Post data ******************************************/
		for ($i = 1; $i <= intVal($_REQUEST["FileCount"]); $i++)
		{
			$sIndex = rand()."_";
			$bRealFile = false;
			if (!empty($_FILES["Thumbnail3_".$i]) && !empty($_FILES["Thumbnail3_".$i]["size"]))
				$bRealFile = "Thumbnail3_".$i;
			elseif (!empty($_FILES["SourceFile_".$i]) && !empty($_FILES["SourceFile_".$i]["size"]))
				$bRealFile = "SourceFile_".$i;
			if (!$bRealFile)
				continue;

			$arRealFile = $_FILES[$bRealFile]; 
			$arRealFile["pathinfo"] = pathinfo($arRealFile["name"]);
			$arRealFile["basename"] = str_replace(".".$arRealFile["pathinfo"]["extension"], "", $arRealFile["pathinfo"]["basename"]);
			if ($_REQUEST["FileName_".$i])
			{
				$arRealFile["pathinfo1"] = pathinfo($_REQUEST["FileName_".$i]);
				$arRealFile["basename"] = str_replace(".".$arRealFile["pathinfo1"]["extension"], "", $arRealFile["pathinfo1"]["basename"]);;
			}
			
			$arRealFile["name"] = $arRealFile["basename"].".".$arRealFile["pathinfo"]["extension"];
			$arRealFile["image"] = false;
			
			if (!empty($_REQUEST["ExifDateTime_".$i]))
			{
				$arRealFile["ExifDateTime"] = $_REQUEST["ExifDateTime_".$i];
				$arRealFile["ExifTimeStamp"] = MakeTimeStamp($arRealFile["ExifDateTime"], "YYYY:MM:DD HH:MI:SS");
			}
			
			if (empty($arRealFile["ExifTimeStamp"]) && function_exists("exif_read_data"))
			{
				$arr = exif_read_data($arRealFile["tmp_name"]);
				$arRealFile["ExifTimeStamp"] = $arr["FILE"]["FileDateTime"];
			}
			if (empty($arRealFile["ExifTimeStamp"]))
			{
				$arRealFile["ExifTimeStamp"] = filemtime($arRealFile["tmp_name"]);
			}
				
			foreach ($arPictureSights as $key => $Sight)
			{
				if ($key == "REAL_PICTURE")
				{
					$File = $arRealFile;
					$_REQUEST["photo_resize_size"] = intVal($_REQUEST["photo_resize_size"]);
					// To do check for resize
					if ($_REQUEST["photo_resize_size"] > 0)
					{
						$Sight["width"] = 1024; $Sight["height"] = 768;
						if ($_REQUEST["photo_resize_size"] > 1)
						{
							$Sight["width"] = 800; $Sight["height"] = 600;
							if ($_REQUEST["photo_resize_size"] > 2)
							{
								$Sight["width"] = 640; $Sight["height"] = 480;
							}
						}
					}
					if ($_REQUEST["photo_resize_size"] > 0 || !empty($arWaterMark["text"]))
					{
						$File["tmp_name"] = $arParams["PATH_TO_TMP"].$sIndex.$File["name"];
						__ResizeImage($File, $arRealFile, $Sight, 1, $arWaterMark);
					}
				}
				else
				{
					// $File - info about thumbs
					if (!empty($_FILES[$Sight["object_name"].$i]) && !empty($_FILES[$Sight["object_name"].$i]["size"]))
					{
						$File = $_FILES[$Sight["object_name"].$i];
						$File["name"] = $arRealFile["basename"].$Sight["code"].".".$arRealFile["pathinfo"]["extension"];
					}
					else 
					{
						$File = $_FILES[$bRealFile]; 
						$File["name"] = $arRealFile["basename"]."_".$Sight["code"].".".$arRealFile["pathinfo"]["extension"];
						$File["tmp_name"] = $arParams["PATH_TO_TMP"].$sIndex.$File["name"];
						__ResizeImage($File, $arRealFile, $Sight, 1, $arWaterMark);
					}
				}
				$arFiles[$i][$key] = $File;
				$arProp[$i][$key] = array("n0" => $File);
			}
			$arFiles[$i]["basename"] = $arRealFile["basename"];
			$arFiles[$i]["name"] = $arRealFile["name"];
			@imagedestroy($arRealFile["image"]);
		}
	}

	if (empty($arError) && empty($arFiles))
	{
		$arError["empty_post"] = array(
			"code" => "empty_post",
			"title" => GetMessage("P_EMPTY_POST"));
	}
	
	if (empty($arError))
	{
/************** Album cover ****************************************/
		if (intVal($arSection["PICTURE"]) <= 0 ? true : false)
		{
			$arAlbumsFoto = array();
			$arAlbumSights = array();
			$arAlbumSights["ALBUM_PICTURE"] = array(
				"code" => "album",
				"notes" => "for_album",
				"width" => $arParams["ALBUM_PHOTO"]["SIZE"],
				"height" => $arParams["ALBUM_PHOTO"]["SIZE"]);
			$arAlbumSights["ALBUM_PICTURE_THUMBS"] = array(
				"code" => "album_thumbs",
				"notes" => "for_album",
				"width" => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"],
				"height" => $arParams["ALBUM_PHOTO_THUMBS"]["SIZE"]);
			$arTmp = $arFiles;
			$arTmp = array_shift($arTmp);
			$arRealFile = $arTmp["REAL_PICTURE"];
			
			foreach ($arAlbumSights as $key => $Sight)
			{
				$File = $arRealFile;
				$File["basename"] = str_replace(".".$File["pathinfo"]["extension"], "", $File["pathinfo"]["basename"]);;
				$File["name"] = $File["basename"]."_".$Sight["code"].".".$File["pathinfo"]["extension"];
				$File["tmp_name"] = $arParams["PATH_TO_TMP"].$File["name"];
				__ResizeImage($File, $arRealFile, $Sight, 2);
				$File["MODULE_ID"] = "iblock";
				$arAlbumsFoto[$key] = $File;
			}
			@imagedestroy($arRealFile["image"]);
			
			$bs = new CIBlockSection;
			$arFields = Array(
				"PICTURE" => $arAlbumsFoto["ALBUM_PICTURE_THUMBS"],
				"DETAIL_PICTURE" => $arAlbumsFoto["ALBUM_PICTURE"]);
				
			$res = $bs->Update($arSection["ID"], $arFields, false, false);
			if(!$res)
			{
				$result["ALBUM"] = array("error" => $strError);
			}
			else
			{
				$result["ALBUM"] = array("status" => "success");
				$bNeedClearCache = true;
			}
			
			@unlink($arAlbumsFoto["ALBUM_PICTURE_THUMBS"]["tmp_name"]);
			@unlink($arAlbumsFoto["ALBUM_PICTURE"]["tmp_name"]);
		}
/************** Saving photos **************************************/
		$iFileSize = 0;
		foreach ($arFiles as $i => $File)
		{
			$Prop = $arProp[$i];
			$Prop["PUBLIC_ELEMENT"] = array("n0" => ($_REQUEST["Public_".$i] == "Y" ? "Y" : "N"));
			$Prop["APPROVE_ELEMENT"] = array("n0" => "N");
			unset($Prop["THUMBNAIL_PUCTURE"]);
			unset($Prop["PREVIEW_PUCTURE"]);
			
			$number = intVal($_REQUEST["PackageIndex"]) * intVal($arParams["UPLOAD_MAX_FILE"]) + $i;
			$strError = "";
			$res_file = array("status" => "success");
			$ID = 0;
			
			if ($arResult["UPLOAD_MAX_FILE_SIZE"] > 0)
			{
				foreach ($File as $k => $v)
				{
					if ($v["size"] > $arResult["UPLOAD_MAX_FILE_SIZE"])
					{
						$strError = str_replace("#UPLOAD_MAX_FILE_SIZE#", $arParams["UPLOAD_MAX_FILE_SIZE"], GetMessage("P_ERR_UPLOAD_MAX_FILE_SIZE"));
						break;
					}
				}
			}
			
			if (empty($strError))
			{
				$arFields = Array(
					"ACTIVE" => "Y",
					"MODIFIED_BY" => $USER->GetID(),
					"IBLOCK_SECTION" => $arSection["ID"],
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"NAME" => (!empty($_REQUEST["Title_".$number]) ? $_REQUEST["Title_".$number] : $File["basename"]),
					"CODE" => $File["REAL_PICTURE"]["name"],
					"TAGS" => $_REQUEST["Tags_".$number],
					"PREVIEW_PICTURE" => $File["THUMBNAIL_PUCTURE"],
					"PREVIEW_TEXT" => $_REQUEST["Description_".$number],
					"PREVIEW_TEXT_TYPE" => "text",
					"DETAIL_PICTURE" => $File["PREVIEW_PUCTURE"],
					"DETAIL_TEXT" => $_REQUEST["Description_".$number],
					"DETAIL_TEXT_TYPE" => "text",
					"PROPERTY_VALUES" => $Prop);
				$arFields["NAME"] = (!empty($arFields["NAME"]) ? $arFields["NAME"] : $File["REAL_PICTURE"]["name"]);
				$arFields["DATE_CREATE"] = (intVal($arRealFile["ExifTimeStamp"]) > 0 ? 
					ConvertTimeStamp($arRealFile["ExifTimeStamp"], "FULL") : $arFields["DATE_CREATE"]);
				
				$bs = new CIBlockElement;
				$ID = $bs->Add($arFields);
				if($ID <= 0)
				{
					$tmp = $bs->LAST_ERROR;
					$arTmp = explode("<br>", $tmp);
					if (!empty($arTmp) && !empty($arTmp[0]))
						$strError .= $arTmp[0];
					else 
						$strError .= $bs->LAST_ERROR;
					$strError .= "<br>";
				}
				else
					CIBlockElement::RecalcSections($ID);
			}
			
			if (intVal($ID) <= 0)
			{
				$bVarsFromForm = true;
				$res_file = array("status" => "error", "error" => $strError);
			}
			else
			{
				$arFields["ID"] = $ID;
				if(function_exists('BXIBlockAfterSave'))
					BXIBlockAfterSave($arFields);
				$iFileSize += doubleVal($File["REAL_PICTURE"]["size"]);
			}
			// Main info about file
			$result["FILE"][$File["REAL_PICTURE"]["name"]] = $res_file;
			// Additional info about file
			$res_file["id"] = $arFields["ID"];
			$res_file["number"] = $i;
			$res_file["title"] = $arFields["NAME"];
			$res_file["description"] = $arFields["PREVIEW_TEXT"];
			$result["FILE_INFO"][$File["REAL_PICTURE"]["name"]] = $res_file;
			
			foreach ($File as $key => $val)
				@unlink($val["tmp_name"]);
		}
		
		if ($arParams["BEHAVIOUR"] == "USER")
		{
			$bs = new CIBlockSection;
			$arFields = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"], 
				"UF_GALLERY_SIZE" => doubleVal($arResult["GALLERY"]["UF_GALLERY_SIZE"]) + $iFileSize);
			$GLOBALS["UF_GALLERY_SIZE"] = $arFields["UF_GALLERY_SIZE"];
			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("IBLOCK_".$arParams["IBLOCK_ID"]."_SECTION", $arFields);
			$res = $bs->Update($arResult["GALLERY"]["ID"], $arFields, false, false);
			
		}
		$bNeedClearCache = true;
	}
/************** Cache **********************************************/
	if ($bNeedClearCache == true)
	{
		$nameSpace = "bitrix";
		$pthToComponent = strToLower(trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/".BX_PERSONAL_ROOT."/components/")));
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
							BXClearCache(True, $arComponentDescription["CACHE_PATH"]);
					}
				}
			}
		}
		if ($bIblockSectionWasCreate)
		{
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/");
		}
		else 
		{
			$sUserAlias = ($arParams["BEHAVIOUR"] == "USER" && !empty($arParams["USER_ALIAS"]) ? $arParams["USER_ALIAS"] : "!simple!");
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section/".$arSection["ID"]."/");
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$sUserAlias."/".$arSection["IBLOCK_SECTION_ID"]."/");
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/section_list/".$sUserAlias."/".$arSection["ID"]."/");
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/all/");
			BXClearCache(True, "/bitrix/photogallery/".$arParams["IBLOCK_ID"]."/detail_list/".$arSection["ID"]."/");
		}
	}
	
	$bVarsFromForm = ($bVarsFromForm ? $bVarsFromForm : !empty($arError));
	
/************** Answer *********************************************/
	$uploader = array();
	if ($_REQUEST["CACHE_RESULT"] == "Y")
	{
		$tmp = array("clear_cache" => $_GET["clear_cache"],
			"SESS_CLEAR_CACHE" => $_SESSION["SESS_CLEAR_CACHE"],
			"clear_cache_session" => $_GET["clear_cache_session"]);
		$_GET["clear_cache"] = "N";
		$_SESSION["SESS_CLEAR_CACHE"] = "N";
		$_GET["clear_cache_session"] = "N";
		$cache_id = preg_replace("/[^a-z0-9]+/is", "_", $_REQUEST["PackageGuid"]);
		$cache_id = "image_uploader_".$cache_id;
		$cache_path = "/bitrix/photogallery/image_uploader/";
		if ($cache->InitCache(3600, $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			if (is_array($res["uploader"]))
				$uploader = $res["uploader"];
		}
	}
	if (empty($uploader))
	{
		$uploader = array(
			"status" => "success",
			"error" => "",
			"files" => array());
	}
	if (empty($result) && !empty($strWarning))
	{
		$uploader["status"] = "error";
		$uploader["error"] .= $strWarning;
	}
	if (is_array($result["ALBUM"]))
	{
		$uploader["album"] = array();
		foreach ($result["ALBUM"] as $key => $val)
			$uploader["album"][$key] = $val;
	}
	if (is_array($result["FILE"]))
	{
		foreach ($result["FILE"] as $key => $val)
			$uploader["files"][$key] = $val;
	}
	
	if ($_REQUEST["CACHE_RESULT"] == "Y")
	{
		$cache->Clean($cache_id, $cache_path);
		$cache->StartDataCache(3600, $cache_id, $cache_path);
		$cache->EndDataCache(array("uploader"=>$uploader));
		$_GET["clear_cache"] = $tmp["clear_cache"];
		$_SESSION["SESS_CLEAR_CACHE"] = $tmp["SESS_CLEAR_CACHE"];
		$_GET["clear_cache_session"] = $tmp["clear_cache_session"];
	}
	
	$uploader["section_id"] = $arParams["SECTION_ID"];
	$uploader["url"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
				array("USER_ALIAS" => $arResult["GALLERY"]["CODE"], "SECTION_ID" => $arSection["ID"]));
	$arResult["RETURN_DATA"] = $uploader;
	if ($_REQUEST["FORMAT_ANSWER"] != "return")
	{
		if ($_REQUEST["redirect"] != "Y")
		{
			$APPLICATION->RestartBuffer();
			if ($_REQUEST["CONVERT"] == "Y")
				array_walk($uploader, '__Escape');
			?><?=CUtil::PhpToJSObject($uploader);?><?
			die();
		}
		elseif (!$bVarsFromForm)
		{
			LocalRedirect($uploader["url"]);
		}
	}
	else 
	{
		$arResult["RETURN_DATA"]["current_files"] = $result["FILE_INFO"];
		if ($_REQUEST["AJAX_CALL"] == "Y" || !$bVarsFromForm)
		{
			return $arResult["RETURN_DATA"];
		}
	}
}
/********************************************************************
				/Action
********************************************************************/
$arResult["ERROR_MESSAGE"] = $strWarning;
if (!empty($arParams["SECTION_CODE"]) || !empty($arParams["SECTION_ID"]))
{
	$arFilter = array(
		"ACTIVE" => "Y",
		"GLOBAL_ACTIVE" => "Y",
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"IBLOCK_ACTIVE" => "Y");
	if(strlen($arParams["SECTION_CODE"]) > 0)
		$arFilter["CODE"]=$arParams["SECTION_CODE"];
	else
		$arFilter["ID"]=$arParams["SECTION_ID"];
	
	$rsSection = CIBlockSection::GetList(Array(), $arFilter);
	
	if ($rsSection && $arResult["SECTION"] = $rsSection->GetNext())
	{
		$rsPath = GetIBlockSectionPath($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"]);
		while($arPath=$rsPath->GetNext())
		{
			if ($arParams["BEHAVIOUR"] == "USER" && $arPath["ID"] == $arResult["GALLERY"]["ID"])
			{
				$arPath["SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~GALLERY_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"]));
			}
			else 
			{
				$arPath["SECTION_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~SECTION_URL"], 
					array("USER_ALIAS" => $arParams["USER_ALIAS"], "SECTION_ID" => $arPath["ID"]));
			}
			$arResult["SECTION"]["PATH"][] = $arPath;
		}
	}
}

if ($arParams["DISPLAY_PANEL"] == "Y" && $GLOBALS["USER"]->IsAuthorized())
{
	CIBlock::ShowPanel($arParams["IBLOCK_ID"], $arResult["SECTION"]["ID"], $arResult["SECTION"]["IBLOCK_SECTION_ID"], $arParams["IBLOCK_TYPE"], false, $this->GetName());			
}

if (!empty($arResult["SECTION"]["PATH"]) && is_array($arResult["SECTION"]["PATH"]))
{
	foreach($arResult["SECTION"]["PATH"] as $arPath)
	{
		if ($arParams["ADD_CHAIN_ITEM"] == "N" && !empty($arResult["GALLERY"]) && $arResult["GALLERY"]["ID"] == $arPath["ID"])
			continue;
		if ($arPath["ID"] != $arResult["SECTION"]["ID"])
			$APPLICATION->AddChainItem($arPath["NAME"], $arPath["SECTION_PAGE_URL"]);
		else
			$APPLICATION->AddChainItem($arPath["NAME"]);
	}
}
elseif ($arParams["BEHAVIOUR"] == "USER")
{
	$APPLICATION->AddChainItem($arResult["GALLERY"]["NAME"], $arResult["GALLERY_LINK"]);
}

if($arParams["SET_TITLE"])
{
	if (!empty($arResult["SECTION"]["NAME"]))
		$APPLICATION->SetTitle($arResult["SECTION"]["NAME"]);
	elseif (!empty($arResult["GALLERY"]["NAME"]))
		$APPLICATION->SetTitle($arResult["GALLERY"]["NAME"]);
	else 
		$APPLICATION->SetTitle(getMessage("P_TITLE"));
}

// For form List
$arResult["SECTION_LIST"] = array();
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
$rsIBlockSectionList = CIBlockSection::GetTreeList($arFilter);
$iDiff = ($arParams["BEHAVIOUR"] == "USER" ? 2 : 1);
while ($arSection = $rsIBlockSectionList->GetNext())
{
	$arSection["NAME"] = str_repeat(" . ", ($arSection["DEPTH_LEVEL"] - $iDiff)).$arSection["NAME"];
	$arResult["SECTION_LIST"][$arSection["ID"]] = $arSection["NAME"];
}

$this->IncludeComponentTemplate();

if ($_REQUEST["FORMAT_ANSWER"] == "return")
{
	return $arResult["RETURN_DATA"];
}
?>