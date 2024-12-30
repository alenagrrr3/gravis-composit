<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$URL_NAME_DEFAULT = array(
		"search" => "PAGE_NAME=search",
		"detail_list" => "PAGE_NAME=detail_list",
		"galleries" => "PAGE_NAME=galleries&USER_ID=#USER_ID#",
		"tags" => "PAGE_NAME=tags");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
$sDetailListUrl = CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array());
if (strpos($sDetailListUrl, "?") === false)
	$sDetailListUrl .= "?";
	
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
		"USER_ALIAS" => $arResult["VARIABLES"]["USER_ALIAS"],
		"ANALIZE_SOCNET_PERMISSION" => $arParams["ANALIZE_SOCNET_PERMISSION"],
		
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
		
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
	),
	$component, 
	array("HIDE_ICONS" => "Y")
);?><?

if ($result === false)
	return false;

if ($arParams["PERMISSION"] >= "W" && $arParams["MODERATE"] == "Y"):
?><div class="photo-controls photo-action">
	<a href="<?=$sDetailListUrl."&moderate=Y"?>" class="photo-action photo-moderate"><?=GetMessage("P_NOT_APPROVED")?></a> 
</div><?
endif;
	

?><div class="empty-clear"></div>
<div id="photo-main-div"><?

$arShows = array("SHOW_RATING" => "N", "SHOW_COMMENTS" => "N", "SHOW_SHOWS" => "N");
$sSortField = "ID";
$res = array();
if ($arParams["MODERATE"] == "Y")
	$res["PROPERTY_APPROVE_ELEMENT"] = "Y";
if ($arParams["SHOW_ONLY_PUBLIC"] == "Y")
	$res["PROPERTY_PUBLIC_ELEMENT"] = "Y";

$res_best = $res;
if ($arParams["USE_RATING"] == "Y")
{
	$res_best[">PROPERTY_RATING"] = "0";
	$arShows["SHOW_RATING"] = "Y";
	$sSortField = "rating";
}
elseif ($arParams["USE_COMMENTS"] == "Y")
{
	if ($arParams["COMMENTS_TYPE"] == "FORUM")
		$res_best[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
	else
		$res_best[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
	
	$arShows["SHOW_COMMENTS"] = "Y";
	$sSortField = "comments";
}
else
{
	$arShows["SHOW_SHOWS"] = "Y";
	$sSortField = "shows";
}


?><table border="0" cellpadding="0" cellspacing="0" id="photo-main-table">
<tr><td id="photo-main-td-left">
	<div id="photo-main-div-best">
<?$element_id = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"simple", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "USER",
	
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD"	=>	"created_date",
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1"	=>	$sSortField,
		"ELEMENT_SORT_ORDER1"	=>	"desc",
		"ELEMENT_FILTER" => $res_best,
		
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE"	=>	"Y",
		"PAGE_ELEMENTS"	=>	"1",
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT" =>	"detail",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "Y",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
		"THUMBS_SIZE"	=>	$arParams["PREVIEW_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		"SHOW_TAGS"	=>	"N",
		"SHOW_RATING"	=>	"N",
		"SHOW_COMMENTS"	=>	"N",
		"SHOW_SHOWS"	=>	"N",
		
		"MAX_VOTE"	=>	$arParams["MAX_VOTE"],
		"VOTE_NAMES"	=>	$arParams["VOTE_NAMES"]
	),
	$component);
?>
	</div>
</td>
<?
$res_best["!ID"] = $element_id;
ob_start();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"ascetic", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "USER",
	
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD"	=>	"created_date",
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1"	=>	$sSortField,
		"ELEMENT_SORT_ORDER1"	=>	"desc",
		"ELEMENT_FILTER" => $res_best,

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => "N",
		"PAGE_ELEMENTS" => "10",
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT"	=>	"standart",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "Y",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		"SQUARE" => "Y",
		"PERCENT" => 70
	),
	$component
);?><?
$best = ob_get_contents();
ob_end_clean();

?><td id="photo-main-td-right">
<?$APPLICATION->IncludeComponent("bitrix:photogallery.interface", "bookmark", 
	Array(
		"DATA" => array(
			array(
				"HEADER" => array(
					"TITLE" => GetMessage("P_BEST_PHOTO"),
					"LINK" => $sDetailListUrl."&order=".$sSortField),
				"BODY" => $best,
				"ACTIVE" => "Y"),
			array(
				"HEADER" => array(
					"TITLE" => GetMessage("P_BEST_PHOTOS"),
					"LINK" => $sDetailListUrl."&order=".$sSortField,
					"HREF" => "Y"),
			))),
	$component,
	array("HIDE_ICONS" => "Y"));
?></td>
</tr>
<tr><?
$bSearch = false;
if($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search")):
	$bSearch = true;
ob_start();
?><?$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default", 
		Array(
		"SEARCH" => $arResult["REQUEST"]["~QUERY"],
		"TAGS" => $arResult["REQUEST"]["~TAGS"],
		
		"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
		"PERIOD" => $arParams["TAGS_PERIOD"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],
		
		"URL_SEARCH" =>  CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()),
		
		"FONT_MAX" => $arParams["TAGS_FONT_MAX"],
		"FONT_MIN" => $arParams["TAGS_FONT_MIN"],
		"COLOR_NEW" => $arParams["TAGS_COLOR_NEW"],
		"COLOR_OLD" => $arParams["TAGS_COLOR_OLD"],
		"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"],
		"WIDTH" => "100%",
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
		"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])
		), $component);
	?><?
$tags_cloud = ob_get_clean();

