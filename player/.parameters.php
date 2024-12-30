<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$type = $arCurrentValues["PLAYER_TYPE"] ? $arCurrentValues["PLAYER_TYPE"] : 'auto';
$type_ = $type;
$adv_mode = ($arCurrentValues["ADVANCED_MODE_SETTINGS"] == 'Y');
$hidden = ($adv_mode) ? "N" : "Y";

function getSkins($path)
{
	$basePath = $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $path);
	$arSkins = Array('default' => GetMessage('PC_DEFAUL_SKIN'));
	if (!file_exists($basePath) || !is_dir($basePath))
		return $arSkins;
	$handle  = @opendir($basePath);
	while(false !== ($f = @readdir($handle)))
	{
		if($f == "." || $f == ".." || $f == ".htaccess")
			continue;
		if(strtolower(GetFileExtension($f)) != 'swf')
			continue;
		$arSkins[$f] = substr($f, 0, -4);
	}
	return $arSkins;
}

$fp = $arCurrentValues["PATH"];
if ($type == 'auto' && strlen($fp) > 0 && strpos($fp, '.') !== false)
{
	$ext = strtolower(GetFileExtension($fp));
	$type = (in_array($ext, array('wmv', 'wma'))) ? 'wmv' : 'flv';
}

$arComponentParameters = array();
$arComponentParameters["GROUPS"] = array(
	"BASE_SETTINGS" => array("NAME" => GetMessage("PC_GROUP_BASE_SETTINGS"), "SORT" => "100"),
	"ADDITIONAL_SETTINGS" => array("NAME" => GetMessage("PC_GROUP_ADDITIONAL_SETTINGS"), "SORT" => "300")
);

if ($type == 'auto' && $adv_mode)
{
	$arComponentParameters["GROUPS"]["APPEARANCE"] = array(
		"NAME" => GetMessage("PC_GROUP_APPEARANCE_COMMON"),
		"SORT" => "140"
	);
	$arComponentParameters["GROUPS"]["PLAYBACK_FLV"] = array(
		"NAME" => GetMessage("PC_GROUP_PLAYBACK_FLV"),
		"SORT" => "210"
	);
}

if ($type == 'flv' || $type == 'auto')
{
	if ($adv_mode)
	{
		$arComponentParameters["GROUPS"]["APPEARANCE_FLV"] = array(
			"NAME" => ($type == 'auto') ? GetMessage("PC_GROUP_APPERANCE_FLV") : GetMessage("PC_GROUP_APPERANCE"),
			"SORT" => "150"
		);
	}
}

if ($type == 'wmv' || $type == 'auto')
{
	if ($adv_mode)
		$arComponentParameters["GROUPS"]["APPEARANCE_WMV"] = array(
			"NAME" => ($type == 'auto') ? GetMessage("PC_GROUP_APPERANCE_WMV") : GetMessage("PC_GROUP_APPERANCE"),
			"SORT" => "160"
		);

	$arComponentParameters["GROUPS"]["PLAYBACK"] = array(
		"NAME" => GetMessage("PC_GROUP_PLAYBACK"),
		"SORT" => "200"
	);
}

if ($type == 'flv')
{
	$arComponentParameters["GROUPS"]["PLAYBACK_FLV"] = array(
		"NAME" =>  GetMessage("PC_GROUP_PLAYBACK"),
		"SORT" => "210"
	);
}

// * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
$arParams = array(); // $arComponentParameters["PARAMETERS"]
$arParams["PLAYER_TYPE"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_PLAYER_TYPE"),
	"TYPE" => "LIST",
	"VALUES" => array(
		"auto" => GetMessage("PC_PAR_PLAYER_AUTODETECT"),
		"flv" => GetMessage("PC_PAR_PLAYER_FLV"),
		"wmv" => GetMessage("PC_PAR_PLAYER_WMV")
	),
	"DEFAULT" => $type,
	"REFRESH" => "Y",
	"HIDDEN" => $hidden,
);

//if ($type == 'flv')
//{
$arParams["USE_PLAYLIST"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_USE_PLAYLIST"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
	"HIDDEN" => $hidden,
);
//}
if ($arCurrentValues["USE_PLAYLIST"] == 'Y')
	$ext = 'xml';
elseif($type == 'flv')
	$ext = 'flv,vp6,mp3,mp4,aac,jpg,jpeg,gif,png';
elseif($type == 'wmv')
	$ext = 'wmv,wma';
else
	$ext = 'wmv,wma,flv,vp6,mp3,mp4,aac,jpg,jpeg,gif,png';

