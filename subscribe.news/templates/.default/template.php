<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<table cellpadding="0" cellspacing="10" border="0">
<?
foreach($arResult["IBLOCKS"] as $arIBlock):
	if(count($arIBlock["ITEMS"]) > 0):
?>
        <tr><td><h1><?=$arIBlock['NAME']?></h1></td></tr>
<?
	foreach($arIBlock["ITEMS"] as $arItem):
?>
	<tr><td>
		<font class="text">
		<?if($arItem["PREVIEW_PICTURE"]):?>
		<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><img hspace='5' vspace='5' align='left' border='0' src="<?echo $arItem["PREVIEW_PICTURE"]["SRC"]?>" width="100" height="100" alt="<?echo $arItem["PREVIEW_PICTURE"]["ALT"]?>"  title="<?echo $arItem["NAME"]?>"></a>
		<?endif;?>
		<?if(strlen($arItem["DATE_ACTIVE_FROM"])>0):?>
			<font class="newsdata"><?echo $arItem["DATE_ACTIVE_FROM"]?></font><br>
		<?endif;?>
		<a href="<?echo $arItem["DETAIL_PAGE_URL"]?>"><b><?echo $arItem["NAME"]?></b></a><br>
		<?echo $arItem["PREVIEW_TEXT"];?>
		</font>
	</td></tr>
<?
	endforeach;
	endif;
?>
<?endforeach?>
</table>
