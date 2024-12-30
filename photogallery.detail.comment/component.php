<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("photogallery"))
{
	ShowError(GetMessage("P_MODULE_IS_NOT_INSTALLED"));
	return 0;
}
/********************************************************************
				Input params
********************************************************************/
//***************** BASE *******************************************/
	$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
	$arParams["IBLOCK_ID"] = intval($arParams["IBLOCK_ID"]);
	$arParams["SECTION_ID"] = intval($arParams["SECTION_ID"]);
	$arParams["BEHAVIOUR"] = ($arParams["BEHAVIOUR"] == "USER" ? "USER" : "SIMPLE");
	
	$arParams["COMMENTS_TYPE"] = ($arParams["COMMENTS_TYPE"] == "forum" ? "forum" : "blog"); 
	// For blog
	$arParams["BLOG_URL"] = trim($arParams["BLOG_URL"]); 
//***************** URL ********************************************/
	$URL_NAME_DEFAULT = array(
		"detail" => "PAGE_NAME=detail".($arParams["BEHAVIOUR"] == "USER" ? "&USER_ALIAS=#USER_ALIAS#" : "" ).
			"&SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"
	);
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		$arParams[strToUpper($URL)."_URL"] = trim($arParams[strToUpper($URL)."_URL"]);
		if (empty($arParams[strToUpper($URL)."_URL"]))
			$arParams[strToUpper($URL)."_URL"] = $APPLICATION->GetCurPageParam($URL_VALUE, array("PAGE_NAME", "SECTION_ID", "ELEMENT_ID", "ACTION", "AJAX_CALL"));
		$arParams["~".strToUpper($URL)."_URL"] = $arParams[strToUpper($URL)."_URL"];
		$arParams[strToUpper($URL)."_URL"] = htmlspecialchars($arParams["~".strToUpper($URL)."_URL"]);
	}
//***************** CACHE ******************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/
/********************************************************************
				Default values
********************************************************************/
	if (!IsModuleInstalled($arParams["COMMENTS_TYPE"]))
	{
		ShowError("Module is not installed (".$arParams["COMMENTS_TYPE"].")");
		return 0;
	}
	if ($arParams["COMMENTS_TYPE"] == "blog" && empty($arParams["BLOG_URL"]))
	{
		ShowError("Empty blog url");
		return 0;
	}
/********************************************************************
				/Default values
********************************************************************/

/*************************************************************************
				Before caching
*************************************************************************/
// Clear cache.
if (isset($_REQUEST["parentId"]) || $_REQUEST["save_product_review"] == "Y" || isset($_REQUEST["delete_comment_id"]))
{
	$nameSpace = "bitrix";
	$pthToComponent = strToLower(trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/")));
	$tmpPathThis = strToLower(trim(preg_replace("'[\\\\/]+'", "/", __FILE__)));
	$tmpPathThis = str_replace($pthToComponent, "", $tmpPathThis);
	$arPath = explode("/", $tmpPathThis);
	if (!empty($arPath[0]))
		$nameSpace = $arPath[0];

	$arComponentPath = array($nameSpace.":photogallery.detail.comment");
	foreach ($arComponentPath as $path)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($path);
		if (StrLen($componentRelativePath) > 0)
		{
			$arComponentDescription = CComponentUtil::GetComponentDescr($path);
			if (IsSet($arComponentDescription) && is_array($arComponentDescription))
			{
				if (array_key_exists("CACHE_PATH", $arComponentDescription))
				{
					if ($arComponentDescription["CACHE_PATH"] == "Y")
						$arComponentDescription["CACHE_PATH"] = "/".SITE_ID.$componentRelativePath;
					if (StrLen($arComponentDescription["CACHE_PATH"]) > 0)
						BXClearCache(true, $arComponentDescription["CACHE_PATH"]);
				}
			}
		}
	}
}
/*************************************************************************
				/Before caching
*************************************************************************/

/*************************************************************************
				Caching
*************************************************************************/
$arFilter = array(
	"TYPE" => $arParams["COMMENTS_TYPE"],
	"USER" => $USER->GetGroups(),
	"PAGEN" => $_GET["pagen"]);