$arParams["PATH"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => $arCurrentValues['USE_PLAYLIST'] != 'Y' ? GetMessage("PC_PAR_FILE_PATH") : GetMessage("PC_PAR_PLAYLIST_PATH"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => $ext,
	"FD_UPLOAD" => true,
	"DEFAULT" => "",
);
//if ($type_ == 'auto' && $adv_mode)
//	$arParams["PATH"]["REFRESH"] = "Y";

$bPlaylistExists = true;

if ($arCurrentValues["USE_PLAYLIST"] == 'Y')
{
	$butTitle = $bPlaylistExists ? GetMessage("PC_PAR_EDIT") : GetMessage("PC_PAR_CREATE");
	$arParams["PLAYLIST_DIALOG"] = Array(
		"PARENT" => "BASE_SETTINGS",
		"NAME" => GetMessage("PC_PAR_PLAYLIST_BUT"),
		"TYPE" => "CUSTOM",
		"JS_FILE" => "/bitrix/components/bitrix/player/playlist_edit/prop_playlist_edit.js",
		"JS_EVENT" => "ComponentPropsEditPlaylistDialog",
		"JS_DATA" => $butTitle.'||'.GetMessage("ERROR_EMPTY_PATH"),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
}

$arParams["WIDTH"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_WIDTH"),
	"COLS" => 10,
	"DEFAULT" => 400,
);
$arParams["HEIGHT"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_HEIGHT"),
	"COLS" => 10,
	"DEFAULT" => 300,
);
$arParams["PREVIEW"] = Array(
	"PARENT" => "BASE_SETTINGS",
	"NAME" => GetMessage("PC_PAR_PREVIEW_IMAGE"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => "png,gif,jpg,jpeg",
	"FD_UPLOAD" => true,
	"DEFAULT" => '',
	"HIDDEN" => $hidden,
);

//APPEARANCE
$appearance_parent = $type == 'auto' ? 'APPEARANCE' : 'APPEARANCE_'.strtoupper($type);
$arParams["LOGO"] = Array(
	"PARENT" => $appearance_parent,
	"NAME" => GetMessage("PC_PAR_LOGO"),
	"TYPE" => "FILE",
	"FD_TARGET" => "F",
	"FD_EXT" => "png,gif,jpg,jpeg",
	"FD_UPLOAD" => true,
	"DEFAULT" => "",
	"HIDDEN" => $hidden,
);
$arParams["FULLSCREEN"] = Array(
	"PARENT" => $appearance_parent,
	"NAME" => GetMessage("PC_PAR_FULLSCREEN"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
	"HIDDEN" => $hidden,
);

if ($type != 'wmv')
{
	$basePath = "/bitrix/components/bitrix/player/mediaplayer/skins";
	$arParams["SKIN_PATH"] = Array(
		"PARENT" => "APPEARANCE_FLV",
		"NAME" => GetMessage("PC_PAR_SKIN_PATH"),
		"TYPE" => "FILE",
		"FD_TARGET" => "D",
		"FD_UPLOAD" => false,
		"DEFAULT" => $basePath,
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);
	$arSkins = getSkins($arCurrentValues['SKIN_PATH'] ? $arCurrentValues['SKIN_PATH'] : $basePath);
	$defSkin = isset($arSkins['bitrix.swf']) ? 'bitrix.swf' : 'default';

	if (count($arSkins) > 0)
	{
		$arParams["SKIN"] = Array(
			"PARENT" => "APPEARANCE_FLV",
			"NAME" => GetMessage("PC_PAR_SKIN"),
			"TYPE" => "LIST",
			"VALUES" => $arSkins,
			"DEFAULT" => $defSkin,
			"HIDDEN" => $hidden,
		);
	}
	$arParams["CONTROLBAR"] = Array(
		"PARENT" => "APPEARANCE_FLV",
		"NAME" => GetMessage("PC_PAR_CONTROLS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
			'over' => GetMessage("PC_PAR_CONTROLS_OVER"),
			'none' => GetMessage("PC_PAR_CONTROLS_NONE")
		),
		"DEFAULT" => "bottom",
		"HIDDEN" => $hidden,
	);
	$arParams["WMODE"] = Array(
		"PARENT" => "APPEARANCE_FLV",
		"NAME" => GetMessage("PC_PAR_WMODE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'window' => GetMessage("PC_PAR_WMODE_WINDOW"),
			'opaque' => GetMessage("PC_PAR_WMODE_OPAQUE"),
			'transparent' => GetMessage("PC_PAR_WMODE_TRANSPARENT")
		),
		"DEFAULT" => "transparent",
		"HIDDEN" => $hidden,
	);
	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["PLAYLIST"] = Array(
			"PARENT" => "APPEARANCE_FLV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
				//'over' => GetMessage("PC_PAR_CONTROLS_OVER"),
				'right' => GetMessage("PC_PAR_PLAYLIST_RIGHT"),
				'none' => GetMessage("PC_PAR_CONTROLS_NONE")
			),
			"DEFAULT" => "none",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_SIZE"] = Array(
			"PARENT" => "APPEARANCE_FLV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_SIZE"),
			"COLS" => 10,
			"DEFAULT" => "180",
			"HIDDEN" => $hidden,
		);
	}
	$arParams["HIDE_MENU"] = Array(
		"PARENT" => "APPEARANCE_FLV",
		"NAME" => GetMessage("PC_PAR_HIDE_MENU"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);
}

if ($type != 'flv')
{
	$arParams["SHOW_CONTROLS"] = Array(
		"PARENT" => "APPEARANCE_WMV",
		"NAME" => GetMessage("PC_PAR_SHOW_CONTROLS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"REFRESH" => "Y",
		"HIDDEN" => $hidden,
	);
	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["PLAYLIST"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'bottom' => GetMessage("PC_PAR_CONTROLS_BOTTOM"),
				'right' => GetMessage("PC_PAR_PLAYLIST_RIGHT")
			),
			"DEFAULT" => "right",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_TYPE"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'asx' => 'ASX',
				'atom' => 'ATOM',
				'rss' => 'RSS',
				'xspf' => 'XSPF'
			),
			"DEFAULT" => "xspf",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_SIZE"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_SIZE"),
			"COLS" => 10,
			"DEFAULT" => "180",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_PREVIEW_WIDTH"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_PREVIEW_WIDTH"),
			"COLS" => 4,
			"DEFAULT" => "64",
			"HIDDEN" => $hidden,
		);
		$arParams["PLAYLIST_PREVIEW_HEIGHT"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_PLAYLIST_PREVIEW_HEIGHT"),
			"COLS" => 4,
			"DEFAULT" => "48",
			"HIDDEN" => $hidden,
		);
	}
	if ($arCurrentValues['SHOW_CONTROLS'] != 'N')
	{
		$arParams["SHOW_STOP"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_SHOW_STOP"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => $hidden,
		);
		$arParams["SHOW_DIGITS"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_SHOW_DIGITS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_BGCOLOR"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_BGCOLOR"),
			"COLS" => 10,
			"DEFAULT" => "FFFFFF",
			//"TYPE" => "COLORPICKER",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_COLOR"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
		$arParams["CONTROLS_OVER_COLOR"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_OVER_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
		$arParams["SCREEN_COLOR"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_SCREEN_COLOR"),
			"COLS" => 10,
			"DEFAULT" => "000000",
			"HIDDEN" => $hidden,
		);
		$arParams["WMODE_WMV"] = Array(
			"PARENT" => "APPEARANCE_WMV",
			"NAME" => GetMessage("PC_PAR_WMODE_WMV"),
			"TYPE" => "LIST",
			"VALUES" => array(
				'window' => GetMessage("PC_PAR_WMODE_WINDOW"),
				'windowless' => GetMessage("PC_PAR_WMODE_TRANSPARENT")
			),
			"DEFAULT" => "window",
			"HIDDEN" => $hidden,
		);
	}
	//$arParams["SHOWICONS"] = Array(
	//	"PARENT" => "APPEARANCE_WMV",
	//	"NAME" => GetMessage("PC_PAR_SHOWICONS"),
	//	"TYPE" => "CHECKBOX",
	//	"DEFAULT" => "Y",
	//	"HIDDEN" => $hidden,
	//);
	//overstretch (false): Sets how to stretch images/movies to make them fit the display. The default stretches to fit the display. Set this to true to stretch them proportionally to fill the display, fit to stretch them disproportionally and none to keep original dimensions.
	//$arParams["KEEP_PROPORTION"] = Array(
	//	"PARENT" => "APPEARANCE_WMV",
	//	"NAME" => "overstretch",
	//	"TYPE" => "CHECKBOX",
	//	"DEFAULT" => "Y",
	//	"HIDDEN" => $hidden,
	//);
	//showdownload (false): Set this to true to show a button in the player controlbar which links to the link flashvar.
}

