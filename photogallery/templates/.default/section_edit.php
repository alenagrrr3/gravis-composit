<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		
		"ACTION" => $arResult["VARIABLES"]["ACTION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
 		
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_WIDTH"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_WIDTH"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"]
	),
	$component
);
?>