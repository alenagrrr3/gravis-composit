<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
//Prepare params
$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
$arParams["ELEMENT_ID"] = intval($arParams["ELEMENT_ID"]);
$arParams["PATH_TO_FILE"] = intval($arParams["PATH_TO_FILE"]);
$arParams["DURATION"] = trim($arParams["DURATION"]);
$arParams["LOGO"] = trim($arParams["LOGO"]);
$arParams["DEFAULT_SMALL_IMAGE"] = trim($arParams["DEFAULT_SMALL_IMAGE"]);
$arParams["DEFAULT_BIG_IMAGE"] = trim($arParams["DEFAULT_BIG_IMAGE"]);
$arParams["WIDTH"] = intval($arParams["WIDTH"])>0
	?intval($arParams["WIDTH"])
	:640;
$arParams["HEIGHT"] = intval($arParams["HEIGHT"])>0
	?intval($arParams["HEIGHT"])
	:480;
$arParams["CACHE_TIME"] = isset($arParams["CACHE_TIME"])
	?intval($arParams["CACHE_TIME"])
	:3600;
$arParams["SORT_BY1"] = trim($arParams["SORT_BY1"]);
if(strlen($arParams["SORT_BY1"])<=0)
	$arParams["SORT_BY1"] = 'SORT';
if($arParams["SORT_ORDER1"]!='ASC')
	 $arParams["SORT_ORDER1"]='DESC';

if($arParams["IBLOCK_ID"]<=0 || $arParams["PATH_TO_FILE"]<=0 || !CModule::IncludeModule("iblock"))
	return false;

$arParams["DISPLAY_PANEL"] = isset($arParams["DISPLAY_PANEL"]) ? $arParams["DISPLAY_PANEL"] : "Y";

//SELECT
$arSelect = array(
	"ID",
	"NAME",
	"IBLOCK_SECTION_ID",
	"PREVIEW_TEXT",
	"PREVIEW_PICTURE",
	"DETAIL_PICTURE",
	"IBLOCK_TYPE_ID",
	"PROPERTY_".$arParams["PATH_TO_FILE"],
);
if($arParams["DURATION"]>0)
	$arSelect[]="PROPERTY_".$arParams["DURATION"];
//WHERE
$arFilter = array(
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"ACTIVE" => 'Y',
	"IBLOCK_ACTIVE" => 'Y',
);
if(strlen($arParams["IBLOCK_TYPE"])>0)
	$arFilter["IBLOCK_TYPE"] = $arParams["IBLOCK_TYPE"];
if($arParams["SECTION_ID"]>0)
	$arFilter["SECTION_ID"] = $arParams["SECTION_ID"];
//ORDER BY
$arSort = array(
	$arParams["SORT_BY1"] => $arParams["SORT_ORDER1"],
);


global $BX_TV_PREFIX;
if(!isset($BX_TV_PREFIX))
	$BX_TV_PREFIX = 0;
else
	$BX_TV_PREFIX = intval($BX_TV_PREFIX)+1;

if(!class_exists("__ciblocktv"))
{
	class __CIBlockTV
	{
		function Prepare($Value)
		{
			return str_replace(array("\r\n", "\r", "\n"), array("<br>", "<br>", "<br>"), CUtil::addslashes(htmlspecialchars($Value)));
		}
	}
}

