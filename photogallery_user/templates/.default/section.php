<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "SECTION",
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
// SECTION 
?>
<br class="wd-br" />
<?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "USER",
		
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?

// SECTIONS LIST 
?><?
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	"",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "USER",
		
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		
		"SECTIONS_TOP_URL" => $arResult["URL_TEMPLATES"]["sections_top"],
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
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
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"BEHAVIOUR" => "USER",
		
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => $arParams["ELEMENTS_PAGE_ELEMENTS"],
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" => "",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "N",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
		
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"bottom",
		
		"SHOW_CONTROLS"	=>	"Y",
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
		"SHOW_COMMENTS" => $arParams["SHOW_COMMENTS"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>