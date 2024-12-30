<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "TAGS",
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
	$URL_NAME_DEFAULT = array(
		"search" => "PAGE_NAME=search");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit", "order"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
?>
<div class="empty-clear"></div>
<div class="tags-cloud">
<table cellpadding="0" cellspacing="0" border="0" class="tab-header">
	<tr class="top">
		<td class="left"><div class="empty"></div></td>
		<td class="center"><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr class="middle">
		<td class="left"><div class="empty"></div></td>
		<td class="body-text">
			<div class="photo-head"><?=GetMessage("P_TAGS_CLOUD")?></div><?
?><?$APPLICATION->IncludeComponent(
	"bitrix:search.tags.cloud", 
	".default", 
	Array(
		"SEARCH" => $arResult["REQUEST"]["~QUERY"],
		"TAGS" => $arResult["REQUEST"]["~TAGS"],
		"PERMISSION" => $result["PERMISSION"],
		
		"PAGE_ELEMENTS" => 0,
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
		"arrFILTER_iblock_".$arParams["IBLOCK_TYPE"] => array($arParams["IBLOCK_ID"])), 
	$component,
	array("HIDE_ICONS" => "Y"));
?>		</td>
		<td class="right"><div class="empty"></div></td>
	</tr>
	<tr class="bottom">
		<td class="left"><div class="empty"></div></td>
		<td class="center"><div class="empty"></div></td>
		<td class="right"><div class="empty"></div></td>
	</tr>
</table>
</div>
<?
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle(GetMessage("P_TITLE"));
?>