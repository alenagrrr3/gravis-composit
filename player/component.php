<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$player_type = $arParams['PLAYER_TYPE'];
$fp = $arParams['PATH'];

if (strlen($fp) > 0 && strpos($fp, '.') !== false)
$ext = (strlen($fp) > 0 && strpos($fp, '.') !== false) ? strtolower(GetFileExtension($fp)) : '';

if ($player_type == 'auto')
	$player_type = (in_array($ext, array('wmv', 'wma'))) ? 'wmv' : 'flv';

if ($ext == 'swf' && $arParams['ALLOW_SWF'] != 'Y')
	return CComponentUtil::__ShowError(GetMessage("SWF_DENIED"));

if (!function_exists(escapeFlashvar))
{
	function escapeFlashvar($str)
	{
		$str = str_replace('?', '%3F', $str);
		$str = str_replace('=', '%3D', $str);
		$str = str_replace('&', '%26', $str);
		return $str;
	}

	function isYes($str)
	{
		if (strtoupper($str) == 'Y')
			return 'true';
		return 'false';
	}

	function addFlashvar(&$str, $key, $value, $default)
	{
		if (!isset($value) || $value == '' || $value == $default)
			return;
		if (strlen($str) > 0)
			$str .= '&';
		$str .= $key.'='.escapeFlashvar($value);
	}

	function addWMVJSConfig(&$str, $key, $value, $default = false)
	{
		if (!isset($value) || $value == '' || $value === $default)
			return;
		if ($str != '{')
			$str .= ',';
		$str .= $key.': \''.$value.'\'';
	}

	function findCorrectFile($path, &$strWarn, $warning = false)
	{
		if (strpos($path, '://') !== false)
			return $path;
		$DOC_ROOT = $_SERVER["DOCUMENT_ROOT"];
		$path = Rel2Abs("/", $path);
		$path_ = $path;
		//if (!file_exists($DOC_ROOT.$path))
		//	$path = rtrim($GLOBALS['APPLICATION']->GetCurDir(), "/").$path;
		if (!file_exists($DOC_ROOT.$path))
		{
			if ($warning)
				$strWarn .= $warning."<br />";
			$path = $path_;
		}
		return $path;
	}
}

$warning = '';
$arResult["WIDTH"] = intval($arParams['WIDTH']);
if ($arResult["WIDTH"] <= 0)
	$arResult["WIDTH"] = 400;

$arResult["HEIGHT"] = intval($arParams['HEIGHT']);
if ($arResult["HEIGHT"] <= 0)
	$arResult["HEIGHT"] = 300;

$path = findCorrectFile($arParams['PATH'], $warning, GetMessage("INCORRECT_FILE"));
$preview = (strlen($arParams['PREVIEW'])) ? findCorrectFile($arParams['PREVIEW'], $w = '') : '';
$logo = (strlen($arParams['LOGO']) > 0) ? findCorrectFile($arParams['LOGO'], $w = '') : '';

if (intval($arParams['VOLUME']) > 100)
	$arParams['VOLUME'] = 100;
if (intval($arParams['VOLUME']) < 0)
	$arParams['VOLUME'] = 0;
if (isset($arParams['PLAYER_ID']) && strlen($arParams['PLAYER_ID']) > 0)
	$arResult["ID"] = $arParams['PLAYER_ID'];
else
	$arResult["ID"] = "bx_".$player_type."_player_".rand();

