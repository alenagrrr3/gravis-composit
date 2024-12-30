<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$URL_NAME_DEFAULT = array(
		"search" => "PAGE_NAME=search",
		"detail_list" => "PAGE_NAME=detail_list&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}

if(IsModuleInstalled("search") && $arParams["SHOW_TAGS"] == "Y")
{
	?><div class="tags-cloud"><?
	$APPLICATION->IncludeComponent("bitrix:search.tags.cloud", ".default", 
		Array(
		"SEARCH" => $arResult["REQUEST"]["~QUERY"],
		"TAGS" => $arResult["REQUEST"]["~TAGS"],
		
		"PAGE_ELEMENTS" => $arParams["TAGS_PAGE_ELEMENTS"],
		"PERIOD" => $arParams["TAGS_PERIOD"],
		"TAGS_INHERIT" => $arParams["TAGS_INHERIT"],
		
		"URL_SEARCH" =>  CComponentEngine::MakePathFromTemplate($arParams["~SEARCH_URL"], array()),
		
		"FONT_MAX" => $arParams["FONT_MAX"],
		"FONT_MIN" => $arParams["FONT_MIN"],
		"COLOR_NEW" => $arParams["COLOR_NEW"],
		"COLOR_OLD" => $arParams["COLOR_OLD"],
		"SHOW_CHAIN" => $arParams["TAGS_SHOW_CHAIN"],
		"WIDTH" => "100%",
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"arrFILTER" => array("iblock_".$arParams["IBLOCK_TYPE"]), 
		"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])
		), $component);
	?></div><?
}
if (is_array($arParams["SHOW_LINK_ON_MAIN_PAGE"])):
	$detail_list = array(
		"~url" => CComponentEngine::MakePathFromTemplate($arParams["DETAIL_LIST_URL"], array("SECTION_ID" => "all", "ELEMENT_ID" => "all")));
	$detail_list["url"] = $detail_list["~url"];
	if (strpos($detail_list["url"], "?") === false)
		$detail_list["url"] .= "?";
		
	$arRes = array();

	foreach ($arParams["SHOW_LINK_ON_MAIN_PAGE"] as $key):
	
		if ($key == "id"):
			$arRes["id"] = array(
				"title" => GetMessage("P_PHOTO_SORT_ID"),
				"description" => GetMessage("P_PHOTO_SORT_ID_TITLE"),
				"url" => $detail_list["~url"]);
		elseif ($key == "shows"):
			$arRes["shows"] = array(
				"title" => GetMessage("P_PHOTO_SORT_SHOWS"),
				"description" => GetMessage("P_PHOTO_SORT_SHOWS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=shows");
		elseif ($key == "rating" && ($arParams["USE_RATING"] == "Y")):
			$arRes["rating"] = array(
				"title" => GetMessage("P_PHOTO_SORT_RATING"),
				"description" => GetMessage("P_PHOTO_SORT_RATING_TITLE"),
				"url" => $detail_list["url"]."&amp;order=rating");
		elseif ($key == "comments" && ($arParams["USE_COMMENTS"] == "Y")):
			$arRes["comments"] = array(
				"title" => GetMessage("P_PHOTO_SORT_COMMENTS"),
				"description" => GetMessage("P_PHOTO_SORT_COMMENTS_TITLE"),
				"url" => $detail_list["url"]."&amp;order=comments");
		endif;
	endforeach;
	
?><div class="photo-controls photo-view only-on-main"><?
	$counter = 0;
	foreach ($arRes as $key => $val):
	
		$addClassName = (count($arRes) <= 1 ? " single" : "");
		$addClassName .= ($arParams["SHOW_ON_MAIN_PAGE"] == $key ? " active" : "");

			?><a href="<?=$val["url"]?>" class="photo-view <?=$key?><?=$addClassName?>"<?
				?>title="<?=$val["description"]?>"><?=$val["title"]?></a><?

		if ($counter < (count($arRes) - 1)):
			?><span class="empty"></span><?	
		endif;
		$counter++;
	endforeach;
?></div><?
?><div class="empty-clear"></div><?
endif;

?><table cellpadding="0" cellspacing="0" border="0" id="photo_main_page" width="100%"><?
?><tr valign="top"><?
if ($arParams["SHOW_ON_MAIN_PAGE"] != "none" && $arParams["SHOW_ON_MAIN_PAGE_POSITION"] == "left")
{
	?><td id="photo_main_page_tape" style="padding-right:15px;"><?
	?><div class="photo_main_page_tape1"><?
		?><div class="photo_main_page_tape2"><?
			?><div class="photo_main_page_tape3"><?
				?><div class="photo_main_page_tape4"><?
				
	$arParams["ELEMENT_FILTER"] = array();
	if ($arParams["SHOW_ON_MAIN_PAGE"] == "shows")
	{
		$arParams["ELEMENT_FILTER"] = array(">SHOW_COUNTER" => "0");
	}
	elseif ($arParams["SHOW_ON_MAIN_PAGE"] == "rating" && $arParams["USE_RATING"] == "Y")
	{
		$arParams["ELEMENT_FILTER"] = array(">PROPERTY_RATING" => "0");
	}
	elseif ($arParams["SHOW_ON_MAIN_PAGE"] == "comments" && ($arParams["USE_COMMENTS"] == "Y"))
	{
		if ($arParams["COMMENTS_TYPE"] == "blog")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_BLOG_COMMENTS_CNT" => "0");
		elseif ($arParams["COMMENTS_TYPE"] == "forum")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_FORUM_MESSAGE_CNT" => "0");
	}

	?><?$APPLICATION->IncludeComponent("bitrix:photogallery.detail.list", $arParams["TEMPLATE_LIST"], 
		Array(
			"IBLOCK_TYPE"	=>	$arParams["IBLOCK_TYPE"],
			"IBLOCK_ID"	=>	$arParams["IBLOCK_ID"],
			"SECTION_ID"	=>	0,
	 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
			
			"ELEMENT_LAST_TYPE"	=>	$arParams["SHOW_ON_MAIN_PAGE_TYPE"],
			"ELEMENTS_LAST_COUNT"	=>	$arParams["SHOW_ON_MAIN_PAGE_COUNT"],
			"ELEMENTS_LAST_TIME" => $arParams["SHOW_ON_MAIN_PAGE_COUNT"], 
			
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
			"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
			"PAGE_ELEMENTS"	=>	$arParams["ELEMENTS_PAGE_ELEMENTS"],
			
			"ELEMENT_SORT_FIELD" => $arParams["SHOW_ON_MAIN_PAGE"], 
			"ELEMENT_SORT_ORDER" => "desc", 
			"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
			
			"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
			"THUMBS_SIZE" => $arParams["THUMBS_SIZE"],
			
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
			"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
			
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
			
			"SHOW_TAGS" => $arParams["SHOW_TAGS"],
			"SHOW_RATING" => (($arParams["USE_RATING"] == "Y" && $arParams["SHOW_ON_MAIN_PAGE"] == "rating") ? "Y" : $arParams["SHOW_RATING"]),
			"SHOW_COMMENTS" => (($arParams["USE_COMMENTS"] == "Y" && $arParams["SHOW_ON_MAIN_PAGE"] == "comments") ? "Y" : $arParams["SHOW_COMMENTS"]),
			"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
			
			"SET_TITLE"	=>	"N",
			"ADDITIONAL_SIGHTS"	=>	array(),
			"PICTURES_SIGHT"	=>	"standart",
			"SHOW_PAGE_NAVIGATION"	=>	"bottom",
			"SHOW_CONTROLS"	=>	"N",
			"MAX_VOTE" => $arParams["MAX_VOTE"],
			"VOTE_NAMES" => $arParams["VOTE_NAMES"]),
			$component,
			array("HIDE_ICONS" => "Y"));
				?></div><?
			?></div><?
		?></div><?
	?></div><?
	?></td><?
}
?><td id="photo_main_page_albums"><?
	?><div class="photo_main_page_albums1"><?
		?><div class="photo_main_page_albums2"><?
			?><div class="photo_main_page_albums3"><?
				?><div class="photo_main_page_albums4"><?
$APPLICATION->IncludeComponent(
	"bitrix:photogallery.section.list",
	".big",
	Array(
		"CACHE_TYPE" => $arParams["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["CACHE_TIME"],
		
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_SECTION"],
		
		"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
		"PAGE_ELEMENTS" => $arParams["SECTION_PAGE_ELEMENTS"],
		"SORT_BY" => $arParams["SECTION_SORT_BY"],
		"SORT_ORD" => $arParams["SECTION_SORT_ORD"],
		
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"],
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],

		"SECTION_URL" => $arResult["URL_TEMPLATES"]["section"],
		"SECTION_EDIT_URL" => $arResult["URL_TEMPLATES"]["section_edit"],
		"SECTION_EDIT_ICON_URL" => $arResult["URL_TEMPLATES"]["section_edit_icon"],
		"DETAIL_URL" => $arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL" => $arResult["URL_TEMPLATES"]["search"],
		"UPLOAD_URL" => $arResult["URL_TEMPLATES"]["upload"],
		
		"ALBUM_PHOTO_THUMBS_SIZE"	=>	$arParams["ALBUM_PHOTO_THUMBS_SIZE"],
		"ALBUM_PHOTO_SIZE"	=>	$arParams["ALBUM_PHOTO_SIZE"],
		"SET_TITLE"	=>	"N",
		),
	$component
);
				?></div><?
			?></div><?
		?></div><?
	?></div><?
?></td><?
if ($arParams["SHOW_ON_MAIN_PAGE"] != "none" && $arParams["SHOW_ON_MAIN_PAGE_POSITION"] == "right")
{
	?><td id="photo_main_page_tape" style="padding-left:15px;"><?
	?><div class="photo_main_page_tape1"><?
		?><div class="photo_main_page_tape2"><?
			?><div class="photo_main_page_tape3"><?
				?><div class="photo_main_page_tape4"><?
				
	$arParams["ELEMENT_FILTER"] = array();
	if ($arParams["SHOW_ON_MAIN_PAGE"] == "shows")
	{
		$arParams["ELEMENT_FILTER"] = array(">SHOW_COUNTER" => "0");
	}
	elseif ($arParams["SHOW_ON_MAIN_PAGE"] == "rating" && $arParams["USE_RATING"] == "Y")
	{
		$arParams["ELEMENT_FILTER"] = array(">PROPERTY_RATING" => "0");
	}
	elseif ($arParams["SHOW_ON_MAIN_PAGE"] == "comments" && ($arParams["USE_COMMENTS"] == "Y"))
	{
		if ($arParams["COMMENTS_TYPE"] == "blog")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_BLOG_COMMENTS_CNT" => "0");
		elseif ($arParams["COMMENTS_TYPE"] == "forum")
			$arParams["ELEMENT_FILTER"] = array(">PROPERTY_FORUM_MESSAGE_CNT" => "0");
	}

	?><?$APPLICATION->IncludeComponent("bitrix:photogallery.detail.list", $arParams["TEMPLATE_LIST"], 
		Array(
			"IBLOCK_TYPE"	=>	$arParams["IBLOCK_TYPE"],
			"IBLOCK_ID"	=>	$arParams["IBLOCK_ID"],
			"SECTION_ID"	=>	0,
	 		"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT_DETAIL"],
			
			"ELEMENT_LAST_TYPE"	=>	$arParams["SHOW_ON_MAIN_PAGE_TYPE"],
			"ELEMENTS_LAST_COUNT"	=>	$arParams["SHOW_ON_MAIN_PAGE_COUNT"],
			"ELEMENTS_LAST_TIME" => $arParams["SHOW_ON_MAIN_PAGE_COUNT"], 
			
			"PAGE_NAVIGATION_TEMPLATE" => $arParams["PAGE_NAVIGATION_TEMPLATE"],
			"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
			"PAGE_ELEMENTS"	=>	$arParams["ELEMENTS_PAGE_ELEMENTS"],
			
			"ELEMENT_SORT_FIELD" => $arParams["SHOW_ON_MAIN_PAGE"], 
			"ELEMENT_SORT_ORDER" => "desc", 
			"ELEMENT_FILTER" => $arParams["ELEMENT_FILTER"],
			
			"COMMENTS_TYPE" => $arParams["COMMENTS_TYPE"],
			"THUMBS_SIZE" => $arParams["THUMBS_SIZE"],
			
			"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
			"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
			
			"CACHE_TYPE" => $arParams["CACHE_TYPE"],
			"CACHE_TIME" => $arParams["CACHE_TIME"],
			
			"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
			"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
			"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
			
			"SHOW_TAGS" => $arParams["SHOW_TAGS"],
			"SHOW_RATING" => (($arParams["USE_RATING"] == "Y" && $arParams["SHOW_ON_MAIN_PAGE"] == "rating") ? "Y" : $arParams["SHOW_RATING"]),
			"SHOW_COMMENTS" => (($arParams["USE_COMMENTS"] == "Y" && $arParams["SHOW_ON_MAIN_PAGE"] == "comments") ? "Y" : $arParams["SHOW_COMMENTS"]),
			"SHOW_SHOWS" => $arParams["SHOW_SHOWS"],
			
			"SET_TITLE"	=>	"N",
			"ADDITIONAL_SIGHTS"	=>	array(),
			"PICTURES_SIGHT"	=>	"standart",
			"SHOW_PAGE_NAVIGATION"	=>	"bottom",
			"SHOW_CONTROLS"	=>	"N",
			"MAX_VOTE" => $arParams["MAX_VOTE"],
			"VOTE_NAMES" => $arParams["VOTE_NAMES"]),
			$component,
			array("HIDE_ICONS" => "Y"));
				?></div><?
			?></div><?
		?></div><?
	?></div><?
	?></td><?
}
if($arParams["SET_TITLE"] != "N")
{
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("P_PHOTO"));
}


?></tr></table>