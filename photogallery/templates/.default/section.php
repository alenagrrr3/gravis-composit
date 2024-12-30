<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// SECTION 
?><?
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
	),
	$component
);?><?

// SECTIONS LIST 
?><?
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?><?

// DETAIL LIST
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	$arParams["TEMPLATE_LIST"], 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"SHOW_PAGE_NAVIGATION"	=>	"bottom",
		"SHOW_CONTROLS"	=>	"Y",
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"SHOW_RATING" => $arParams["SHOW_RATING"],
		"SHOW_COMMENTS" => $arParams["SHOW_COMMENTS"],
		"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
		"SET_TITLE" => "N",
		
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"PREVIEW_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>