if($this->StartResultCache(false, $arFilter))
{
	$arResult["ELEMENT"] = array();
	
	if(!CModule::IncludeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
		return 0;
	}
	if($arParams["ELEMENT_ID"]>0)
	{
		//SELECT
		$arSelect = array(
			"ID",
			"CODE",
			"IBLOCK_ID",
			"IBLOCK_SECTION_ID",
			"SECTION_PAGE_URL",
			"NAME",
			"DETAIL_PICTURE",
			"PREVIEW_PICTURE",
			"PREVIEW_TEXT",
			"DETAIL_TEXT",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DETAIL_TEXT_TYPE",
			"TAGS",
			"DATE_CREATE",
			"CREATED_BY",
			"PROPERTY_REAL_PICTURE");
		if ($arParams["COMMENTS_TYPE"] == "blog"):
			$arSelect[] = "PROPERTY_BLOG_POST_ID";
			$arSelect[] = "PROPERTY_BLOG_COMMENTS_CNT";
		endif;
		//WHERE
		$arFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y");

		//EXECUTE
		$rsElement = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult["ELEMENT"] = $obElement->GetFields();
			$arResult["ELEMENT"]["PROPETIES"] = array();
			foreach ($arResult["ELEMENT"] as $key => $val)
			{
				if ((substr($key, 0, 9) == "PROPERTY_" && substr($key, -6, 6) == "_VALUE"))
				{
					$arResult["ELEMENT"]["PROPERTIES"][substr($key, 9, intVal(strLen($key)-15))] = array("VALUE" => $val);
				}
			}
			
			$arResult["ELEMENT"]["~DETAIL_PAGE_URL"] = CComponentEngine::MakePathFromTemplate($arParams["~DETAIL_URL"], 
					array("USER_ALIAS" => "empty","SECTION_ID" => $arResult["ELEMENT"]["IBLOCK_SECTION_ID"], "ELEMENT_ID" =>$arResult["ELEMENT"]["ID"]));
			$arResult["ELEMENT"]["DETAIL_PAGE_URL"] = htmlSpecialChars($arResult["ELEMENT"]["~DETAIL_PAGE_URL"]);
		}
	}
	
	if (!empty($arResult["ELEMENT"]))
	{
		$obProperty = false;
		$iCommentID = 0;
/************** BLOG *****************************************************/
		if ($arParams["COMMENTS_TYPE"] == "blog"):
			CModule::IncludeModule("blog");
			
			$obProperty = new CIBlockProperty;
			if (is_set($arResult["ELEMENT"]["PROPERTIES"], "BLOG_POST_ID"))
				$iCommentID = intVal($arResult["ELEMENT"]["PROPERTIES"]["BLOG_POST_ID"]["VALUE"]);
			else
			{
				$res = $obProperty->Add(array(
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "N",
						"MULTIPLE" => "N",
						"NAME" => (strLen(GetMessage("P_BLOG_POST_ID")) <= 0 ? "BLOG_POST_ID" : GetMessage("P_BLOG_POST_ID")),
						"CODE" => "BLOG_POST_ID"));
			}
			if (!is_set($arResult["ELEMENT"], "PROPERTY_BLOG_COMMENTS_CNT_VALUE"))
			{
				$res = $obProperty->Add(array(
						"IBLOCK_ID" => $arParams["IBLOCK_ID"],
						"ACTIVE" => "Y",
						"PROPERTY_TYPE" => "N",
						"MULTIPLE" => "N",
						"NAME" => (strLen(GetMessage("P_BLOG_COMMENTS_CNT")) <= 0 ? "P_BLOG_COMMENTS_CNT" : GetMessage("P_BLOG_COMMENTS_CNT")),
						"CODE" => "BLOG_COMMENTS_CNT"));
			}
			
			if ($iCommentID > 0)
			{
				$arPost = CBlogPost::GetByID($iCommentID);
				if (!$arPost)
					$iCommentID = 0;
				elseif (intVal($arPost["NUM_COMMENTS"]) > 0 && $arPost["NUM_COMMENTS"] != $arResult["ELEMENT"]["PROPERTIES"]["BLOG_COMMENTS_CNT"]["VALUE"])
				{
					CIBlockElement::SetPropertyValues($arParams["ELEMENT_ID"], $arParams["IBLOCK_ID"], intVal($arPost["NUM_COMMENTS"]), "BLOG_COMMENTS_CNT");
				}
			}
			
			if (intVal($iCommentID) <= 0 && isset($_REQUEST["parentId"]))
			{
				$arCategory = array();
				$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
				if (!empty($arResult["ELEMENT"]["TAGS"]))
				{
					$arCategoryVal = explode(",", $arResult["ELEMENT"]["TAGS"]);
					foreach($arCategoryVal as $k => $v)
					{
						$id = CBlogCategory::Add(array("BLOG_ID"=>$arBlog["ID"],"NAME"=>$v));
						if ($id)
							$arCategory[] = $id;
					}
				}
		
				$arResult["ELEMENT"]["DETAIL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["DETAIL_PICTURE"]);
				$arResult["ELEMENT"]["REAL_PICTURE"] = CFile::GetFileArray($arResult["ELEMENT"]["PROPERTIES"]["REAL_PICTURE"]["VALUE"]);

				$arFields=array(
					"TITLE"			=> $arResult["ELEMENT"]["NAME"],
					"DETAIL_TEXT"		=> 
						"[IMG]http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["DETAIL_PICTURE"]["SRC"]."[/IMG]\n".
						"[URL=http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["~DETAIL_PAGE_URL"]."]".$arResult["ELEMENT"]["NAME"]."[/URL]\n".
						(!empty($arResult["ELEMENT"]["TAGS"]) ? $arResult["ELEMENT"]["TAGS"]."\n" : "").
						$arResult["ELEMENT"]["~DETAIL_TEXT"]."\n".
						"[URL=http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["REAL_PICTURE"]["SRC"]."]".GetMessage("P_ORIGINAL")."[/URL]",
					"CATEGORY_ID"		=> implode(",", $arCategory),
					"PUBLISH_STATUS"	=> "P",
					"PERMS_POST"	=> array(),
					"PERMS_COMMENT"	=> array(),
					"=DATE_CREATE"	=> $DB->GetNowFunction(),
					"=DATE_PUBLISH"	=> $DB->GetNowFunction(),
					"AUTHOR_ID"	=>	(!empty($arResult["ELEMENT"]["CREATED_BY"]) ? $arResult["ELEMENT"]["CREATED_BY"] : 1),
					"BLOG_ID"	=> $arBlog["ID"],
					"ENABLE_TRACKBACK" => "N");

				$newID = CBlogPost::Add($arFields);
				if ($newID > 0)
				{
					foreach($arCategory as $key)
						CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $newID, "CATEGORY_ID"=>$key));
						
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
						BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
						BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$newID."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$newID."/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$newID."/"); 
						BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/category/"); 
					$iCommentID = $newID;
					CIBlockElement::SetPropertyValues($arResult["ELEMENT"]["ID"], $arParams["IBLOCK_ID"], $iCommentID, "BLOG_POST_ID");
				}
			}
			?><?
