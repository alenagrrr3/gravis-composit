<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}
$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res))
{
	foreach ($res as $key => $val)
	{
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"];
	}
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PHOTO_SETTINGS" => array(
			"NAME" => GetMessage("P_PHOTO_SETTINGS")),
		"THUMBS_SETTINGS" => array(
			"NAME" => GetMessage("P_PREVIEW"),
			"PARENT" => "PHOTO_SETTINGS"),
		"DETAIL_SETTINGS" => array(
			"NAME" => GetMessage("P_DETAIL"),
			"PARENT" => "PHOTO_SETTINGS"),
		"ORIGINAL_SETTINGS" => array(
			"NAME" => GetMessage("P_ORIGINAL"),
			"PARENT" => "PHOTO_SETTINGS")
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock,
		),
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}',
		),
		"SECTION_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => '',
		),
		"SECTIONS_TOP_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTIONS_TOP_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "sections_top.php",
		),
		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?SECTION_ID=#SECTION_ID#",
		),
		
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SET_TITLE" => Array(),
		
		"UPLOAD_MAX_FILE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_UPLOAD_MAX_FILE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5",
				"6" => "6",
				"7" => "7",
				"8" => "8",
				"9" => "9",
				"10" => "10",
				),
			"DEFAULT" => array("5"),
			"MULTIPLE" => "N",
		),

		"UPLOAD_MAX_FILE_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_UPLOAD_MAX_FILE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "7",
		),
		"ALBUM_PHOTO_THUMBS_WIDTH" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "70",
		),
		"ALBUM_PHOTO_WIDTH" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_WIDTH"),
			"TYPE" => "STRING",
			"DEFAULT" => "150",
		),
		
		"THUMBS_SIZE" => array(
			"PARENT" => "THUMBS_SETTINGS",
			"NAME" => GetMessage("P_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "120",
		),
		"JPEG_QUALITY1" => Array(
			"PARENT" => "THUMBS_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "95"),
		
		"PREVIEW_SIZE" => array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("P_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "300",
		),
		"JPEG_QUALITY2" => Array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "95"),
		"JPEG_QUALITY" => Array(
			"PARENT" => "ORIGINAL_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "90"),
		"ADDITIONAL_SIGHTS" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
			"TYPE" => "LIST",
			"VALUES" => $arSights,
			"DEFAULT" => array(),
			"MULTIPLE" => "Y"),
		"WATERMARK_MIN_PICTURE_SIZE" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_MIN_PICTURE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "200"),
		"PATH_TO_FONT" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PATH_TO_FONT"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
));
?>