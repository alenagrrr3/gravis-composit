<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

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
	
$arParams["PATH_TO_BLOG_INDEX"] = trim($arParams["PATH_TO_BLOG_INDEX"]);
if(strlen($arParams["PATH_TO_BLOG_INDEX"])<=0)
	$arParams["PATH_TO_BLOG_INDEX"] = htmlspecialchars($APPLICATION->GetCurPage());
	
$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if(strlen($arParams["PATH_TO_DRAFT"])<=0)
	$arParams["PATH_TO_DRAFT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_FRIENDS"] = trim($arParams["PATH_TO_USER_FRIENDS"]);
if(strlen($arParams["PATH_TO_USER_FRIENDS"])<=0)
	$arParams["PATH_TO_USER_FRIENDS"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_friends&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER_SETTINGS"] = trim($arParams["PATH_TO_USER_SETTINGS"]);
if(strlen($arParams["PATH_TO_USER_SETTINGS"])<=0)
	$arParams["PATH_TO_USER_SETTINGS"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings&".$arParams["BLOG_VAR"]."=#blog#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if(strlen($arParams["PATH_TO_GROUP_EDIT"])<=0)
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_BLOG_EDIT"] = trim($arParams["PATH_TO_BLOG_EDIT"]);
if(strlen($arParams["PATH_TO_BLOG_EDIT"])<=0)
	$arParams["PATH_TO_BLOG_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog_edit&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_CATEGORY_EDIT"] = trim($arParams["PATH_TO_CATEGORY_EDIT"]);
if(strlen($arParams["PATH_TO_CATEGORY_EDIT"])<=0)
	$arParams["PATH_TO_CATEGORY_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=category_edit&".$arParams["BLOG_VAR"]."=#blog#");

if(!($USER->IsAuthorized()))
{
	$arResult["urlToAuth"] = htmlspecialchars($APPLICATION->GetCurPageParam("auth=Y", array("login", "logout", "register", "forgot_password", "change_password")));

	if(COption::GetOptionString("main", "new_user_registration", "Y") == "Y")
		$arResult["urlToRegister"] = htmlspecialchars($APPLICATION->GetCurPageParam("auth=Y&register=yes", array("login", "logout", "register", "forgot_password", "change_password", "backurl")));
}
else
{
	$arResult["urlToLogout"] = $APPLICATION->GetCurPageParam("logout=yes", array("login", "logout", "register", "forgot_password", "change_password", "backurl"));
}

$user_id = $USER->GetID();

if(IntVal($user_id)>0)
{
	CBlogUser::SetLastVisit();
	$arResult["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $user_id));
	$arOwnBlog = CBlog::GetByOwnerID($user_id);
	if($arOwnBlog["ACTIVE"] == "Y")
	{
		$arGroup = CBlogGroup::GetByID($arOwnBlog["GROUP_ID"]);
		if($arGroup["SITE_ID"] == SITE_ID)
		{
			if(!empty($arOwnBlog))
			{
				$arResult["OwnBlog"] = $arOwnBlog;
				$arResult["urlToOwnBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnBlog["URL"]));
				$arResult["urlToFriends"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_FRIENDS"], array("user_id" => $user_id));
				$arResult["urlToOwnNewPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arOwnBlog["URL"], "post_id" => "new"));
				$arResult["urlToOwnBlogEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_EDIT"], array("blog" => $arOwnBlog["URL"]));

			}
		}
		else
			unset($arOwnBlog);		
	}
	else
		unset($arOwnBlog);

}

if(strlen($arParams["BLOG_URL"])>0)
{
	if($arOwnBlog["URL"] != $arParams["BLOG_URL"])
	{
		$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
		if($arBlog["ACTIVE"] == "Y")
		{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID)
			{
				$arResult["Blog"] = $arBlog;
				$arResult["urlToCurrentBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arParams["BLOG_URL"]));
			}
			else
				unset($arBlog);
		}
		else
			unset($arBlog);
	}
	elseif(!empty($arOwnBlog))
	{
		$arBlog = $arOwnBlog;
	}

	if(!empty($arBlog))
	{
		
		if(IntVal($user_id)>0)
		{
			if(CBlog::GetBlogUserPostPerms($arBlog["ID"], $user_id) >= BLOG_PERMS_WRITE)
			{
				$arResult["urlToDraft"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("blog" => $arBlog["URL"]));
				$arResult["urlToNewPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id" => "new"));
				$arResult["SecondLine"] = "Y";
			}
			
			if (CBlog::CanUserManageBlog($arBlog["ID"], $user_id))
			{
				$arResult["urlToUserSettings"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arBlog["URL"]));
				$arResult["urlToGroupEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("blog" => $arBlog["URL"]));
				$arResult["urlToCategoryEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_CATEGORY_EDIT"], array("blog" => $arBlog["URL"]));
				$arResult["urlToBlogEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_EDIT"], array("blog" => $arBlog["URL"]));
				$arResult["ThirdLine"] = "Y";
			}
		}
		
		if(empty($arOwnBlog) || $arOwnBlog["URL"] != $arBlog["URL"])
		{
			if(IntVal($user_id)>0)
			{
				if (!CBlog::IsFriend($arBlog["ID"], $user_id))
				{
					$arResult["urlToBecomeFriend"] = $arResult["urlToCurrentBlog"].(strpos($arResult["urlToCurrentBlog"], "?") === false ? "?" : "&")."become_friend=Y";
					$arResult["SecondLine"] = "Y";
				}

				if(!empty($arOwnBlog))
				{
					if (!CBlog::IsFriend($arOwnBlog["ID"], $arBlog["OWNER_ID"]))
					{
						$tmpUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arOwnBlog["URL"]));
						
						$arResult["urlToAddFriend"] = $tmpUrl.(strpos($tmpUrl, "?") === false ? "?" : "&")."add_friend[]=".UrlEncode($arBlog["URL"]);
						$arResult["SecondLine"] = "Y";
					}
				}
			}
		}
	}
}

$this->IncludeComponentTemplate();
?>