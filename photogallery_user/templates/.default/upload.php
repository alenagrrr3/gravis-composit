<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "UPLOAD",
		"USER_ALIAS" => $arResult["VARIABLES"]["USER_ALIAS"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
		"ANALIZE_SOCNET_PERMISSION" => $arParams["ANALIZE_SOCNET_PERMISSION"],
		
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		
		"INDEX_URL" => $arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"GALLERIES_URL" => $arResult["URL_TEMPLATES"]["galleries"],
		"GALLERY_EDIT_URL" => $arResult["URL_TEMPLATES"]["gallery_edit"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		
		"RETURN_ARRAY" => "Y", 
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		"ONLY_ONE_GALLERY" => $arParams["ONLY_ONE_GALLERY"],
		"GALLERY_GROUPS" => $arParams["GALLERY_GROUPS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		
		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"]
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
if ($result === false)
	return false;
?>
<br class="wd-br" />
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.upload",
	"",
	Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "USER",
 		
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		
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
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		"PATH_TO_FONT"	=>	$arParams["PATH_TO_FONT"],
		
		"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);?>