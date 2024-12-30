<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.upload",
	"",
	Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
 		
 		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
 		"WATERMARK" => $arParams["WATERMARK"],
 		"WATERMARK_COLORS" => $arParams["WATERMARK_COLORS"],
		"JPEG_QUALITY1"	=>	$arParams["JPEG_QUALITY1"],
		"JPEG_QUALITY2"	=>	$arParams["JPEG_QUALITY2"],
		"JPEG_QUALITY"	=>	$arParams["JPEG_QUALITY"],
		"WATERMARK_MIN_PICTURE_SIZE"	=>	$arParams["WATERMARK_MIN_PICTURE_SIZE"],
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"UPLOAD_MAX_FILE"	=>	$arParams["UPLOAD_MAX_FILE"],
		"UPLOAD_MAX_FILE_SIZE"	=>	$arParams["UPLOAD_MAX_FILE_SIZE"],
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_WIDTH"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_WIDTH"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		"PATH_TO_FONT"	=>	$arParams["PATH_TO_FONT"],
	),
	$component
);?>