?>	<td id="photo-main-td-middle-left"><?
	?><?$APPLICATION->IncludeComponent("bitrix:photogallery.interface", 
		"bookmark", 
		Array("DATA" => array(
			array(
				"HEADER" => array(
					"TITLE" => GetMessage("P_TAGS_POPULAR"),
					"LINK" => ""),
				"BODY" => $tags_cloud,
				"ACTIVE" => "Y"),
			array(
				"HEADER" => array(
					"TITLE" => GetMessage("P_TAGS_ALL"),
					"HREF" => "Y",
					"LINK" => CComponentEngine::MakePathFromTemplate($arParams["TAGS_URL"], array()))))),
		$component,
		array("HIDE_ICONS" => "Y"));
		?></td>
		
	<td id="photo-main-td-middle-right">
<?
else:
?>	<td id="photo-main-td-middle-left" colspan="2"><?
endif;	
	?><div class="photo-head"><a href="<?=CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => "users"))?>"><?=GetMessage("P_GALLERIES")?></a></div>
	<div id="photo-main-galleries"><?
	?><?$APPLICATION->IncludeComponent("bitrix:photogallery.gallery.list", 
	"ascetic", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USER_ID"	=>	"0",
		"SORT_BY"	=>	"ID",
		"SORT_ORD"	=>	"DESC",
		"INDEX_URL"	=>	$arResult["URL_TEMPLATES"]["index"],
		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"GALLERY_EDIT_URL"	=>	$arResult["URL_TEMPLATES"]["gallery_edit"],
		"UPLOAD_URL"	=>	$arResult["URL_TEMPLATES"]["upload"],
		"ONLY_ONE_GALLERY"	=>	$arParams["ONLY_ONE_GALLERY"],
		"GALLERY_SIZE"	=>	$arParams["GALLERY_SIZE"],
		"PAGE_ELEMENTS"	=>	($bSearch ? 3 : 6),
		"PAGE_NAVIGATION_TEMPLATE"	=>	$arParams["PAGE_NAVIGATION_TEMPLATE"],
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT_SECTION"],
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"DISPLAY_PANEL"	=>	$arParams["DISPLAY_PANEL"],
		"SET_TITLE" => "N",
		
		"GALLERY_AVATAR_SIZE"	=>	$arParams["GALLERY_AVATAR_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		
		), $component,
		array("HIDE_ICONS" => "Y"));?><?
	?><div class="photo-gallery-ascetic">
		<div class="all-elements">
			<a href="<?=CComponentEngine::MakePathFromTemplate($arParams["GALLERIES_URL"], array("USER_ID" => "users"));
			?>"><?=GetMessage("P_VIEW_ALL_GALLERIES")?></a></div></div>
	</div>
	</td>
</tr></table><?
ob_start();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	"ascetic", 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "USER",
	
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TIME" => "",
		"ELEMENT_SORT_FIELD"	=>	"date_create",
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1" => "",
		"ELEMENT_SORT_ORDER1" => "",
		"ELEMENT_FILTER" => $res,

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE" => $arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS" => "10",
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
		"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
		
		"ADDITIONAL_SIGHTS" => $arParams["~ADDITIONAL_SIGHTS"],
		"PICTURES_SIGHT"	=>	"standart",
		"GALLERY_SIZE" => $arParams["GALLERY_SIZE"],
		"GET_GALLERY_INFO" => "Y",
		"SHOW_PHOTO_USER" => $arParams["SHOW_PHOTO_USER"],
		"GALLERY_AVATAR_SIZE" => $arParams["GALLERY_AVATAR_SIZE"],
		
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"SET_TITLE" => "N",
		
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	"none",
		"SQUARE" => "Y",
		"PERCENT" => 70
	),
	$component
);?>
<div class="all-elements"><a href="<?=($sDetailListUrl."&order=date_create")?>"><?=GetMessage("P_PHOTO_NEW_ALL")?></a></div><?
$new = ob_get_clean();

$arFields = array(
	array(
		"HEADER" => array(
			"TITLE" => GetMessage("P_PHOTO_NEW"),
			"LINK" => ""),
		"BODY" => $new,
		"ACTIVE" => "Y"),
	array(
		"HEADER" => array(
			"TITLE" => GetMessage("P_PHOTO_POPULAR"),
			"LINK" => $sDetailListUrl."&order=shows&group_photo=Y"),
		"BODY" => "",
		"AJAX_USE" => "Y"));
			
if ($arParams["USE_COMMENTS"] == "Y")
$arFields[] = array(
		"HEADER" => array(
			"TITLE" => GetMessage("P_PHOTO_COMMENT"),
			"LINK" => $sDetailListUrl."&order=comments&group_photo=Y"),
		"BODY" => "",
		"AJAX_USE" => "Y");

?><div id="photo-main-new"><?$APPLICATION->IncludeComponent("bitrix:photogallery.interface", "bookmark", 
	Array("DATA" => $arFields),
	$component,
	array("HIDE_ICONS" => "Y"));
?></div>
</div>
<style>
div#photo-main-new div.photo-photos{
	height:<?=intVal($arParams["THUMBS_SIZE"] * 70/100)?>px;}
div.photo-body-text-ajax{
	height:<?=intVal($arParams["THUMBS_SIZE"] * 70/100 + 39)?>px;
	padding-top:<?=intVal($arParams["THUMBS_SIZE"] * 70/200)?>px;
	text-align:center;}
div#photo-main-galleries div.photo-gallery-ascetic{
	height:<?=($arParams["GALLERY_AVATAR_SIZE"])?>px;
	}
</style>
<?
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
?>