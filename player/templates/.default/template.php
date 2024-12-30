<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if ($arResult["PLAYER_TYPE"] == "flv"): // Attach Flash Player?>
<div id="<?=$arResult["ID"]?>_div" style="display:none">
<object
	classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0"
	width="<?=$arResult['WIDTH']?>"
	height="<?=$arResult['HEIGHT']?>"
	id="<?=$arResult["ID"]?>">
	<param name="movie" value="/bitrix/components/bitrix/player/mediaplayer/player.swf">
	<param name="quality" value="high">
	<param name="wmode" value="<?=$arResult['WMODE']?>">
	<param name="flashvars" value="<?=$arResult['FLASH_VARS']?>">
	<param name="allowscriptaccess" value="always">
	<param name="allowfullscreen" value="true">
	<embed
		id="<?=$arResult["ID"]?>_embed"
		name="<?=$arResult["ID"]?>_embed"
		src="/bitrix/components/bitrix/player/mediaplayer/player.swf"
		type="application/x-shockwave-flash"
		width="<?=$arResult['WIDTH']?>"
		height="<?=$arResult['HEIGHT']?>"
		allowscriptaccess="always"
		allowfullscreen="true"
		menu="<?=$arResult['MENU']?>"
		wmode="<?=$arResult['WMODE']?>"
		flashvars="<?=$arResult['FLASH_VARS']?>"
	/>
</object>
</div>
<script>
showFLVPlayer('<?=$arResult["ID"]?>', "<?=GetMessage('INSTALL_FLASH_PLAYER')?>");
</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>
<?elseif ($arResult["PLAYER_TYPE"] == "wmv"): // Attach WMV Player?>
<div id="<?=$arResult["ID"]?>"></div>
<script>
showWMVPlayer('<?=$arResult["ID"]?>', <?=$arResult['WMV_CONFIG']?>, <?=($arResult['PLAYLIST_CONFIG'] ? $arResult['PLAYLIST_CONFIG'] : '{}')?>);
</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>
<?endif;?>