if ($player_type == 'flv') // FLASH PLAYER
{
	$fv = '';
	addFlashvar($fv, 'file', $path, null);
	addFlashvar($fv, 'image', $preview, '');
	addFlashvar($fv, 'logo', $logo, '');
	addFlashvar($fv, 'fullscreen', isYes($arParams['FULLSCREEN']), 'false');
	$skin = rtrim($arParams['SKIN_PATH'], "/")."/".$arParams['SKIN'];
	if ($arParams['SKIN'] != '' && $arParams['SKIN'] != 'default' &&
	file_exists($_SERVER["DOCUMENT_ROOT"].$skin) &&
	strtolower(GetFileExtension($arParams['SKIN'])) == 'swf')
		addFlashvar($fv, 'skin', $skin, '');
	addFlashvar($fv, 'controlbar', $arParams['CONTROLBAR'], 'bottom');
	addFlashvar($fv, 'playlist', $arParams['PLAYLIST'], 'none');
	addFlashvar($fv, 'playlistsize', $arParams['PLAYLIST_SIZE'], '180');
	addFlashvar($fv, 'autostart', isYes($arParams['AUTOSTART']), 'false');
	addFlashvar($fv, 'repeat', isYes($arParams['REPEAT']), 'false');
	addFlashvar($fv, 'volume', $arParams['VOLUME'], 90);
	addFlashvar($fv, 'displayclick', $arParams['DISPLAY_CLICK'], 'play');
	addFlashvar($fv, 'mute', isYes($arParams['MUTE']), 'false');
	addFlashvar($fv, 'quality', isYes($arParams['HIGH_QUALITY']), 'true');
	addFlashvar($fv, 'shuffle', isYes($arParams['SHUFFLE']), 'false');
	addFlashvar($fv, 'item', $arParams['START_ITEM'], '0');
	addFlashvar($fv, 'bufferlength', $arParams['BUFFER_LENGTH'], '1');
	addFlashvar($fv, 'link', $arParams['DOWNLOAD_LINK'], '');
	addFlashvar($fv, 'linktarget', $arParams['DOWNLOAD_LINK_TARGET'], '_self');
	addFlashvar($fv, 'abouttext', GetMessage('ABOUT_TEXT'), '');
	addFlashvar($fv, 'aboutlink', GetMessage('ABOUT_LINK'), '');
	if ($arParams['CONTENT_TYPE'])
		addFlashvar($fv, 'type', $arParams['CONTENT_TYPE'], '');
	$arResult["FLASH_VARS"] = $fv;
	//if (!$arParams['CONTROLBAR'] || $arParams['CONTROLBAR'] == 'bottom')
		//$arResult["HEIGHT"] += 24;
	$arResult["WMODE"] = $arParams['WMODE'];
	$arResult["MENU"] = $arParams['HIDE_MENU'] == 'Y' ? 'false' : 'true';
}
else // WMV PLAYER
{
	$conf = "{";
	addWMVJSConfig($conf, 'file', $path, '');
	addWMVJSConfig($conf, 'image', $preview, '');
	addWMVJSConfig($conf, 'logo', $logo, '');
	addWMVJSConfig($conf, 'width', $arResult["WIDTH"], 320);
	addWMVJSConfig($conf, 'height', $arResult["HEIGHT"], 260);
	addWMVJSConfig($conf, 'backcolor', $arParams["CONTROLS_BGCOLOR"], 'FFFFFF');
	addWMVJSConfig($conf, 'frontcolor', $arParams["CONTROLS_COLOR"], '000000');
	addWMVJSConfig($conf, 'lightcolor', $arParams["CONTROLS_OVER_COLOR"], '000000');
	addWMVJSConfig($conf, 'screencolor', $arParams["SCREEN_COLOR"], '000000');
	//addWMVJSConfig($conf, 'showicons', isYes($arParams["SHOWICONS"]), 'true');
	//overstretch (false): Sets how to stretch images/movies to make them fit the display. The default stretches to fit the display. Set this to true to stretch them proportionally to fill the display, fit to stretch them disproportionally and none to keep original dimensions.
	//addWMVJSConfig($conf, 'overstretch', isYes($arParams["KEEP_PROPORTION"]), 'true');
	addWMVJSConfig($conf, 'shownavigation', isYes($arParams["SHOW_CONTROLS"]), 'true');
	addWMVJSConfig($conf, 'showstop', isYes($arParams["SHOW_STOP"]), 'false');
	addWMVJSConfig($conf, 'showdigits', isYes($arParams["SHOW_DIGITS"]), 'true');
	//showdownload (false): Set this to true to show a button in the player controlbar which links to the link flashvar.
	addWMVJSConfig($conf, 'autostart', isYes($arParams["AUTOSTART"]), 'false');
	addWMVJSConfig($conf, 'repeat', isYes($arParams["REPEAT"]), 'false');
	addWMVJSConfig($conf, 'volume', $arParams['VOLUME'], 80);
	addWMVJSConfig($conf, 'bufferlength', $arParams['BUFFER_LENGTH'], 3);
	addWMVJSConfig($conf, 'link', $arParams['DOWNLOAD_LINK'], '');
	addWMVJSConfig($conf, 'linktarget', $arParams['DOWNLOAD_LINK_TARGET'], '_self');
	if ($arParams["WMODE_WMV"] == 'windowless')
	{
		addWMVJSConfig($conf, 'windowless', 'true', '');
		addWMVJSConfig($conf, 'usefullscreen', 'false', '');
	}
	else
	{
		addWMVJSConfig($conf, 'usefullscreen', isYes($arParams["FULLSCREEN"]), 'true');
	}
	//linkfromdisplay (false): Set this to true to make a click on the display result in a jump to the webpage assigned to the link flashvar.
	$conf .= "}";
	$arResult["WMV_CONFIG"] = $conf;
	if ($arParams["SHOW_CONTROLS"] == 'Y')
		$arResult["HEIGHT"] += 20;

	$arResult["USE_JS_PLAYLIST"] = (($arParams["USE_PLAYLIST"] == 'Y'));
	$playlist_conf = false;
	if ($arResult["USE_JS_PLAYLIST"])
	{
		$playlist_conf = '{';
		addWMVJSConfig($playlist_conf, 'format', $arParams['PLAYLIST_TYPE'], 'xspf');
		addWMVJSConfig($playlist_conf, 'size', $arParams['PLAYLIST_SIZE'], '180');
		addWMVJSConfig($playlist_conf, 'image_height', $arParams['PLAYLIST_PREVIEW_HEIGHT'], 48);
		addWMVJSConfig($playlist_conf, 'image_width', $arParams['PLAYLIST_PREVIEW_WIDTH'], 64);
		addWMVJSConfig($playlist_conf, 'position', $arParams['PLAYLIST'] == 'right' ? 'right' : 'bottom', 'right');
		addWMVJSConfig($playlist_conf, 'path', $path, '');
		$playlist_conf .= "}";
	}
	$arResult["PLAYLIST_CONFIG"] = $playlist_conf;
}
$arResult["PLAYER_TYPE"] = $player_type;

