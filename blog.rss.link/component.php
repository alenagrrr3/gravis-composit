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

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["RSS1"] = ($arParams["RSS1"] != "N") ? "Y" : "N";
$arParams["RSS2"] = ($arParams["RSS2"] != "N") ? "Y" : "N";
$arParams["ATOM"] = ($arParams["ATOM"] != "N") ? "Y" : "N";
$arParams["MODE"] = trim($arParams["MODE"]);
$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["GROUP_VAR"])<=0)
	$arParams["GROUP_VAR"] = "id";

$arParams["PATH_TO_RSS"] = trim($arParams["PATH_TO_RSS"]);
if(strlen($arParams["PATH_TO_RSS"])<=0)
	$arParams["PATH_TO_RSS"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=rss&".$arParams["BLOG_VAR"]."=#blog#"."&type=#type#");
$arParams["PATH_TO_RSS_ALL"] = trim($arParams["PATH_TO_RSS_ALL"]);
if(strlen($arParams["PATH_TO_RSS_ALL"])<=0)
	$arParams["PATH_TO_RSS_ALL"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=rss_all&type=#type#&".$arParams["GROUP_VAR"]."=#group_id#");

if($arParams["MODE"] == "S")
{
		if($arParams["RSS1"] == "Y")
			$arResult[] = Array(
					"type" => "rss1", 
					"name" => GetMessage("BRL_S")."RSS .92", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss1", "group_id" => ""))
				);
		if($arParams["RSS2"] == "Y")
			$arResult[] = Array(
					"type" => "rss2", 
					"name" => GetMessage("BRL_S")."RSS 2.0", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss2", "group_id" => ""))
				);
		if($arParams["ATOM"] == "Y")
			$arResult[] = Array(
					"type" => "atom", 
					"name" => GetMessage("BRL_S")."Atom .3", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "atom", "group_id" => ""))
				);
			
		$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss2", "group_id" => "")).'" />');
}
elseif($arParams["MODE"] == "G")
{
		if($arParams["RSS1"] == "Y")
			$arResult[] = Array(
					"type" => "rss1", 
					"name" => GetMessage("BRL_G")."RSS .92", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss1", "group_id" => $arParams["GROUP_ID"]))
				);
		if($arParams["RSS2"] == "Y")
			$arResult[] = Array(
					"type" => "rss2", 
					"name" => GetMessage("BRL_G")."RSS 2.0", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss2", "group_id" => $arParams["GROUP_ID"]))
				);
		if($arParams["ATOM"] == "Y")
			$arResult[] = Array(
					"type" => "atom", 
					"name" => GetMessage("BRL_G")."Atom .3", 
					"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "atom", "group_id" => $arParams["GROUP_ID"]))
				);
			
		$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_ALL"], array("type" => "rss1", "group_id" => $arParams["GROUP_ID"])).'" />');
}
else
{
	if($bSoNet)
	{
		$blogOwnerID = $arParams["USER_ID"];
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

	if(!empty($arBlog) && $arBlog["ACTIVE"] == "Y")
	{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
				if($arParams["RSS1"] == "Y")
					$arResult[] = Array(
							"type" => "rss1", 
							"name" => GetMessage("BRL_B")."RSS .92", 
							"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS"], array("blog" => $arBlog["URL"], "type"=>"rss1", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]))
						);
				if($arParams["RSS2"] == "Y")
					$arResult[] = Array(
							"type" => "rss2", 
							"name" => GetMessage("BRL_B")."RSS 2.0", 
							"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS"], array("blog" => $arBlog["URL"], "type"=>"rss2", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]))
						);
				if($arParams["ATOM"] == "Y")
					$arResult[] = Array(
							"type" => "atom", 
							"name" => GetMessage("BRL_B")."Atom .3", 
							"url" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS"], array("blog" => $arBlog["URL"], "type"=>"atom", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]))
						);
				$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS"], array("blog" => $arBlog["URL"], "type"=>"rss2", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])).'" />');
			}
	}
}

$this->IncludeComponentTemplate();
?>