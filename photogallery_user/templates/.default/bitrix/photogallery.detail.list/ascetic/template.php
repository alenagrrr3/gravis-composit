<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 120);
if ($arParams["PICTURES_SIGHT"] != "standart" && intVal($arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"]) > 0)
	$arParams["THUMBS_SIZE"] = $arParams["PICTURES"][$arParams["PICTURES_SIGHT"]]["size"];

$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ? 
		$arParams["SHOW_PAGE_NAVIGATION"] : "none");

$arParams["SQUARE"] = ($arParams["SQUARE"] == "N" ? "N" : "Y");
$arParams["PERCENT"] = (intVal($arParams["PERCENT"]) > 0 ? intVal($arParams["PERCENT"]) : 70)/100;
/********************************************************************
				Input params
********************************************************************/
if ($arParams["SQUARE"] == "Y")
	$div_size = ($arParams["THUMBS_SIZE"] * $arParams["PERCENT"]);
if (!empty($arResult["ERROR_MESSAGE"])):
?><div class="photo-error"><?=ShowError($arResult["ERROR_MESSAGE"])?></div>
	<div class="empty-clear"></div><?
endif;

if (!empty($arResult["ELEMENTS_LIST"]) && is_array($arResult["ELEMENTS_LIST"])):

if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
	?><div class="photo-navigation"><?=$arResult["NAV_STRING"]?></div>
	<div class="empty-clear"></div><?
endif;

?><div class="photo-photos"><?
foreach ($arResult["ELEMENTS_LIST"]	as $key => $arItem):
	if (!is_array($arItem))
		continue;

if ($arParams["SQUARE"] == "Y"):
	$margin_left = 0 - intVal(($arItem["PICTURE"]["WIDTH"] - $div_size)/2);
	$margin_top = 0 - intVal(($arItem["PICTURE"]["HEIGHT"] - $div_size)/2);
?><div class="photo-ascetic" style="width:<?=$div_size?>px; height:<?=$div_size?>px; overflow:hidden;">
	<a href="<?=$arItem["URL"]?>" class="photo-simple" style="display:block; overflow:hidden;">
		<img src="<?=$arItem["PICTURE"]["SRC"]?>" width="<?=$arItem["PICTURE"]["WIDTH"]?>" height="<?=$arItem["PICTURE"]["HEIGHT"]?>" <?
			?>border="0" alt="<?=htmlspecialchars($arItem["CODE"])?>" title="<?=htmlspecialchars($arItem["~NAME"])?>" <?
			?>style="margin-left: <?=$margin_left?>px; margin-top: <?=$margin_top?>px; position:static;"/><?
	?></a><?
?></div><?
else:
?><div class="photo-ascetic" style="width:<?=($arParams["THUMBS_SIZE"] + 10)?>px; height:<?=($arParams["THUMBS_SIZE"] + 10)?>px;">
	<a href="<?=$arItem["URL"]?>" class="photo-simple"><?
			?><?=CFile::ShowImage($arItem["PICTURE"]["SRC"], $arParams["THUMBS_SIZE"], $arParams["THUMBS_SIZE"], 
			"border=\"0\" vspace=\"0\" hspace=\"0\" alt=\"".htmlspecialchars($arItem["CODE"]).
			"\" title=\"".htmlspecialchars($arItem["~NAME"])."\"");?></a><?
?></div><?
endif;
endforeach;
?></div><?
if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
	?><div class="empty-clear"></div>
	<div class="photo-navigation"><?=$arResult["NAV_STRING"]?></div><?
endif;

endif;

if (!empty($arResult["ERROR_MESSAGE"])):
	?><div class="empty-clear"></div>
	<div class="photo-error"><?=ShowError($arResult["ERROR_MESSAGE"])?></div><?
endif;
?>
<div class="empty-clear"></div>