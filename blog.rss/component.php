<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);

$bSoNet = false;
$bGroupMode = false;
if (CModule::IncludeModule("socialnetwork") && (IntVal($arParams["SOCNET_GROUP_ID"]) > 0 || IntVal($arParams["USER_ID"]) > 0))
{
	$bSoNet = true;

	if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
		$bGroupMode = true;
	
	if($bGroupMode)
	{
		if(!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog"))
		{
			return;
		}
	}
	else
	{
		if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		{
			return;
		}
	}
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 10;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
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

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";

$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

if($bSoNet)
{
	$arFilterblg = Array(
		        "ACTIVE" => "Y",
			"GROUP_ID" => $arParams["GROUP_ID"],
			"GROUP_SITE_ID" => SITE_ID,
			);
		if($bGroupMode)
			$arFilterblg["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		else
			$arFilterblg["OWNER_ID"] = $arParams["USER_ID"];
		$dbBl = CBlog::GetList(Array(), $arFilterblg);
		$arBlog = $dbBl ->Fetch();
}
else
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
}

	
$cache = new CPHPCache; 
$cache_id = "blog_rss_out_".serialize($arParams);
$cache_path = "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/".strtolower($arResult["TYPE"])."/";

$APPLICATION->RestartBuffer();
header("Content-Type: text/xml");
header("Pragma: no-cache");

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$cache->Output();
}
else
{
	if (!empty($arBlog))
	{
		if ($textRSS = CBlog::BuildRSS($arBlog["ID"], $arResult["TYPE"], $arParams["MESSAGE_COUNT"], $arParams["PATH_TO_BLOG"], $arParams["PATH_TO_POST"], $arParams["PATH_TO_USER"], $bSoNet))
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

			echo $textRSS;

			if ($arParams["CACHE_TIME"] > 0)
				$cache->EndDataCache(array());
		}
	}
}
die();
?>