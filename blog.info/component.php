<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["CATEGORY_ID"] = (IntVal($arParams["CATEGORY_ID"])>0 ? IntVal($arParams["CATEGORY_ID"]) : false);
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
	
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");



if(strlen($arParams["BLOG_URL"])>0)
{
	$cache = new CPHPCache;
	$cache_id = "blog_blog_category"."_".$arParams["CATEGORY_ID"];
	$cache_path = "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/category/";

	if ($arParams["CACHE_TIME"] > 0 && $cache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
	{
		$Vars = $cache->GetVars();
		CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
		$arResult = $Vars["arResult"];
		$cache->Output();
	}
	else
	{
		if ($arParams["CACHE_TIME"] > 0)
			$cache->StartDataCache($arParams["CACHE_TIME"], $cache_id, $cache_path);
		
		if($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
		{
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$arBlog["Group"] = $arGroup;
					$arResult = $arBlog;
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
					
					$arResult["arUserBlog"] = CBlogUser::GetByID($arBlog["OWNER_ID"], BLOG_BY_USER_ID);
					$arResult["arUserBlog"] = CBlogTools::htmlspecialcharsExArray($arResult["arUserBlog"]);
					$dbUser = CUser::GetByID($arBlog["OWNER_ID"]);
					$arResult["arUser"] = $dbUser->GetNext();
					$arResult["AuthorName"] = CBlogUser::GetUserName($arResult["arUserBlog"]["ALIAS"], $arResult["arUser"]["NAME"], $arResult["arUser"]["LAST_NAME"], $arResult["arUser"]["LOGIN"]);
					$arResult["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arBlog["OWNER_ID"]));
					$arResult["Avatar"] = CFile::GetFileArray($arResult["arUserBlog"]["AVATAR"]);
					if (!empty($arResult["Avatar"]))
						$arResult["Avatar_FORMATED"] = CFile::ShowImage($arResult["Avatar"]["SRC"], 100, 100, 'title="'.$arResult["AuthorName"].'" border="0"');

					$arCategoryAll = Array();
					$dbCategory = CBlogCategory::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$arBlog["ID"]));
					while($arCategory = $dbCategory->GetNext())
					{
						if($arParams["CATEGORY_ID"] == $arCategory["ID"])
								$arCategory["SELECTED"] = "Y";
						$arCategory["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $arCategory["ID"]));
						$arCategoryAll[] = $arCategory;
					}
					$arResult["CATEGORY"] = $arCategoryAll;
					
					$arResult["BLOG_PROPERTIES"] = array("SHOW" => "N");

					if (!empty($arParams["BLOG_PROPERTY_LIST"]))
					{
						$arBlogFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", $arBlog["ID"], LANGUAGE_ID);

						if (count($arParams["BLOG_PROPERTY_LIST"]) > 0)
						{
							foreach ($arBlogFields as $FIELD_NAME => $arBlogField)
							{
								if (!in_array($FIELD_NAME, $arParams["BLOG_PROPERTY_LIST"]))
									continue;
								$arBlogField["EDIT_FORM_LABEL"] = strLen($arBlogField["EDIT_FORM_LABEL"]) > 0 ? $arBlogField["EDIT_FORM_LABEL"] : $arBlogField["FIELD_NAME"];
								$arBlogField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arBlogField["EDIT_FORM_LABEL"]);
								$arBlogField["~EDIT_FORM_LABEL"] = $arBlogField["EDIT_FORM_LABEL"];
								$arResult["BLOG_PROPERTIES"]["DATA"][$FIELD_NAME] = $arBlogField;
							}
						}
						if (!empty($arResult["BLOG_PROPERTIES"]["DATA"]))
							$arResult["BLOG_PROPERTIES"]["SHOW"] = "Y";
					}
				}
			}
		}

		if ($arParams["CACHE_TIME"] > 0)
			$cache->EndDataCache(array("templateCachedData"=>$this->GetTemplateCachedData(), "arResult"=>$arResult));
	}
	$this->IncludeComponentTemplate();
}	
?>