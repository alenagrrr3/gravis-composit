<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "DETAIL",
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
<?
// DETAIL LIST
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"slide_show", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
		"BEHAVIOUR" => "USER",
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
 		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
 		
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
		"PAGE_ELEMENTS" => 0,
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
 		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		
		"PICTURES_SIGHT" => "REAL",
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "N",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		"ADD_CHAIN_ITEM" => "N",
		
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?>