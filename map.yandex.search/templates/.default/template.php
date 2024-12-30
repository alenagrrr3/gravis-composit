<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//$arParams['MAP_WIDTH'] = 'auto';
//$arParams['MAP_HEIGHT'] = '500';
?>
<div class="bx-yandex-search-layout">
	<div class="bx-yandex-search-form">
		<form name="search_form_<?echo $arParams['MAP_ID']?>" onsubmit="jsYandexSearch_<?echo $arParams['MAP_ID']?>.searchByAddress(this.address.value); return false;">
			<?echo GetMessage('MYMS_TPL_SEARCH')?>: <input type="text" name="address" value="" style="width: 300px;" /><input type="submit" value="<?echo GetMessage('MYMS_TPL_SUBMIT')?>" />
		</form>
	</div>

	<div class="bx-yandex-search-results" id="results_<?echo $arParams['MAP_ID']?>"></div>

	<div class="bx-yandex-search-map">
<?
$APPLICATION->IncludeComponent('bitrix:map.yandex.system', '.default', $arParams, null, array('HIDE_ICONS' => 'Y'));
?>
	</div>
	
</div>
<script type="text/javascript">
function BXWaitForMap_search<?echo $arParams['MAP_ID']?>() 
{
	if (('\v'=='v') && (null == window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>']))
	{
		setTimeout(this, 300);
	}
	else
	{
		window.jsYandexSearch_<?echo $arParams['MAP_ID']?> = new JCBXYandexSearch('<?echo $arParams['MAP_ID']?>', document.getElementById('results_<?echo $arParams['MAP_ID']?>'), {
			mess_error: '<?echo GetMessage('MYMS_TPL_JS_ERROR')?>',
			mess_search: '<?echo GetMessage('MYMS_TPL_JS_SEARCH')?>',
			mess_found: '<?echo GetMessage('MYMS_TPL_JS_RESULTS')?>',
			mess_search_empty: '<?echo GetMessage('MYMS_TPL_JS_RESULTS_EMPTY')?>'
		});
	}
}

if (window.attachEvent) // IE
	window.attachEvent("onload", function () {setTimeout(BXWaitForMap_search<?echo $arParams['MAP_ID']?>, 300)});
else if (window.addEventListener) // Gecko / W3C
	window.addEventListener('load', function () {setTimeout(BXWaitForMap_search<?echo $arParams['MAP_ID']?>, 300)}, false);
else
	window.onload = function () {setTimeout(BXWaitForMap_search<?echo $arParams['MAP_ID']?>, 300)};
</script>
