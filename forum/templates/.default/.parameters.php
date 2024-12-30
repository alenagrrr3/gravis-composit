<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
/********************************************************************
				Input params
********************************************************************/
$arThemesMessages = array(
	"beige" => GetMessage("F_THEME_BEIGE"), 
	"blue" => GetMessage("F_THEME_BLUE"), 
	"fluxbb" => GetMessage("F_THEME_FLUXBB"), 
	"gray" => GetMessage("F_THEME_GRAY"), 
	"green" => GetMessage("F_THEME_GREEN"), 
	"orange" => GetMessage("F_THEME_ORANGE"), 
	"red" => GetMessage("F_THEME_RED"), 
	"white" => GetMessage("F_THEME_WHITE"));
$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[$file] = (!empty($arThemesMessages[$file]) ? $arThemesMessages[$file] : strtoupper(substr($file, 0, 1)).strtolower(substr($file, 1)));
	}
	closedir($directory);
endif;
/********************************************************************
				/Input params
********************************************************************/

$arTemplateParameters = array(
	"THEME" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_THEMES"),
		"TYPE" => "LIST",
		"VALUES" => $arThemes,
		"MULTIPLE" => "N",
		"DEFAULT" => "blue"),
    "SHOW_TAGS" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_TAGS"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_AUTH_FORM" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_AUTH"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_NAVIGATION" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_NAVIGATION"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_SUBSCRIBE_LINK" => array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
        "NAME" => GetMessage("F_SHOW_SUBSCRIBE_LINK"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N"),
	"TMPLT_SHOW_ADDITIONAL_MARKER" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_SHOW_ADDITIONAL_MARKER"),
		"TYPE" => "STRING",
		"DEFAULT" => "(new)"), 
	"PATH_TO_SMILE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/smile/"),
	"PATH_TO_ICON" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_DEFAULT_PATH_TO_ICON"),
		"TYPE" => "STRING",
		"DEFAULT" => "/bitrix/images/forum/icon/"),
	"PAGE_NAVIGATION_TEMPLATE" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => "forum"),
	"PAGE_NAVIGATION_WINDOW" => Array(
		"PARENT" => "TEMPLATE_TEMPLATES_SETTINGS",
		"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
		"TYPE" => "STRING",
		"DEFAULT" => "5"), 
	"WORD_WRAP_CUT" => CForumParameters::GetWordWrapCut(false, "TEMPLATE_TEMPLATES_SETTINGS"),
	"WORD_LENGTH" => CForumParameters::GetWordLength(false, "TEMPLATE_TEMPLATES_SETTINGS"),
);
?>