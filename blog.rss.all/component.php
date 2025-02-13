<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 10;
$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

if (strtolower($arParams["TYPE"]) == "rss1")
	$arResult["TYPE"] = "RSS .92";
if (strtolower($arParams["TYPE"]) == "rss2")
	$arResult["TYPE"] = "RSS 2.0";
if (strtolower($arParams["TYPE"]) == "atom")
	$arResult["TYPE"] = "Atom .03";

if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$cache = new CPHPCache; 
$cache_id = "blog_rss_all_out_".$arParams["GROUP_ID"]."_".$arParams["MESSAGE_COUNT"]."_".$arResult["TYPE"]."_".IntVal($USER->GetID())."_".$arParams["PATH_TO_POST"]."_".$arParams["PATH_TO_USER"];
$cache_path = "/".SITE_ID."/blog/rss_all/".strtolower($arResult["TYPE"])."/".$arParams["GROUP_ID"]."/";

$APPLICATION->RestartBuffer();
header("Content-Type: text/xml");
header("Pragma: no-cache");

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$cache->Output();
}
else
{
	if ($textRSS = CBlog::BuildRSSAll($arParams["GROUP_ID"], $arResult["TYPE"], $arParams["MESSAGE_COUNT"], SITE_ID, $arParams["PATH_TO_POST"], $arParams["PATH_TO_USER"]))
	{
		if ($arParams["CACHE_TIME"] > 0)
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

		echo $textRSS;

		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array());
	}
}
die();
?>