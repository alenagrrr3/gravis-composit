<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
// Template params
/********************************************************************
				Input params
********************************************************************/
/***************** URL *********************************************/
	$res = $arResult;
	$URL_NAME_DEFAULT = array(
			"user_list" => "PAGE_NAME=user_list");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (strLen(trim($res["URL_TEMPLATES_".strToUpper($URL)])) <= 0)
			$res["URL_TEMPLATES_".strToUpper($URL)] = $GLOBALS["APPLICATION"]->GetCurPage()."?".$URL_VALUE;
		$res["~URL_TEMPLATES_".strToUpper($URL)] = $res["URL_TEMPLATES_".strToUpper($URL)];
		$res["URL_TEMPLATES_".strToUpper($URL)] = htmlspecialchars($res["~URL_TEMPLATES_".strToUpper($URL)]);
	}
/***************** ADDITIONAL **************************************/
/********************************************************************
				/Input params
********************************************************************/
if ($this->__page !== "menu"):
	$oTemplate = $this;
	$this->__component->IncludeComponentTemplate("menu");
	$this->__component->__template = $oTemplate;
	
	if (in_array(strToLower($this->__page), array("profile", "profile_view", "subscr_list", "user_post"))):
		$GLOBALS["APPLICATION"]->AddChainItem(GetMessage("F_USERS"), CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_USER_LIST"], array()));
	endif;
else:
	return true;
endif;
/********************************************************************
				Input params
********************************************************************/
$arThemes = array();
$sTemplateDirFull = preg_replace("'[\\\\/]+'", "/", dirname(realpath(__FILE__))."/");
$dir = $sTemplateDirFull."themes/";
if (is_dir($dir) && $directory = opendir($dir)):
	
	while (($file = readdir($directory)) !== false)
	{
		if ($file != "." && $file != ".." && is_dir($dir.$file))
			$arThemes[] = $file;
	}
	closedir($directory);
endif;
$sTemplateDir = $this->__component->__template->__folder;
$sTemplateDir = preg_replace("'[\\\\/]+'", "/", $sTemplateDir."/");

$arParams["SHOW_AUTH_FORM"] = ($arParams["SHOW_AUTH_FORM"] == "N" ? "N" : "Y");
$arParams["SHOW_NAVIGATION"] = ($arParams["SHOW_NAVIGATION"] == "N" ? "N" : "Y");
$arParams["SHOW_SUBSCRIBE_LINK"] = ($arParams["SHOW_SUBSCRIBE_LINK"] == "Y" ? "Y" : "N");
$arParams["TMPLT_SHOW_ADDITIONAL_MARKER"] = trim($arParams["TMPLT_SHOW_ADDITIONAL_MARKER"]);

$arParams["WORD_LENGTH"] = intVal($arParams["WORD_LENGTH"]);
$arParams["WORD_WRAP_CUT"] = intVal($arParams["WORD_WRAP_CUT"]);
$arParams["PATH_TO_SMILE"] = (empty($arParams["PATH_TO_SMILE"]) ? "/bitrix/images/forum/smile/" : $arParams["PATH_TO_SMILE"]);
$arParams["PATH_TO_ICON"] = (empty($arParams["PATH_TO_ICON"]) ? "/bitrix/images/forum/icon/" : $arParams["PATH_TO_ICON"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = trim($arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_TEMPLATE"] = (empty($arParams["PAGE_NAVIGATION_TEMPLATE"]) ? "forum" : $arParams["PAGE_NAVIGATION_TEMPLATE"]);
$arParams["PAGE_NAVIGATION_WINDOW"] = intVal(intVal($arParams["PAGE_NAVIGATION_WINDOW"]) > 0 ? $arParams["PAGE_NAVIGATION_WINDOW"] : 5);
$arParams["THEME"] = trim($arParams["THEME"]);
$arParams["THEME"] = (in_array($arParams["THEME"], $arThemes) ? $arParams["THEME"] : (in_array("blue", $arThemes) ? "blue" : $arThemes[0]));
/********************************************************************
				/Input params
********************************************************************/
if (!empty($arParams["THEME"])):
	$date = filemtime($dir.$arParams["THEME"]."/style.css");
	$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'themes/'.$arParams["THEME"].'/style.css?'.$date);
endif;
$date = @filemtime($sTemplateDirFull."styles/additional.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS($sTemplateDir.'styles/additional.css?'.$date);

$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);

$file = trim(preg_replace("'[\\\\/]+'", "/", (dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php")));
if(file_exists($file)):
	global $MESS;
	include_once($file);
endif;

?><script type="text/javascript">
//<![CDATA[
	if (phpVars == null || typeof(phpVars) != "object")
	{
		var phpVars = {
			'ADMIN_THEME_ID': '.default',
			'titlePrefix': '<?=CUtil::addslashes(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - '};
	}
	if (typeof oText != "object")
	{
		var oText = {};
	}
	oText['wait_window'] = '<?=GetMessage("F_LOAD")?>';
//]]>
window.oForumForm = {};
</script>