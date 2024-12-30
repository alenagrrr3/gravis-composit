<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><?$result = $APPLICATION->IncludeComponent(
	"bitrix:photogallery.user",
	".default",
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"PAGE_NAME" => "INDEX",
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
/********************************************************************
				Input params
********************************************************************/
	$arParams["SHOW_ONLY_APPROVED"] = ($arParams["MODERATE"] == "Y" ? "Y" : "N");
	$arParams["MODERATE"] = (($arParams["PERMISSION"] >= "W" && $arParams["MODERATE"] == "Y") ? "Y" : "N");
/***************** URL *********************************************/
	$URL_NAME_DEFAULT = array(
		"index" => "");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arResult["URL_TEMPLATES"][strToLower($URL)]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "sessid", "edit"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default params
********************************************************************/
	if (!empty($_REQUEST["photo_filter_reset"]))
	{
		if (!empty($_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"]))
			$url = $_REQUEST["SEF_APPLICATION_CUR_PAGE_URL"];
		else 
			$url = $APPLICATION->GetCurPageParam("", array("photo_from", "photo_to", "group_photo", 
				"photo_filter_reset", "order", "moderate"));
		$url = str_replace(array("&group_photo=Y", "&amp;group_photo=Y"), "", $url);
		LocalRedirect($url);
	}
	$arResult["ORDER"] = array("date_create", "shows");
	if ($arParams["USE_RATING"] == "Y")
		$arResult["ORDER"][] = "rating";
	if ($arParams["USE_COMMENTS"] == "Y")
		$arResult["ORDER"][] = "comments";
		
	$arResult["ORDER_BY"] = (in_array($_REQUEST["order"], $arResult["ORDER"]) ? $_REQUEST["order"] : "date_create");
	$arResult["PERIOD_FROM"] = trim($_REQUEST["photo_from"]);
	$arResult["PERIOD_TO"] = trim($_REQUEST["photo_to"]);
	$arResult["GROUP_BY_DATE_CREATE"] = ($_REQUEST["group_photo"] == "Y" ? "Y" : "N");
	$arResult["SHOW_NOT_APPROVED"] = (($_REQUEST["moderate"] == "Y" && $arParams["MODERATE"] == "Y") ? "Y" : "N");
	$arResult["SHOW_FILTER"] = ((!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) || $arResult["GROUP_BY_DATE_CREATE"] == "Y" ||
		$arResult["SHOW_NOT_APPROVED"] == "Y") ? "Y" : "N");
/********************************************************************
				/Default params
********************************************************************/
?><div class="photo-controls"><?
?><a href="<?=CComponentEngine::MakePathFromTemplate($arParams["INDEX_URL"], array())?>" title="<?=GetMessage("P_UP_TITLE")?>"  class="photo-action back-to-album" <?
	?>><?=GetMessage("P_UP")?></a><?
?></div>
	<div class="photo-controls photo-view only-on-main">
	<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=date_create", array("order"))?>" 
		title="<?=GetMessage("P_PHOTO_SORT_ID_TITLE")?>" class="photo-view order-date-create<?=
		($arResult["ORDER_BY"] == "date_create" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_ID")?></a>
	<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=shows", array("order"))?>" 
		title="<?=GetMessage("P_PHOTO_SORT_SHOWS_TITLE")?>" class="photo-view order-shows<?=
		($arResult["ORDER_BY"] == "shows" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_SHOWS")?></a>
<?
if (in_array("rating", $arResult["ORDER"])):
?>	<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=rating", array("order"))?>" 
		title="<?=GetMessage("P_PHOTO_SORT_RATING_TITLE")?>" class="photo-view order-rating<?=
		($arResult["ORDER_BY"] == "rating" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_RATING")?></a><?
endif;
if (in_array("comments", $arResult["ORDER"])):
?>	<a href="<?=$GLOBALS['APPLICATION']->GetCurPageParam("&order=comments", array("order"))?>" 
		title="<?=GetMessage("P_PHOTO_SORT_COMMENTS_TITLE")?>" class="photo-view order-comments<?=
		($arResult["ORDER_BY"] == "comments" ? " active" : "")?>"><?=GetMessage("P_PHOTO_SORT_COMMENTS")?></a><?
endif;
?></div>
<div class="empty-clear"></div>
<div id="photo-filter">
	<div id="photo-filter-switcher" class="<?=($arResult["SHOW_FILTER"] == "Y" ? "filter-opened" : "filter-closed")?>" <?
		?>onclick="if(this.className=='filter-opened'){this.className='filter-closed';document.getElementById('photo-filter-container').style.display='none';document.getElementById('photo-filter-switcher-href').innerHTML='<?=CUtil::JSEscape(GetMessage("P_OPEN_FILTER"))?>';}else{this.className='filter-opened';document.getElementById('photo-filter-container').style.display='block';document.getElementById('photo-filter-switcher-href').innerHTML='<?=CUtil::JSEscape(GetMessage("P_CLOSE_FILTER"))?>';}"><a href="javascript:void(0);" id="photo-filter-switcher-href"><?=
		($arResult["SHOW_FILTER"] == "Y" ? GetMessage("P_CLOSE_FILTER") : GetMessage("P_OPEN_FILTER"))?></a></div>
	
	<div id="photo-filter-container" style="display:<?=($arResult["SHOW_FILTER"] == "Y" ? "block" : "none")?>;">
		<div id="photo-filter-body">
			<form action="" id="photo_filter_form" class="photo_form" method="get">
				<input type="hidden" name="PAGE_NAME" value="detail_list" />
				<input type="hidden" name="order" value="<?=$arResult["ORDER_BY"]?>" />
				<div id="photo-period">
					<span class="field-name"><?=GetMessage("P_SELECT_PHOTO_FROM_PERIOD")?>: </span>
					<span class="field-value"><?$APPLICATION->IncludeComponent("bitrix:main.calendar", ".default", 
						Array(
							"SHOW_INPUT"	=>	"Y",
							"INPUT_NAME"	=>	"photo_from",
							"INPUT_NAME_FINISH"	=>	"photo_to",
							"INPUT_VALUE"	=>	$arResult["PERIOD_FROM"],
							"INPUT_VALUE_FINISH"	=>	$arResult["PERIOD_TO"],
							"SHOW_TIME"	=>	"N"
						), $component,
						array("HIDE_ICONS" => "Y"));?></span>
				</div>
				<div id="photo-group-photo">
					<span class="field-value"><input type="checkbox" name="group_photo" id="group_photo" value="Y" <?=
					($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? " checked='checked'" : "")?> /></span>
					<span class="field-name"><label for="group_photo"><?=GetMessage("P_GROUP_BY_DATE_CREATE")?></label></span>
				</div>
				<?if ($arParams["MODERATE"] == "Y"):?>
				<div id="photo-moderate">
					<span class="field-value"><input type="checkbox" name="moderate" id="moderate" value="Y" <?=
					($arResult["SHOW_NOT_APPROVED"] == "Y" ? " checked='checked'" : "")?>/></span>
					<span class="field-name"><label for="moderate"><?=GetMessage("P_SHOW_ONLY_NOT_APPROVED")?></label></span>
				</div>
				<?endif;?>
				<div id="photo-submit">
					<input type="submit" name="photo_filter_submit" value="<?=GetMessage("P_FILTER_SHOW")?>" />
					<input type="submit" name="photo_filter_reset" value="<?=GetMessage("P_FILTER_RESET")?>" />
				</div>
			</form>
		</div>
		<div id="photo-filter-footer"></div>
	</div>
</div>

<div id="detail_list_order"><?
$res = array();
if ($arParams["SHOW_ONLY_PUBLIC"] == "Y")
	$res["PROPERTY_PUBLIC_ELEMENT"] = "Y";
if ($arResult["SHOW_NOT_APPROVED"] == "Y")
	$res["!PROPERTY_APPROVE_ELEMENT"] = "Y";
elseif ($arParams["SHOW_ONLY_APPROVED"] == "Y" && $_REQUEST["AJAX_CALL"] == "Y")
	$res["PROPERTY_APPROVE_ELEMENT"] = "Y";

if ($arResult["ORDER_BY"] == "shows")
	$res[">SHOW_COUNTER"] = "0";
elseif ($arResult["ORDER_BY"] == "rating")
	$res[">PROPERTY_RATING"] = "0";
elseif ($arResult["ORDER_BY"] == "comments")
{
	if ($arParams["COMMENTS_TYPE"] == "blog")
		$res[">PROPERTY_BLOG_COMMENTS_CNT"] = "0";
	elseif ($arParams["COMMENTS_TYPE"] == "forum")
		$res[">PROPERTY_FORUM_MESSAGE_CNT"] = "0";
}
if ($arResult["SHOW_NOT_APPROVED"] == "Y"):
?><div class="photo-controls">
	<a href="javascript:void(0);" onmousedown="Approve();" class="photo-action photo-moderate"><?=GetMessage("P_APPROVE_SELECTED")?></a>
</div>
<script type="text/javascript">
function Approve()
{
	var form = document.getElementById('photoForm');
	var bNotEmpty = false;
	if (form && form.elements["items[]"])
	{
		if (!form.elements["items[]"].length && form.elements["items[]"].checked)
		{
			bNotEmpty = true;
		}
		else if (form.elements["items[]"].length > 0)
		{
			for (var ii = 0; ii < form.elements["items[]"].length; ii++)
			{
				if (form.elements["items[]"][ii].checked == true)
				{
					bNotEmpty = true;
					break;
				}
			}
		}
		if (bNotEmpty)
		{
			form.elements['ACTION'].value = 'public'; 
			form.submit();
		}
	}
}
</script>
<?
endif;
if ($_REQUEST["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
endif;
ob_start();
?><?$APPLICATION->IncludeComponent(
	"bitrix:photogallery.detail.list", 
	($_REQUEST["AJAX_CALL"] == "Y" ? "ascetic" : $arParams["TEMPLATE_LIST"]), 
	Array(
		"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
		"IBLOCK_ID" => $arParams["IBLOCK_ID"],
		"SECTION_ID" => 0,
		"SECTION_CODE" => "",
		"USER_ALIAS" => "",
		"BEHAVIOUR" => "USER",
	
		"ELEMENTS_LAST_COUNT" => "",
		"ELEMENT_LAST_TYPE" => (!empty($arResult["PERIOD_FROM"]) || !empty($arResult["PERIOD_TO"]) ? "period" : ""),
		"ELEMENTS_LAST_TIME_FROM"	=>	$arResult["PERIOD_FROM"],
		"ELEMENTS_LAST_TIME_TO"	=>	$arResult["PERIOD_TO"],
		"ELEMENT_SORT_FIELD"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? "created_date" : $arResult["ORDER_BY"]),
		"ELEMENT_SORT_ORDER"	=>	"desc",
		"ELEMENT_SORT_FIELD1"	=>	($arResult["GROUP_BY_DATE_CREATE"] == "Y" ? $arResult["ORDER_BY"] : ""),
		"ELEMENT_SORT_ORDER1"	=>	"desc",
		"ELEMENT_FILTER" => $res,

		"GALLERY_URL"	=>	$arResult["URL_TEMPLATES"]["gallery"],
		"DETAIL_SLIDE_SHOW_URL"	=>	$arResult["URL_TEMPLATES"]["detail_slide_show"],
		"DETAIL_URL"	=>	$arResult["URL_TEMPLATES"]["detail"],
		"SEARCH_URL"	=>	$arResult["URL_TEMPLATES"]["search"],
		
		"USE_PERMISSIONS" => $arParams["USE_PERMISSIONS"],
		"GROUP_PERMISSIONS" => $arParams["GROUP_PERMISSIONS"],
		
		"USE_DESC_PAGE"	=>	$arParams["ELEMENTS_USE_DESC_PAGE"],
		"PAGE_ELEMENTS"	=>	($_REQUEST["AJAX_CALL"] == "Y" ? "10" : $arParams["ELEMENTS_PAGE_ELEMENTS"]),
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
		"SET_TITLE" => $arParams["SET_TITLE"],
		
		"THUMBS_SIZE"	=>	$arParams["THUMBS_SIZE"],
		"SHOW_PAGE_NAVIGATION"	=>	($_REQUEST["AJAX_CALL"] == "Y" ? "none" : "both"),
		
		"SHOW_CONTROLS"	=>	"N",
		"SHOW_INPUTS" => $arResult["SHOW_NOT_APPROVED"],
		"CELL_COUNT"	=>	$arParams["CELL_COUNT"],
		
		"SHOW_TAGS" => $arParams["SHOW_TAGS"],
		"SHOW_RATING" => $arParams["USE_RATING"],
		"SHOW_COMMENTS" => $arParams["USE_COMMENTS"],
		"SHOW_SHOWS" => "Y",
		"SHOW_DATE" => $arResult["GROUP_BY_DATE_CREATE"],
		"NEW_DATE_TIME_FORMAT" => (empty($arParams["DATE_FORMAT"]) ? $arParams["DATE_TIME_FORMAT_DETAIL"] : $arParams["DATE_FORMAT"]),
		
		"MAX_VOTE" => $arParams["MAX_VOTE"],
		"VOTE_NAMES" => $arParams["VOTE_NAMES"],		
	),
	$component,
	array("HIDE_ICONS" => "Y")
);?><?
$new = ob_get_clean();
$new = trim($new);

if (!empty($new)):
	?><?=$new?><?
endif;

if ($_REQUEST["AJAX_CALL"] == "Y"):
	if (empty($new)):
		?><div class="no-photo-text"><?=GetMessage("P_NO_PHOTO");?></div><?
	endif;
	?><div class="all-elements"><a href="<?=($APPLICATION->GetCurPageParam("", array("AJAX_CALL")))?>"><?
		if ($arResult["ORDER_BY"] == "date_create"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_DATE_CREATE")?><?
		elseif ($arResult["ORDER_BY"] == "shows"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_SHOWS")?><?
		elseif ($arResult["ORDER_BY"] == "rating"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_RATING")?><?
		elseif ($arResult["ORDER_BY"] == "comments"):
			?><?=GetMessage("P_PHOTO_ORDER_BY_COMMENTS")?><?
		endif;
	?></a></div><?
	die();
endif;

?></div>