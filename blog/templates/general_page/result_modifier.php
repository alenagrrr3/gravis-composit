<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arThemes = array();
$dir = trim(preg_replace("'[\\\\/]+'", "/", dirname(__FILE__)."/themes/"));
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;

$arParams["THEME"] = trim($arParams["THEME"]);
$arParams["THEME"] = (in_array($arParams["THEME"], $arThemes) ? $arParams["THEME"] : (in_array("blue", $arThemes) ? "blue" : $arThemes[0]));

$arParams["NAV_TEMPLATE"] = trim($arParams["NAV_TEMPLATE"]);
$arParams["NAV_TEMPLATE"] = (empty($arParams["NAV_TEMPLATE"]) ? "blog" : $arParams["NAV_TEMPLATE"]);


if (!empty($arParams["THEME"])):
{
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/'.$arParams["THEME"].'/style.css');
	if($arParams["THEME"] == "blue")
	{
		$arParams["COLOR_OLD"] = "7fa5ca";
		$arParams["COLOR_NEW"] = "0e5196";
	}
	elseif($arParams["THEME"] == "green")
	{
		$arParams["COLOR_OLD"] = "8dac8a";
		$arParams["COLOR_NEW"] = "33882a";
	}
	elseif($arParams["THEME"] == "orange")
	{
		$arParams["COLOR_OLD"] = "7fa5ca";
		$arParams["COLOR_NEW"] = "006bcf";
	}
	elseif($arParams["THEME"] == "red")
	{
		$arParams["COLOR_OLD"] = "e59494";
		$arParams["COLOR_NEW"] = "d52020";
	}
	elseif($arParams["THEME"] == "red2")
	{
		$arParams["COLOR_OLD"] = "92a6bb";
		$arParams["COLOR_NEW"] = "346ba4";
	}

}
endif;
