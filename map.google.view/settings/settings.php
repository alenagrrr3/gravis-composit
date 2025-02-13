<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_js.php");

__IncludeLang($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/map.google.view/lang/'.LANGUAGE_ID.'/settings.php');

//if(!$USER->IsAdmin())
//	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED")); 

$obJSPopup = new CJSPopup('',
	array(
		'TITLE' => GetMessage('MYMV_SET_POPUP_TITLE'),
		'SUFFIX' => 'google_map',
		'ARGS' => ''
	)
);

if ($_REQUEST['MAP_DATA'])
{

	$arData = unserialize($APPLICATION->UnJSEscape($_REQUEST['MAP_DATA']));

	if (is_array($arData) && is_array($arData['PLACEMARKS']) && ($cnt = count($arData['PLACEMARKS'])))
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arData['PLACEMARKS'][$i]['TEXT'] = str_replace('###RN###', "\r\n", $arData['PLACEMARKS'][$i]['TEXT']);
		}
	}
}
else
	$arData = array();
?>
<script type="text/javascript" src="/bitrix/components/bitrix/map.google.view/settings/settings_load.js"></script>
<script type="text/javascript">
jsUtils.loadCSSFile('/bitrix/components/bitrix/map.google.view/settings/settings.css');
var arPositionData = <?echo is_array($arData) && count($arData) > 0 ? CUtil::PhpToJsObject($arData) : '{}'?>;
window._global_BX_UTF = <?echo defined('BX_UTF') && BX_UTF == true ? 'true' : 'false'?>;
window.jsGoogleMess = {
	noname: '<?echo GetMessage('MYMV_SET_NONAME')?>',
	MAP_VIEW_MAP: '<?echo GetMessage('MYMS_PARAM_INIT_MAP_TYPE_MAP')?>', 
	MAP_VIEW_SATELLITE: '<?echo GetMessage('MYMS_PARAM_INIT_MAP_TYPE_SATELLITE')?>', 
	MAP_VIEW_HYBRID: '<?echo GetMessage('MYMS_PARAM_INIT_MAP_TYPE_HYBRID')?>',
	current_view: '<?echo CUtil::JSEscape($_REQUEST['INIT_MAP_TYPE'])?>',
	nothing_found: '<?echo GetMessage('MYMS_PARAM_INIT_MAP_NOTHING_FOUND')?>'
};
</script>
<form name="bx_popup_form_google_map">
<?
$obJSPopup->ShowTitlebar();
$obJSPopup->StartDescription('bx-edit-menu');
?>
	<p><b><?echo GetMessage('MYMV_SET_POPUP_WINDOW_TITLE')?></b></p>
	<p class="note"><?echo GetMessage('MYMV_SET_POPUP_WINDOW_DESCRIPTION')?></p>
<?
$obJSPopup->StartContent();
?>
<div id="bx_google_map_control" style="position: absolute; margin-right: 275px; margin-top: -2px; margin-left: -2px; border: solid 1px #B8C1DD;">
<?
$APPLICATION->IncludeComponent('bitrix:map.google.system', '', array(
	'KEY' => $_REQUEST['KEY'],
	'INIT_MAP_TYPE' => $_REQUEST['INIT_MAP_TYPE'],
	'MAP_WIDTH' => 500,
	'MAP_HEIGHT' => 385,
	'INIT_MAP_LAT' => $arData['google_lat'],
	'INIT_MAP_LON' => $arData['google_lon'],
	'INIT_MAP_SCALE' => $arData['google_scale'],
	'MAP_ID' => 'system_view_edit',
	'DEV_MODE' => 'Y',
), false, array('HIDE_ICONS' => 'Y'));
?>
</div><div class="bx-google-map-address-search" id="bx_google_map_address_search" style="visibility: hidden; ">
	<?echo GetMessage('MYMV_SET_ADDRESS_SEARCH')?>: <input type="text" name="address" value="" style="width: 400px;" onkeypress="jsGoogleCESearch.setTypingStarted(this)" autocomplete="off" />
</div><div class="bx-google-map-controls" id="bx_google_map_controls" style="margin-left: 510px; visibility: hidden;">
	<div class="bx-google-map-controls-group">
		<b><?echo GetMessage('MYMV_SET_START_POS')?></b><br />
			<ul id="bx_google_position">
				<li><?echo GetMessage('MYMV_SET_START_POS_LAT')?>: <span class="bx-google-map-controls-value" id="bx_google_lat_value"></span><input type="hidden" name="bx_google_lat" value="<?echo $arData['google_lat']?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_LON')?>: <span class="bx-google-map-controls-value" id="bx_google_lon_value"></span><input type="hidden" name="bx_google_lon" value="<?echo $arData['google_lon']?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_SCALE')?>: <span class="bx-google-map-controls-value" id="bx_google_scale_value"></span><input type="hidden" name="bx_google_scale" value="<?echo $arData['google_scale']?>" /></li>
				<li><?echo GetMessage('MYMV_SET_START_POS_VIEW')?>: <span class="bx-google-map-controls-value" id="bx_google_view_value"></span><input type="hidden" name="bx_google_view" value="<?echo htmlspecialchars($_REQUEST['INIT_MAP_TYPE'])?>" /></li>
				<li><input type="checkbox" id="bx_google_position_fix" name="bx_google_position_fix" value="Y"<?if ($arData['google_scale']):?> checked="checked"<?endif;?> onclick="jsGoogleCE.setFixedFlag(this.checked)" /> <label for="bx_google_position_fix"><?echo GetMessage('MYMV_SET_START_POS_FIX')?></label>&nbsp;|&nbsp;<a href="javascript:void(0)" id="bx_restore_position"><?echo GetMessage('MYMV_SET_START_POS_RESTORE')?></a>
			</ul>
	</div>
	<div class="bx-google-map-controls-group" id="bx_google_points_group">
		<b><?echo GetMessage('MYMV_SET_POINTS')?></b><br />
		<ul id="bx_google_points"></ul>
		<a href="javascript: void(0)" onclick="jsGoogleCE.addPoint(); return false;" id="bx_google_addpoint_link" style="display: block;"><?echo GetMessage('MYMV_SET_POINTS_ADD')?></a>
		<div id="bx_google_addpoint_message" style="display: none;"><?echo GetMessage('MYMV_SET_POINTS_ADD_DESCRIPTION')?> <a href="javascript:void(0)" onclick="jsGoogleCE.addPoint(); return false;"><?echo GetMessage('MYMV_SET_POINTS_ADD_FINISH')?></a>.</div>
	</div>	
</div>
<script type="text/javascript">
if (null != window.jsGoogleCESearch)
	jsGoogleCESearch.clear();

if (window.GMap2)
{
	jsGoogleCE.init();
	//(function BXWaitForMap1(){if(window.bGoogleMapScriptsLoaded){jsGoogleCE.init();}else{setTimeout(BXWaitForMap1,500)}})();
}
else
{
(function BXWaitForMap(){if(null==window.GLOBAL_arMapObjects)return;if(window.GLOBAL_arMapObjects['system_view_edit']){jsGoogleCE.init();}else{setTimeout(BXWaitForMap,300);}})();
}
</script>
<?
$obJSPopup->StartButtons();
?>
<input type="submit" value="<?echo GetMessage('MYMV_SET_SUBMIT')?>" onclick="return jsGoogleCE.__saveChanges();"/>
<?
$obJSPopup->ShowStandardButtons(array('cancel'));
$obJSPopup->EndButtons();
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_js.php");?>