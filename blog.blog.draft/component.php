<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}
$arParams["SOCNET_GROUP_ID"] = IntVal($arParams["SOCNET_GROUP_ID"]);
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
			ShowError(GetMessage("BLOG_SONET_MODULE_NOT_AVAIBLE"));
			return;
		}
	}
	else
	{
		if (!CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $arParams["USER_ID"], "blog"))
		{
			ShowError(GetMessage("BLOG_SONET_MODULE_NOT_AVAIBLE"));
			return;
		}
	}
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage('B_B_DRAFT_TITLE'));

if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["POST_VAR"])<=0)
	$arParams["POST_VAR"] = "id";
	
$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
	
$arResult["OK_MESSAGE"] = Array();
$arResult["ERROR_MESSAGE"] = Array();

$user_id = IntVal($USER->GetID());
if($bSoNet)
{
	$blogOwnerID = $arParams["USER_ID"];
	$arResult["PostPerm"] = BLOG_PERMS_DENY;
	if($bGroupMode)
	{
		$arBlog = CBlog::GetBySocNetGroupID($arParams["SOCNET_GROUP_ID"]);
		$arResult["PostPerm"] = BLOG_PERMS_DENY;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post"))
			$arResult["PostPerm"] = BLOG_PERMS_READ;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
			$arResult["PostPerm"] = BLOG_PERMS_WRITE;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W")
			$arResult["PostPerm"] = BLOG_PERMS_FULL;
	}
	else
	{
		$arBlog = CBlog::GetByOwnerID($arParams["USER_ID"]);
		$arResult["PostPerm"] = BLOG_PERMS_DENY;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "view_post"))
			$arResult["PostPerm"] = BLOG_PERMS_READ;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "write_post"))
			$arResult["PostPerm"] = BLOG_PERMS_WRITE;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W")
			$arResult["PostPerm"] = BLOG_PERMS_FULL;
	}
}
else
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
	$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $user_id);
}

if(!empty($arBlog) && $arBlog["ACTIVE"] == "Y")
{
		$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
		if($arGroup["SITE_ID"] == SITE_ID)
		{

			$arResult["BLOG"] = $arBlog;
			
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_DRAFT_TITLE_BLOG")));
			if($arParams["SET_NAV_CHAIN"]=="Y")
				$APPLICATION->AddChainItem($arBlog["NAME"], CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "group_id" => $arParams["SOCNET_GROUP_ID"])));

			if($arResult["PostPerm"]>=BLOG_PERMS_WRITE)
			{
				$errorMessage = "";
				$okMessage = "";
				if (IntVal($_GET["del_id"]) > 0)
				{
					if (check_bitrix_sessid() && (CBlogPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id) || ($bSoNet && CBlogSoNetPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id, $arParams["USER_ID"], $arParams["SOCNET_GROUP_ID"]))))
					{
						$DEL_ID = IntVal($_GET["del_id"]);
						if (CBlogPost::Delete($DEL_ID))
						{
							$okMessage = GetMessage("B_B_DRAFT_M_DEL");
						}
						else
							$errorMessage = GetMessage("B_B_DRAFT_M_DEL_ERR");
					}
					else
						$errorMessage = GetMessage("B_B_DRAFT_M_DEL_RIGHTS");
				}

				if (StrLen($errorMessage) > 0)
					$arResult["ERROR_MESSAGE"][] = $errorMessage;
				if (StrLen($okMessage) > 0)
					$arResult["OK_MESSAGE"][] = $okMessage;			
				
				$arResult["POST"] = Array();
				$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
				$arPostColl1 = Array();
				$arPostColl2 = Array();

				$dbPost = CBlogPost::GetList(
					array("DATE_PUBLISH" => "DESC"),
					Array(
							"BLOG_ID" => $arBlog["ID"],
							"AUTHOR_ID" => $user_id,
							"!PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH
					)
				);
				while($arPost = $dbPost->Fetch())
				{
					$arPostColl1[] = $arPost;
				}
				
				$dbPost = CBlogPost::GetList(
					array("DATE_PUBLISH" => "DESC"),
					Array(
							"BLOG_ID" => $arBlog["ID"],
							"AUTHOR_ID" => $user_id,
							">DATE_PUBLISH" => ConvertTimeStamp(false, "FULL"),
							"PUBLISH_STATUS" => BLOG_PUBLISH_STATUS_PUBLISH
					)
				);
				while($arPost = $dbPost->Fetch())
				{
					$arPostColl1[] = $arPost;
				}

				function CompareBlogDatePublish($ar1, $ar2)
				{
				  $ts_ar1 = MakeTimeStamp($ar1["DATE_PUBLISH"]);

				  $ts_ar2 = MakeTimeStamp($ar2["DATE_PUBLISH"]);

				  if (MakeTimeStamp($ar1["DATE_PUBLISH"]) < MakeTimeStamp($ar2["DATE_PUBLISH"]))
				     return 1;
				  return -1;
				}

				uasort($arPostColl1, 'CompareBlogDatePublish');
				
				foreach($arPostColl1 as $arPost)
				{
					$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
					$arImage = array();
					$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"]));
					while ($arImage = $res->Fetch())
						$arImages[$arImage['ID']] = $arImage['FILE_ID'];
						
					if($arPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
					{
					
						$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
						if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
							$arAllow["VIDEO"] = "N";
						$arPost["TEXT_FORMATED"] = $p->convert($arPost["~DETAIL_TEXT"], true, $arImages, $arAllow);
					}
					else
					{
						$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
						if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
							$arAllow["VIDEO"] = "N";
					
						$arPost["TEXT_FORMATED"] = $p->convert($arPost["DETAIL_TEXT"], true, $arImages, $arAllow);
					}

					$arPost["IMAGES"] = $arImages;
					
					if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE || ($arResult["PostPerm"]>=BLOG_PERMS_WRITE && $arPost["AUTHOR_ID"] == $user_id))
					{
						$arPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
						$arPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arPost["ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid")));
					}

					$dbCategory = CBlogPostCategory::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $arPost["ID"]));
					while($arCategory = $dbCategory->GetNext())
					{
						$arCatTmp = $arCategory;
						$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $arCategory["CATEGORY_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"], "user_id" => $arBlog["OWNER_ID"]));
						$arPost["Category"][] = $arCatTmp;
					}
					$arPost["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
					$arResult["POST"][] = $arPost;
				}
			}
			else
				$arResult["FATAL_ERROR"] = GetMessage("B_B_DRAFT_NO_R_CR");
		}
		else
			$arResult["FATAL_ERROR"] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
}
else
	$arResult["FATAL_ERROR"] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
	
$this->IncludeComponentTemplate();
?>