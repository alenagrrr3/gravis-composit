<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "SEARCH",
		"USER_ALIAS" => $arResult["VARIABLES"]["USER_ALIAS"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		
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
	$component
);?><?

$APPLICATION->IncludeComponent(
	"bitrix:search.page",
	"",
	Array(
		"TAGS_PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"], 
		"TAGS_PERIOD" => $arParams["TAGS_PERIOD"], 
		"TAGS_URL_SEARCH" => $arResult["URL_TEMPLATES"]["search"], 
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["index"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"], 
		
		"FONT_MAX" => $arParams["TAGS_FONT_MAX"],
		"FONT_MIN" => $arParams["TAGS_FONT_MIN"],
		"COLOR_NEW" => $arParams["TAGS_COLOR_NEW"],
		"COLOR_OLD" => $arParams["TAGS_COLOR_OLD"],
		"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"], 
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"WIDTH" => "100%",  
		
		"PAGE_RESULT_COUNT" => (empty($arParams["PAGE_RESULT_COUNT"]) ? 50 : $arParams["PAGE_RESULT_COUNT"]),
		"PAGER_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],

		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => 0,
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"], 
		"arrWHERE" => Array(), 
		"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
		"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"]),
		
		"ALBUM_PHOTO_THUMBS_WIDTH"	=>	$arParams["ALBUM_PHOTO_THUMBS_WIDTH"],
		"ALBUM_PHOTO_WIDTH"	=>	$arParams["ALBUM_PHOTO_WIDTH"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"]
	),
	$component
);
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));

?>