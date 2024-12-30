<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<script type="text/javascript">
if (!window.GLOBAL_arMapObjects)
	window.GLOBAL_arMapObjects = {};

function init_<?echo $arParams['MAP_ID']?>() 
{
	if (!window.GMap2)
		return;
		
	window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'] = new GMap2(document.getElementById("BX_GMAP_<?echo $arParams['MAP_ID']?>"));
	var map = window.GLOBAL_arMapObjects['<?echo $arParams['MAP_ID']?>'];
	
	map.setMapType(G_<?echo $arParams['INIT_MAP_TYPE']?>_MAP);
	map.setCenter(new GLatLng(<?echo $arParams['INIT_MAP_LAT']?>, <?echo $arParams['INIT_MAP_LON']?>), <?echo $arParams['INIT_MAP_SCALE']?>);

<?
foreach ($arResult['ALL_MAP_OPTIONS'] as $option => $method)
{
	if (in_array($option, $arParams['OPTIONS'])):
?>
	map.enable<?echo $method?>();
<?
	else:
?>
	map.disable<?echo $method?>();
<?
	endif;
}
foreach ($arResult['ALL_MAP_CONTROLS'] as $control => $method)
{
	if (in_array($control, $arParams['CONTROLS'])):
?>
	map.addControl(new G<?echo $method?>Control());
<?	
	endif;
}
if ($arParams['DEV_MODE'] == 'Y'):
?>
	window.bGoogleMapScriptsLoaded = true;
<?
endif;
?>
}
<?
if ($arParams['DEV_MODE'] == 'Y'):
?>
function BXMapLoader_<?echo $arParams['MAP_ID']?>(MAP_KEY)
{
	if (null == window.bGoogleMapScriptsLoaded)
	{
		if (window.GMap2) 
		{
			window.bGoogleMapScriptsLoaded = true;
			
			init_<?echo $arParams['MAP_ID']?>();
		}
		else
		{
			var obScript = document.createElement('SCRIPT');
			obScript.type = 'text/javascript';
			obScript.charset = 'utf-8';
			obScript.setAttribute('charset', 'utf-8');
			
			if ('\v'=='v') // IsIE
			{
				obScript.onreadystatechange = function() {
					if (obScript.readyState == 'loaded'&&null!=window.google) google.load('maps', <?echo intval($arParams['GOOGLE_VERSION'])?>, {callback: init_<?echo $arParams['MAP_ID']?>});
				}
			}
			else
				obScript.onload = function () {
					google.load('maps', <?echo intval($arParams['GOOGLE_VERSION'])?>, {callback: init_<?echo $arParams['MAP_ID']?>});
				};

			obScript.src = 'http://www.google.com/jsapi?key=' + (window.encodeURIComponent ? window.encodeURIComponent(MAP_KEY) : escape(MAP_KEY)) + '&rnd=' + Math.random();
			
			document.getElementsByTagName('HEAD')[0].appendChild(obScript);
		}
	}
	else
	{
		init_<?echo $arParams['MAP_ID']?>();
	}
}
<?
	if (!$arParams['WAIT_FOR_EVENT']):
?>
BXMapLoader_<?echo $arParams['MAP_ID']?>('<?echo $arParams['KEY']?>');
<?
	else:
		echo CUtil::JSEscape($arParams['WAIT_FOR_EVENT']),' = BXMapLoader_',$arParams['MAP_ID'],';';
	endif;
else:
?>
if (window.attachEvent) // IE
	window.attachEvent("onload", init_<?echo $arParams['MAP_ID']?>);
else if (window.addEventListener) // Gecko / W3C
	window.addEventListener('load', init_<?echo $arParams['MAP_ID']?>, false);
else
	window.onload = init_<?echo $arParams['MAP_ID']?>;
<?
endif;
?>
</script>
<div id="BX_GMAP_<?echo $arParams['MAP_ID']?>" class="bx-google-map" style="height: <?echo $arParams['MAP_HEIGHT'];?>; width: <?echo $arParams['MAP_WIDTH']?>;"><?echo GetMessage('MYS_LOADING'.($arParams['WAIT_FOR_EVENT'] ? '_WAIT' : ''));?></div>