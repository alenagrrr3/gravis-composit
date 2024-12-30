<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGES_PER_PAGE"] = IntVal($arParams["MESSAGES_PER_PAGE"])>0 ? IntVal($arParams["MESSAGES_PER_PAGE"]): 15;
$arParams["PREVIEW_WIDTH"] = IntVal($arParams["PREVIEW_WIDTH"])>0 ? IntVal($arParams["PREVIEW_WIDTH"]): 100;
$arParams["PREVIEW_HEIGHT"] = IntVal($arParams["PREVIEW_HEIGHT"])>0 ? IntVal($arParams["PREVIEW_HEIGHT"]): 100;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");
$arParams["MESSAGE_LENGTH"] = (IntVal($arParams["MESSAGE_LENGTH"])>0)?$arParams["MESSAGE_LENGTH"]:100;
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;	
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

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

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
$UserGroupID = Array(1);
if($USER->IsAuthorized())
	$UserGroupID[] = 2;

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BNPL_TITLE"));

	
$cache = new CPHPCache;
$cache_id = "blog_last_messages_".serialize($arParams)."_".serialize($UserGroupID)."_".$USER->IsAdmin()."_".CDBResult::NavStringForCache($arParams["BLOG_COUNT"]);
$cache_path = "/".SITE_ID."/blog/last_messages_list/";

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
			"<=DATE_PUBLISH" => ConvertTimeStamp(false, "FULL", false),
			"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH,
			"BLOG_ACTIVE" => "Y",
			"BLOG_GROUP_SITE_ID" => SITE_ID,
			">PERMS" => BLOG_PERMS_DENY
		);	
	if(strlen($arParams["BLOG_URL"]) > 0)
		$arFilter["BLOG_URL"] = $arParams["BLOG_URL"];
	if(IntVal($arParams["GROUP_ID"]) > 0)
		$arFilter["BLOG_GROUP_ID"] = $arParams["GROUP_ID"];
	if($USER->IsAdmin())
		unset($arFilter[">PERMS"]);
		
	if(CModule::IncludeModule("socialnetwork") && IntVal($arParams["SOCNET_GROUP_ID"]) <= 0 && IntVal($arParams["USER_ID"]) <= 0)
	{
		unset($arFilter[">PERMS"]);
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
		$arFilter["BLOG_ID"] = $arAvBlog;
	}
	elseif(IntVal($arParams["SOCNET_GROUP_ID"]) > 0 || IntVal($arParams["USER_ID"]) > 0)
	{
		$blogOwnerID = $arParams["USER_ID"];
		$user_id = $USER->GetID();
		$bGroupMode = false;
		if(IntVal($arParams["SOCNET_GROUP_ID"]) > 0)
			$bGroupMode = true;
		if($bGroupMode)
		{
			$arBlog = CBlog::GetBySocNetGroupID($arParams["SOCNET_GROUP_ID"]);
			$perms = BLOG_PERMS_DENY;
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post"))
				$perms = BLOG_PERMS_READ;
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
				$perms = BLOG_PERMS_WRITE;
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W")
				$perms = BLOG_PERMS_FULL;
		}
		else
		{
			$arBlog = CBlog::GetByOwnerID($arParams["USER_ID"]);
			$perms = BLOG_PERMS_DENY;
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "view_post"))
			{
				$perms = BLOG_PERMS_READ;
			}
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "write_post"))
			{
				$perms = BLOG_PERMS_WRITE;
			}
			if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $arParams["USER_ID"], "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W" || $arParams["USER_ID"] == $user_id)
			{
				$perms = BLOG_PERMS_FULL;
			}
		}
		$arFilter["BLOG_ID"] = $arBlog["ID"];
		unset($arFilter[">PERMS"]);
	}
	
	if(strlen($perms) <= 0 || (!empty($arFilter["BLOG_ID"]) && $perms >= BLOG_PERMS_READ))
	{
		
	$arSelectedFields = array("ID", "BLOG_ID", "TITLE", "DATE_PUBLISH", "AUTHOR_ID", "DETAIL_TEXT", "BLOG_ACTIVE", "BLOG_URL", "BLOG_GROUP_ID", "BLOG_GROUP_SITE_ID", "AUTHOR_LOGIN", "AUTHOR_NAME", "AUTHOR_LAST_NAME", "BLOG_USER_ALIAS", "BLOG_OWNER_ID", "VIEWS", "NUM_COMMENTS", "ATTACH_IMG", "BLOG_SOCNET_GROUP_ID", "DETAIL_TEXT_TYPE");

	$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
	
	if($arParams["MESSAGES_PER_PAGE"])
		$COUNT = array("nPageSize"=>$arParams["MESSAGES_PER_PAGE"], "bShowAll" => false);
	else
		$COUNT = false;

	$arResult = Array();
	$dbPosts = CBlogPost::GetList(
		$SORT,
		$arFilter,
		false,
		$COUNT,
		$arSelectedFields
	);
	$arResult["NAV_STRING"] = $dbPosts->GetPageNavString(GetMessage("B_B_GR_TITLE"), $arParams["NAV_TEMPLATE"]);

	$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
	while ($arPost = $dbPosts->GetNext())
	{
		$arTmp = $arPost;
		
		if($arTmp["AUTHOR_ID"] == $arTmp["BLOG_OWNER_ID"])
		{
			$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
		}
		else
		{
			$arOwnerBlog = CBlog::GetByOwnerID($arTmp["AUTHOR_ID"]);
			if(!empty($arOwnerBlog))
				$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"], "user_id" => $arOwnerBlog["OWNER_ID"]));
			else
				$arTmp["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arPost["BLOG_URL"], "user_id" => $arPost["AUTHOR_ID"]));
		}

		if(IntVal($arPost["BLOG_SOCNET_GROUP_ID"]) > 0)
			$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>$arPost["ID"], "group_id" => $arPost["BLOG_SOCNET_GROUP_ID"]));
		else
			$arTmp["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arPost["BLOG_URL"], "post_id"=>$arPost["ID"], "user_id" => $arPost["BLOG_OWNER_ID"]));
			
		$arTmp["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));
		
		$arTmp["AuthorName"] = CBlogUser::GetUserName($arPost["BLOG_USER_ALIAS"], $arPost["AUTHOR_NAME"], $arPost["AUTHOR_LAST_NAME"], $arPost["AUTHOR_LOGIN"]);
		
		$arImage = array();
		$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID']));
		while ($arImage = $res->Fetch())
			$arImages[$arImage['ID']] = $arImage['FILE_ID'];
		
		if (preg_match("/(\[CUT\])/i",$arTmp['DETAIL_TEXT']) || preg_match("/(<CUT>)/i",$arTmp['DETAIL_TEXT']))
			$arTmp["CUT"] = "Y";
		
		if($arTmp["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
		{
			$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
			if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
				$arAllow["VIDEO"] = "N";
			$arTmp["TEXT_FORMATED"] = $p->convert($arTmp["~DETAIL_TEXT"], true, $arImages, $arAllow);
		}
		else
		{
			$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
			if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
				$arAllow["VIDEO"] = "N";
			$arTmp["TEXT_FORMATED"] = $p->convert($arTmp["~DETAIL_TEXT"], true, $arImages, $arAllow);
		}
		$arTmp["IMAGES"] = $arImages;

		$arTmp["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arTmp["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
		
		$dbCategory = CBlogPostCategory::GetList(Array("NAME" => "ASC"), Array("POST_ID" => $arTmp["ID"], "BLOG_ID" => $arPost["BLOG_ID"]));
		while($arCategory = $dbCategory->GetNext())
		{
			$arCategory["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arTmp["BLOG_URL"], "category_id" => $arCategory["ID"]));
			$arTmp["CATEGORY"][] = $arCategory;
		}

		$arResult["POSTS"][] = $arTmp;
	}

	if ($arParams["CACHE_TIME"] > 0)
		$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
	}
}
$this->IncludeComponentTemplate();
?>