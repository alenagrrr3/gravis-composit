<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_COUNT"] = IntVal($arParams["BLOG_COUNT"])>0 ? IntVal($arParams["BLOG_COUNT"]): 20;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "LAST_POST_DATE");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["ID"] = intval($arParams["ID"]);
$arParams["SHOW_BLOG_WITHOUT_POSTS"] = ($arParams["SHOW_BLOG_WITHOUT_POSTS"] == "Y")? "Y" : "N";
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
$arFilter = Array("SITE_ID"=>SITE_ID, "GROUP_ID"=>$arParams["ID"], "ACTIVE"=>"Y");
$arSelectFields = Array("ID", "NAME", "DESCRIPTION", "URL", "SITE_ID", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "OWNER_LOGIN", "OWNER_NAME", "OWNER_LAST_NAME", "LAST_POST_DATE", "LAST_POST_ID", "BLOG_USER_AVATAR", "BLOG_USER_ALIAS", "SOCNET_GROUP_ID");

CpageOption::SetOptionString("main", "nav_page_in_session", "N");

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if($arGroup = CBlogGroup::GetByID($arParams["ID"]))
{
	$arGroup = CBlogTools::htmlspecialcharsExArray($arGroup);
	$arResult["GROUP"] = $arGroup;
	if($arParams["SET_TITLE"]=="Y")
		$APPLICATION->SetTitle($arGroup["NAME"]);
	
	$cache = new CPHPCache;
	$cache_id = "blog_groups_".serialize($arParams)."_".CDBResult::NavStringForCache($arParams["BLOG_COUNT"]);
			
	$cache_path = "/".SITE_ID."/blog/groups/".$arParams["ID"]."/";

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

		$dbBlog = CBlog::GetList(
				$SORT,
				$arFilter,
				false,
				array("nPageSize"=>$arParams["BLOG_COUNT"], "bShowAll" => false),
				$arSelectFields
			);
		$arResult["NAV_STRING"] = $dbBlog->GetPageNavString(GetMessage("B_B_GR_TITLE"), $arParams["NAV_TEMPLATE"]);
		$arResult["BLOG"] = Array();
		while($arBlog = $dbBlog->GetNext())
		{
			if(IntVal($arBlog["SOCNET_GROUP_ID"]) > 0)
			{
				$arBlog["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arBlog["LAST_POST_ID"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
				$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "group_id" => $arBlog["SOCNET_GROUP_ID"]));
			}
			else
			{
				$arBlog["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arBlog["LAST_POST_ID"], "user_id" => $arBlog["OWNER_ID"]));
				$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"]));
			}
			
			//$arBlog["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
			//$arBlog["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arBlog["LAST_POST_ID"]));
			if(IntVal($arBlog["OWNER_ID"]) > 0)
			{
				$arBlog["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
				$arBlog["AuthorName"] = CBlogUser::GetUserName($arBlog["BLOG_USER_ALIAS"], $arBlog["OWNER_NAME"], $arBlog["OWNER_LAST_NAME"], $arBlog["OWNER_LOGIN"]);
				$arBlog["BLOG_USER_AVATAR_ARRAY"] = CFile::GetFileArray($arBlog["BLOG_USER_AVATAR"]);
				if ($arBlog["BLOG_USER_AVATAR_ARRAY"] !== false)
					$arBlog["BLOG_USER_AVATAR_IMG"] = CFile::ShowImage($arBlog["BLOG_USER_AVATAR_ARRAY"]["SRC"], 100, 100, 'align="right"'); 
			}
			$arBlog["LAST_POST_DATE_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arBlog["LAST_POST_DATE"], CSite::GetDateFormat("FULL")));
			$arResult["BLOG"][] = $arBlog;
		}
			
		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
	}
}
else
	$arResult["FATAL_ERROR"] = GetMessage("B_B_GR_NO_GROUP");
$this->IncludeComponentTemplate();
?>