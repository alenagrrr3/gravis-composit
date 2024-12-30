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
<?$ElementID = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail",
	"",
	Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
 		"ELEMENT_ID" => $arResult["VARIABLES"]["ELEMENT_ID"],
 		"ELEMENT_CODE" => $arResult["VARIABLES"]["ELEMENT_CODE"],
		"BEHAVIOUR" => "USER",
 		
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
 		
		"GALLERY_URL" => $arResult["URL_TEMPLATES"]["gallery"],
		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"DETAIL_EDIT_URL" => $arResult["URL_TEMPLATES"]["detail_edit"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"SEARCH_URL" => $arResult["URL_TEMPLATES"]["search"], 
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		"THUMBS_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
 		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
 		"SET_TITLE" => $arParams["SET_TITLE"],
		"ADD_CHAIN_ITEM" => $arParams["ADD_CHAIN_ITEM"],
 		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
if ($ElementID <= 0)
	return false;

if($arParams["USE_RATING"]=="Y"):
?><div id="photo_vote_source" style="display:none;"><?
$APPLICATION->IncludeComponent(
	"bitrix:iblock.vote",
	"ajax",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"ELEMENT_ID" => $ElementID,
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_AS_RATING" => "rating"
	),
	$component,
	array("HIDE_ICONS" => "Y")
);
?></div><?
?><script>
function to_show_vote()
{
	if (document.getElementById('photo_vote') && document.getElementById('vote_<?=$ElementID?>'))
	{
		var _div = document.getElementById('vote_<?=$ElementID?>');
		var div = document.getElementById('vote_<?=$ElementID?>').cloneNode(true);
		_div.id = 'temp';
		document.getElementById('photo_vote').appendChild(div);
	}
	else
	{
		document.getElementById('photo_vote_source').style.display = '';
	}
	
}
setTimeout(to_show_vote, 100);
</script><?
endif;

// SLIDER
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"slider", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
		"SECTION_CODE" => $arResult["VARIABLES"]["SECTION_CODE"],
		"USER_ALIAS" => $result["USER_ALIAS"],
		"PERMISSION" => $result["PERMISSION"],
		"BEHAVIOUR" => "USER",
 		"ELEMENT_ID" => $ElementID,
 		
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD" => $arParams["ELEMENT_SORT_FIELD"],
		"ELEMENT_SORT_ORDER" => $arParams["ELEMENT_SORT_ORDER"],
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => array(),
		
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => "0",
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

		
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		"ELEMENT_ID" => $ElementID,
		"SLIDER_COUNT_CELL" => $arParams["SLIDER_COUNT_CELL"],
		"SHOW_DESCRIPTION" => "Y"
	),
	$component,
	array("HIDE_ICONS" => "Y")
);

// COMMENTS
if ($arParams["USE_COMMENTS"] == "Y" && $arParams["COMMENTS_TYPE"] != "none"):
	?><div class="empty-clear before-comment"></div><?
	$arCommentsParams = Array(
 		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
 		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
 		"SECTION_ID" => $arResult["VARIABLES"]["SECTION_ID"],
 		"ELEMENT_ID" => $ElementID,
 		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		"BEHAVIOUR" => "USER",
		
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		
 		"COMMENTS_COUNT" => $arParams["COMMENTS_COUNT"],
		"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"]);

	$arCommentsParams["COMMENTS_TYPE"] = (strToLower($arParams["COMMENTS_TYPE"]) == "forum" ? "forum" : "blog");
	
	if ($arCommentsParams["COMMENTS_TYPE"] != "forum")
	{
		$arCommentsParams["COMMENTS_TYPE"] = "blog";
		$arCommentsParams["BLOG_URL"] = $arParams["BLOG_URL"];
		$arCommentsParams["PATH_TO_USER"] = $arParams["PATH_TO_USER"];
		$arCommentsParams["PATH_TO_BLOG"] = $arParams["PATH_TO_BLOG"];
	}
	else
	{
		$arCommentsParams["FORUM_ID"] = $arParams["FORUM_ID"];
		$arCommentsParams["USE_CAPTCHA"] = $arParams["USE_CAPTCHA"];
		$arCommentsParams["URL_TEMPLATES_READ"] = $arParams["URL_TEMPLATES_READ"];
		$arCommentsParams["URL_TEMPLATES_PROFILE_VIEW"] = trim($arParams["URL_TEMPLATES_PROFILE_VIEW"]);
		if (empty($arCommentsParams["URL_TEMPLATES_PROFILE_VIEW"]))
			$arCommentsParams["URL_TEMPLATES_PROFILE_VIEW"] = str_replace("#USER_ID#", "#UID#", $arResult["URL_TEMPLATES"]["galleries"]);
		$arCommentsParams["PREORDER"] = ($arParams["PREORDER"] != "N" ? "Y" : "N");
		$arCommentsParams["SHOW_LINK_TO_FORUM"] = ($arParams["SHOW_LINK_TO_FORUM"] != "N" ? "Y" : "N");
	}
	$APPLICATION->IncludeComponent(
		"bitrix:photogallery.detail.comment", 
		"", 
		$arCommentsParams,
		$component,
		array("HIDE_ICONS" => "Y"));
endif;
?>