//			if ($iCommentID <= 0)
//			{
//				$this->AbortResultCache();
//				return 0;
//			}
			$arResult["COMMENT_ID"] = $iCommentID;
		endif;
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("PHOTO_ELEMENT_NOT_FOUND"));
		@define("ERROR_404", "Y");
	}
}
/*************************************************************************
				/Caching
*************************************************************************/

/*************************************************************************
				After caching
*************************************************************************/
if (isset($_REQUEST["parentId"]) || $_REQUEST["save_product_review"] == "Y" || isset($_REQUEST["delete_comment_id"]))
{
	$nameSpace = "bitrix";
	$pthToComponent = strToLower(trim(preg_replace("'[\\\\/]+'", "/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/")));
	$tmpPathThis = strToLower(trim(preg_replace("'[\\\\/]+'", "/", __FILE__)));
	$tmpPathThis = str_replace($pthToComponent, "", $tmpPathThis);
	$arPath = explode("/", $tmpPathThis);
	if (!empty($arPath[0]))
		$nameSpace = $arPath[0];

	$arComponentPath = array($nameSpace.":photogallery.detail.comment");
	foreach ($arComponentPath as $path)
	{
		$componentRelativePath = CComponentEngine::MakeComponentPath($path);
		if (StrLen($componentRelativePath) > 0)
		{
			$arComponentDescription = CComponentUtil::GetComponentDescr($path);
			if (IsSet($arComponentDescription) && is_array($arComponentDescription))
			{
				if (array_key_exists("CACHE_PATH", $arComponentDescription))
				{
					if ($arComponentDescription["CACHE_PATH"] == "Y")
						$arComponentDescription["CACHE_PATH"] = "/".SITE_ID.$componentRelativePath;
					if (StrLen($arComponentDescription["CACHE_PATH"]) > 0)
						BXClearCache(true, $arComponentDescription["CACHE_PATH"]);
				}
			}
		}
	}
}
/*************************************************************************
				/After caching
*************************************************************************/
?>
