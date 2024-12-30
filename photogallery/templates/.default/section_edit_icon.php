<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.edit.icon",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"ACTION" => $arResult["VARIABLES"]["ACTION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		
		"SECTION_TOP_URL" => $arResult["URL_TEMPLATES"]["section_top"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"]
	),
	$component
);
?>