if($arParams["USE_PLAYLIST"] == 'Y')
{
	$playlistExists = file_exists($_SERVER["DOCUMENT_ROOT"].$path);
	if (!$playlistExists)
		$warning = GetMessage('INCORRECT_PLAYLIST');

	//Icons
	$bShowIcon = ($USER->IsAuthorized() && ($APPLICATION->GetPublicShowMode() == 'configure' || $APPLICATION->GetPublicShowMode() == 'edit'));
	if ($bShowIcon && strlen($path) > 0)
	{
		$playlist_edit_url = $APPLICATION->GetPopupLink(
			array(
				"URL"=> "/bitrix/components/bitrix/player/player_playlist_edit.php?lang=".LANGUAGE_ID.
					"&site=".SITE_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]).
					"&path=".urlencode($path)."&contID=".urlencode($arResult["ID"]),
				"PARAMS" => array(
					'width' => '850',
					'height' => '400'
				)
			),
			'playlist' // suffix using instance of JCPopup: jsPopup_playlist
		);
		if (!$playlistExists)
			$warning .= '<br><a href="javascript:'.$playlist_edit_url.'">'.GetMessage("PLAYER_PLAYLIST_ADD").'</a>';
		$arIcons = Array(Array(
			"URL" => 'javascript:'.$playlist_edit_url,
			"ICON" => "playlist-edit",
			"TITLE" => ($playlistExists ? GetMessage("PLAYER_PLAYLIST_EDIT") : GetMessage("PLAYER_PLAYLIST_ADD")),
			"DEFAULT" => $APPLICATION->GetPublicShowMode() == 'edit',
		));
		echo '<script>if (JCPopup) {window.jsPopup_playlist = new JCPopup({suffix: "playlist", zIndex: 2000});}</script>'; // create instance of JCPopup: jsPopup_playlist
		$this->AddIncludeAreaIcons($arIcons);
	}
}

// add scripts
if ($player_type == "flv")
{
	$j = '/bitrix/components/bitrix/player/mediaplayer/flvscript.js';
	$strHead = '<script type="text/javascript" src="'.$j.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$j).'"></script>';
}
else // wmv
{
	$j = '/bitrix/components/bitrix/player/wmvplayer/silverlight.js';
	$strHead = '<script type="text/javascript" src="'.$j.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$j).'"></script>';
	$j = '/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js';
	$strHead .= '<script type="text/javascript" src="'.$j.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$j).'"></script>';

	if ($arResult["USE_JS_PLAYLIST"])
	{
		$strHead .= '<script>var JSMESS = {ClickToPLay : "'.GetMessage('JS_CLICKTOPLAY').'", Link : "'.GetMessage('JS_LINK').'", PlayListError: "'.GetMessage('JS_PLAYLISTERROR').'"};</script>';
		$j = '/bitrix/components/bitrix/player/templates/.default/wmvscript_playlist.js';
		$strHead .= '<script type="text/javascript" src="'.$j.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$j).'"></script>';
		$s = '/bitrix/components/bitrix/player/templates/.default/wmvplaylist.css';
		$strHead .= '<link rel="stylesheet" type="text/css" href="'.$s.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$s).'"></script>';
	}
	else
	{
		$j = '/bitrix/components/bitrix/player/wmvplayer/wmvscript.js';
		$strHead .= '<script type="text/javascript" src="'.$j.'?v='.filemtime($_SERVER['DOCUMENT_ROOT'].$j).'"></script>';
	}
}
$GLOBALS['APPLICATION']->AddHeadString($strHead, true);


if (strlen($warning) > 0)
{
	CComponentUtil::__ShowError($warning);
	return;
}
$this->IncludeComponentTemplate();
?>