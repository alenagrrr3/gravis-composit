<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["BLOG_COUNT"] = intval($arParams["BLOG_COUNT"]);
if(Intval($arParams["BLOG_COUNT"])<=0)
	$arParams["BLOG_COUNT"] = 6;

$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_CREATE");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["SHOW_DESCRIPTION"] = ($arParams["SHOW_DESCRIPTION"]=="N") ? "N" : "Y";
$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

$arParams["BLOG_VAR"] = trim($arParams["BLOG_VAR"]);
$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
$arParams["USER_VAR"] = trim($arParams["USER_VAR"]);

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);

$cache = new CPHPCache;
$cache_id = "blog_new_blogs_".serialize($arParams);
$cache_path = "/".SITE_ID."/blog/new_blogs/";

if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$Vars = $cache->GetVars();
	foreach($Vars["arResult"] as $k=>$v)
		$arResult[$k] = $v;
	CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
	$cache->Output();
}
else
{
	if ($arParams["CACHE_TIME"] > 0)
		$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);

	$arFilter = Array(
				"ACTIVE" => "Y",
				"GROUP_SITE_ID"=>SITE_ID
			);	
	if(IntVal($arParams["GROUP_ID"]) > 0)
		$arFilter["GROUP_ID"] = $arParams["GROUP_ID"];

	if(CModule::IncludeModule("socialnetwork")  && (IntVal($arParams["SOCNET_GROUP_ID"]) > 0 || IntVal($arParams["USER_ID"]) > 0 || IntVal($arParams["GROUP_ID"]) > 0))
	{
		unset($arFilter[">PERMS"]);
		unset($arFilter["BLOG_GROUP_ID"]);
		$cacheSoNet = new CPHPCache;
		$cache_idSoNet = "blog_sonet_".SITE_ID;
		$cache_pathSoNet = "/".SITE_ID."/blog/sonet/";

		if ($arParams["CACHE_TIME"] > 0 && $cacheSoNet->InitCache($arParams["CACHE_TIME"], $cache_idSoNet, $cache_pathSoNet))
		{
			$Vars = $cacheSoNet->GetVars();
			$arAvBlog = $Vars["arAvBlog"];
			CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
			$cacheSoNet->Output();
		}
		else
		{
			if ($arParams["CACHE_TIME"] > 0)
				$cacheSoNet->StartDataCache($arParams["CACHE_TIME"], $cache_idSoNet, $cache_pathSoNet);

			$arAvBlog = Array();
			
			$arFilterTmp = Array("ACTIVE" => "Y", "GROUP_SITE_ID" => SITE_ID);
			
			if(IntVal($arParams["GROUP_ID"]) > 0)
				$arFilterTmp["GROUP_ID"] = $arParams["GROUP_ID"];

			$dbBlog = CBlog::GetList(Array(), $arFilterTmp);
			while($arBlog = $dbBlog->Fetch())
			{
				if(IntVal($arBlog["SOCNET_GROUP_ID"]) > 0)
				{
					$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arBlog["SOCNET_GROUP_ID"], "blog", "view_post");
					if ($featureOperationPerms == SONET_ROLES_ALL)
						$arAvBlog[] = $arBlog["ID"];
				}
				else
				{
					$featureOperationPerms = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_USER, $arBlog["OWNER_ID"], "blog", "view_post");
					if ($featureOperationPerms == SONET_RELATIONS_TYPE_ALL)
						$arAvBlog[] = $arBlog["ID"];
				}
			}
			if ($arParams["CACHE_TIME"] > 0)
				$cacheSoNet->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arAvBlog" => $arAvBlog));
		}
		$arFilter["ID"] = $arAvBlog;
	}

	$arSelectedFields = array("ID", "NAME", "DESCRIPTION", "URL", "OWNER_ID", "OWNER_NAME", "OWNER_LAST_NAME", "OWNER_LOGIN", "BLOG_USER_ALIAS", "GROUP_ID", "SOCNET_GROUP_ID");
	
	if($arParams["BLOG_COUNT"]>0)
		$COUNT = Array("nTopCount" => $arParams["BLOG_COUNT"]);
	else
		$COUNT = false;

	$arResult = Array();
	$dbBlogs = CBlog::GetList(
		$SORT,
		$arFilter,
		false,
		$COUNT,
		$arSelectedFields
	);
	$i=0;
	while ($arBlog = $dbBlogs->GetNext())
	{
		$arBlog["AuthorName"] = CBlogUser::GetUserName($arBlog["BLOG_USER_ALIAS"], $arBlog["OWNER_NAME"], $arBlog["OWNER_LAST_NAME"], $arBlog["OWNER_LOGIN"]);
	
		if(IntVal($arBlog["SOCNET_GROUP_ID"]) > 0)
		{
			$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
			$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arBlog["SOCNET_GROUP_ID"]));
		}
		else
		{
			$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
			$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
		}
		$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
		
		if($i==0)
			$arBlog["FIRST_BLOG"] = "Y";
		$i++;
		$arResult[] = $arBlog;
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
}
$this->IncludeComponentTemplate();
?>