// PLAYBACK
$playback_parent = $type == 'flv' ? 'PLAYBACK_FLV' : 'PLAYBACK';
$arParams["AUTOSTART"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_AUTOSTART"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);
$arParams["REPEAT"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_REPEAT"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N"
);
$arParams["VOLUME"] = Array(
	"PARENT" => $playback_parent,
	"NAME" => GetMessage("PC_PAR_VOLUME"),
	"COLS" => 10,
	"DEFAULT" => "90"
);

if ($type != 'wmv')
{
	$arParams["DISPLAY_CLICK"] = Array(
		"PARENT" => "PLAYBACK_FLV",
		"NAME" => GetMessage("PC_PAR_DISPLAY_CLICK"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'play' => GetMessage("PC_PAR_DISPLAY_CLICK_PLAY"),
			'link' => GetMessage("PC_PAR_DISPLAY_CLICK_LINK"),
			'fullscreen' => GetMessage("PC_PAR_DISPLAY_CLICK_FULLSCREEN"),
			'none' => GetMessage("PC_PAR_DISPLAY_CLICK_NONE"),
			'mute' => GetMessage("PC_PAR_DISPLAY_CLICK_MUTE"),
			'next' => GetMessage("PC_PAR_DISPLAY_CLICK_NEXT"),
		),
		"DEFAULT" => 'play',
		"HIDDEN" => $hidden,
	);
	$arParams["MUTE"] = Array(
		"PARENT" => "PLAYBACK_FLV",
		"NAME" => GetMessage("PC_PAR_MUTE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);
	$arParams["HIGH_QUALITY"] = Array(
		"PARENT" => "PLAYBACK_FLV",
		"NAME" => GetMessage("PC_PAR_HQ"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y",
		"HIDDEN" => $hidden,
	);

	if ($arCurrentValues['USE_PLAYLIST'] == 'Y')
	{
		$arParams["SHUFFLE"] = Array(
			"PARENT" => "PLAYBACK_FLV",
			"NAME" => GetMessage("PC_PAR_SHUFFLE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"HIDDEN" => $hidden,
		);
		$arParams["START_ITEM"] = Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("PC_PAR_START_FROM"),
			"TYPE" => "STRING",
			"DEFAULT" => "0",
			"HIDDEN" => $hidden,
		);
	}
}

//ADDITIONAL_SETTINGS
$arParams["ADVANCED_MODE_SETTINGS"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_ADVANCED_MODE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"REFRESH" => "Y",
);

$arParams["PLAYER_ID"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_PLAYER_ID"),
	"DEFAULT" => "",
	"HIDDEN" => $hidden,
);

$arParams["BUFFER_LENGTH"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_BUFFER_LENGTH"),
	"COLS" => "10",
	"DEFAULT" => "10",
	"HIDDEN" => $hidden,
);
//stretching (uniform): defines how to resize images in the display. Can be none (no stretching), exactfit (disproportionate), uniform (stretch with black borders) or fill (uniform, but completely fill the display).
/*$arParams["STRETCHING"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => 'stretching',
	"TYPE" => "LIST",
	"VALUES" => array(
		'none' => 'none',
		'exactfit' => 'exactfit',
		'uniform' => 'Uniform',
		'fill' => 'Fill',
	),
	"DEFAULT" => 'uniform',
);
*/

$arParams["DOWNLOAD_LINK"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_DOWNLOAD_LINK"),
	"COLS" => "40",
	"DEFAULT" => "",
	"HIDDEN" => $hidden,
);
$arParams["DOWNLOAD_LINK_TARGET"] = Array(
	"PARENT" => "ADDITIONAL_SETTINGS",
	"NAME" => GetMessage("PC_PAR_LINK_TARGET"),
	"TYPE" => "LIST",
	"VALUES" => array(
		'_self' => GetMessage("PC_PAR_LINK_TARGET_SELF"),
		'_blank' => GetMessage("PC_PAR_LINK_TARGET_BLANK")
	),
	"DEFAULT" => '_self',
	"HIDDEN" => $hidden,
);

if ($type != 'wmv')
{
	$arParams["ALLOW_SWF"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_ALLOW_SWF"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"HIDDEN" => $hidden,
	);

	$arParams["CONTENT_TYPE"] = Array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("PC_PAR_CONTENT_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			'' => GetMessage("PC_PAR_CON_TYPE_ALL"),
			'video' => GetMessage("PC_PAR_CON_TYPE_VIDEO"),
			'youtube' => GetMessage("PC_PAR_CON_TYPE_YOUTUBE"),
			'sound' => GetMessage("PC_PAR_CON_TYPE_SOUND"),
			'image' => GetMessage("PC_PAR_CON_TYPE_IMAGE"),
			//'http' => GetMessage("PC_PAR_CON_TYPE_HTTP"),
		),
		"DEFAULT" => "",
		"HIDDEN" => $hidden,
	);
}

$arComponentParameters["PARAMETERS"] = $arParams;
?>
