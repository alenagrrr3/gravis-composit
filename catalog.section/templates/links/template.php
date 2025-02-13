<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="catalog-section">
<?$APPLICATION->IncludeComponent("bitrix:catalog.section.list", "tree", array(
	"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
	"IBLOCK_ID" => $arParams["IBLOCK_ID"],
	"SECTION_ID" => "0",
	"COUNT_ELEMENTS" => "Y",
	"TOP_DEPTH" => "2",
	"SECTION_URL" => $arParams["SECTION_URL"],
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"DISPLAY_PANEL" => "N",
	"ADD_SECTIONS_CHAIN" => $arParams['ADD_SECTIONS_CHAIN']
	),
	$component	
);?> 
 
<?if($arParams["DISPLAY_TOP_PAGER"]):?>
	<?=$arResult["NAV_STRING"]?><br />
<?endif;?>
<table cellpadding="0" cellspacing="0" border="0">
		<?foreach($arResult["ITEMS"] as $cell=>$arElement):?>


<?
		$arElement["DETAIL_PAGE_URL"] = $arElement["DISPLAY_PROPERTIES"]["URL"]["VALUE"];
?>
		<?if($cell%$arParams["LINE_ELEMENT_COUNT"] == 0):?>
		<tr>
		<?endif;?>

		<td valign="top" width="<?=round(100/$arParams["LINE_ELEMENT_COUNT"])?>%">
<?
if($USER->IsAuthorized() && $APPLICATION->GetPublicShowMode()!== 'view'):
?>
<?
$ar = CIBlock::ShowPanel($arParams["IBLOCK_ID"], $arElement["ID"], 0, $arParams["IBLOCK_TYPE"], true);

if(is_array($ar))
	foreach($ar as $arButton):
if(preg_match("/[^A-Z0-9_]ID=\d+/", $arButton["URL"])):
		?>
		<a href="<?echo htmlspecialchars($arButton["URL"])?>" title="<?echo htmlspecialchars($arButton["TITLE"])?>"><img src="<?=$arButton["IMAGE"]?>" width="20" height="20" border="0" /></a>&nbsp;&nbsp;
		<?
endif;
	endforeach;
?>
<?endif;?>

			<table cellpadding="0" cellspacing="2" border="0">
				<tr>
					<?if(is_array($arElement["PREVIEW_PICTURE"])):?>
						<td valign="top">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" target="_blank"><img border="0" src="<?=$arElement["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arElement["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arElement["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["PREVIEW_PICTURE"]["ALT"]?>" title="<?=$arElement["NAME"]?>" /></a><br />
						</td>
					<?elseif(is_array($arElement["DETAIL_PICTURE"])):?>
						<td valign="top">
						<a href="<?=$arElement["DETAIL_PAGE_URL"]?>" target="_blank"><img border="0" src="<?=$arElement["DETAIL_PICTURE"]["SRC"]?>" width="<?=$arElement["DETAIL_PICTURE"]["WIDTH"]?>" height="<?=$arElement["DETAIL_PICTURE"]["HEIGHT"]?>" alt="<?=$arElement["DETAIL_PICTURE"]["ALT"]?>" title="<?=$arElement["NAME"]?>" /></a><br />
						</td>
					<?endif?>
					<td valign="top"><a href="<?=$arElement["DETAIL_PAGE_URL"]?>" target="_blank"><?=$arElement["NAME"]?></a><br />
						<br />
						<?=$arElement["PREVIEW_TEXT"]?>
					</td>
				</tr>
			</table>

			&nbsp;
		</td>

		<?$cell++;
		if($cell%$arParams["LINE_ELEMENT_COUNT"] == 0):?>
			</tr>
		<?endif?>

		<?endforeach; // foreach($arResult["ITEMS"] as $arElement):?>

		<?if($cell%$arParams["LINE_ELEMENT_COUNT"] != 0):?>
			<?while(($cell++)%$arParams["LINE_ELEMENT_COUNT"] != 0):?>
				<td>&nbsp;</td>
			<?endwhile;?>
			</tr>
		<?endif?>

</table>
<?if($arParams["DISPLAY_BOTTOM_PAGER"]):?>
	<br /><?=$arResult["NAV_STRING"]?>
<?endif;?>
</div>