if($this->StartResultCache(false, array($USER->GetGroups(), $BX_TV_PREFIX)))
{
	$rsProperty = CIBlockProperty::GetByID(
		$arParams["PATH_TO_FILE"],
		$arParams["IBLOCK_ID"]
	);

	if(!$arProperty = $rsProperty->Fetch())
	{
		$this->AbortResultCache();
		return false;
	}

	$arResult = array(
		"RAW_SECTIONS" => array(),
		"RAW_ELEMENTS" => array(),
		"PREFIX" => $BX_TV_PREFIX,
		"ELEMENT_CNT" => 0,
	);

	$rsElements = CIBlockElement::GetList(
		$arSort,
		$arFilter,
		false,
		false,
		$arSelect
	);

	//Get Elements
	while($arElements = $rsElements->Fetch())
	{
		if(intval($arElements["IBLOCK_SECTION_ID"])>0)
		{
			$arResult["RAW_ELEMENTS"][$arElements["ID"]] = $arElements;
			$arResult["RAW_SECTIONS_ID"][] = intval($arElements["IBLOCK_SECTION_ID"]);
			$arResult["ELEMENT_CNT"]++;
		}
	}

	//Get Sections
	if($arParams["SECTION_ID"]<=0 && count($arResult["RAW_SECTIONS_ID"])>0)
	{
		$arResult["RAW_SECTIONS_ID"] = array_unique($arResult["RAW_SECTIONS_ID"]);
		$rsSections = CIBlockSection::GetList(
			$arSort,
			array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"ID" => $arResult["RAW_SECTIONS_ID"],
			)
		);

		while($arSections = $rsSections->Fetch())
			$arResult["RAW_SECTIONS"][$arSections["ID"]] = $arSections;
	}

	$IBLOCK_TYPE_ID = false;

	//Prepare elements
	foreach($arResult["RAW_ELEMENTS"] as $key=>$arItem)
	{
		$IBLOCK_TYPE_ID = $arItem["IBLOCK_TYPE_ID"];

		$SectionId = $arParams["SECTION_ID"]>0
			?$arParams["SECTION_ID"]
			:intval($arItem["IBLOCK_SECTION_ID"]);

		$Duration = $arItem["PROPERTY_".$arParams["DURATION"]."_VALUE"];

		$PathToFile = $arProperty["PROPERTY_TYPE"] == 'F'
			?CFile::GetPath($arItem["PROPERTY_".$arParams["PATH_TO_FILE"]."_VALUE"])
			:$arItem["PROPERTY_".$arParams["PATH_TO_FILE"]."_VALUE"];


		if(
			$PathToFile
			&& file_exists($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile)
			&& is_file($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile)
		)
		{
			$FileSize = filesize($_SERVER["DOCUMENT_ROOT"]."/".$PathToFile);
			if($FileSize)
				$FileSize = round(sprintf("%u", $FileSize)/1024/1024, 2);
			if($FileSize <= 0)
				$FileSize = "";

			$ext = strtolower(substr($PathToFile, -4));
			if($ext == ".wmv" || $ext == "wma")
				$FileType = "wmv";
			else
				$FileType = "flv";
		}
		else
		{
			$FileSize = "";
			$FileType = "flv";
		}


		$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]] = array(
			"NAME" => $arItem["NAME"],
			"PREVIEW_TEXT" => $arItem["PREVIEW_TEXT"],
			"PREVIEW_PICTURE" => CFile::GetPath($arItem["PREVIEW_PICTURE"]),
			"DETAIL_PICTURE" => CFile::GetPath($arItem["DETAIL_PICTURE"]),
			"DURATION" => $Duration,
			"FILE_SIZE" => $FileSize,
			"FILE" => $PathToFile,
			"TYPE" => $FileType,
			"ID" => $arItem["ID"],
			"IBLOCK_SECTION_ID" => $arItem["IBLOCK_SECTION_ID"],
		);

		if(!$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["PREVIEW_PICTURE"])
			$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["PREVIEW_PICTURE"] = $arParams["DEFAULT_SMALL_IMAGE"];
		if(!$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["DETAIL_PICTURE"])
			$arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["DETAIL_PICTURE"] = $arParams["DEFAULT_BIG_IMAGE"];

		if(!isset($arResult["SELECTED_ELEMENT"]))
		{
			if($arParams["ELEMENT_ID"]<=0 || $arParams["ELEMENT_ID"] == $key)
			{
				$arResult["SELECTED_ELEMENT"] = array(
					"VALUES" => $arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]],
					"FILE" => $arResult["SECTIONS"][$SectionId]["ELEMENTS"][$arItem["ID"]]["FILE"],
				);
			}
		}
	}

	if(!isset($arResult["SELECTED_ELEMENT"]))
	{
		$this->AbortResultCache();
		return false;
	}

	global $USER;
	if($arParams["DISPLAY_PANEL"] == "Y" && $USER->IsAuthorized() && CModule::IncludeModule("intranet") && CIBlock::GetPermission($arParams["IBLOCK_ID"])>='U')
	{
		$arResult["IBLOCK_TYPE_ID"] = $IBLOCK_TYPE_ID;
		$arResult["CAN_EDIT"] = "Y";
	}

	$this->SetResultCacheKeys(array(
		"CAN_EDIT", "IBLOCK_TYPE_ID"
	));

	$this->IncludeComponentTemplate();
}


global $USER;
if($arResult["CAN_EDIT"] == "Y")
{
	if(defined("BX_AJAX_PARAM_ID"))
		$return_url = $APPLICATION->GetCurPageParam("", array(BX_AJAX_PARAM_ID));
	else
		$return_url = $APPLICATION->GetCurPageParam();

	$SECTION_ID = $arParams["SECTION_ID"];

	global $INTRANET_TOOLBAR;
	$INTRANET_TOOLBAR->AddButton(
		array(
				'TEXT' => GetMessage('C_IBLOCK.TV_ADD_VIDEO'),
				'TITLE' => GetMessage('C_IBLOCK.TV_ADD_VIDEO_TITLE'),
				'ICON' => 'add',
				'SORT' => 1000,
				'ONCLICK' => 'javascript:'.$APPLICATION->GetPopupLink(
						array(
							"URL" => "/bitrix/admin/iblock_element_edit.php?type=".$arResult["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arParams["IBLOCK_ID"]."&filter_section=".$SECTION_ID."&IBLOCK_SECTION_ID=".$SECTION_ID."&return_url=".UrlEncode($return_url)."&bxpublic=Y&from_module=iblock",
							"PARAMS" => array(
								"width" => 700,
								'height' => 500,
								'resize' => false,
							),
						)
				)
			)
		);

	$urlElementAdminPage = COption::GetOptionString("iblock","combined_list_mode")=="Y"?"iblock_list_admin.php":"iblock_element_admin.php";
	if($SECTION_ID > 0)
		$url = "/bitrix/admin/".$urlElementAdminPage."?type=".$arResult["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arParams["IBLOCK_ID"]."&find_section_section=".$SECTION_ID;
	else
		$url = "/bitrix/admin/".$urlElementAdminPage."?type=".$arResult["IBLOCK_TYPE_ID"]."&lang=".LANGUAGE_ID."&IBLOCK_ID=".$arParams["IBLOCK_ID"]."&find_el_y=Y";

	$INTRANET_TOOLBAR->AddButton(
		array(
				'TEXT' => GetMessage('C_IBLOCK.TV_MANAGE_VIDEO'),
				'TITLE' => GetMessage('C_IBLOCK.TV_MANAGE_VIDEO_TITLE'),
				'ICON' => 'settings',
				'SORT' => 1200,
				'HREF' => $url
			)
		);
}

//include js
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/silverlight.js').'"></script>', true);
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js').'"></script>', true);
$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js?v='.filemtime($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/player/mediaplayer/flvscript.js').'"></script>', true);
?>
