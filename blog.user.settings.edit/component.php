<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arParams["BLOG_URL"]);
if(strLen($arParams["BLOG_VAR"])<=0)
	$arParams["BLOG_VAR"] = "blog";
if(strLen($arParams["USER_VAR"])<=0)
	$arParams["USER_VAR"] = "id";
if(strLen($arParams["PAGE_VAR"])<=0)
	$arParams["PAGE_VAR"] = "page";

$arParams["PATH_TO_USER_SETTINGS"] = trim($arParams["PATH_TO_USER_SETTINGS"]);
if(strlen($arParams["PATH_TO_USER_SETTINGS"])<=0)
	$arParams["PATH_TO_USER_SETTINGS"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
$arParams["ID"] = IntVal($arParams["ID"]);

if (StrLen($arParams["BLOG_URL"]) > 0)
{
	if(IntVal($arParams["ID"]) > 0)
	{
		if($arParams["SET_TITLE"]=="Y")
			$APPLICATION->SetTitle(GetMessage("B_B_USE_TITLE"));

		$dbUser = CUser::GetByID($arParams["ID"]);
		if ($arUser = $dbUser->GetNext())
		{
			$arResult["User"] = $arUser;
			if ($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
			{
				if($arBlog["ACTIVE"] == "Y")
				{
					$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
					if($arGroup["SITE_ID"] == SITE_ID)
					{
						$arResult["Blog"] = $arBlog;

						if (CBlog::CanUserManageBlog($arBlog["ID"], IntVal($USER->GetID())))
						{
							if($arParams["SET_TITLE"]=="Y")
								$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_USE_TITLE_BLOG")));
							$errorMessage = "";
							$okMessage = "";
							$arBlogUser = CBlogUser::GetByID($arUser["ID"], BLOG_BY_USER_ID);
							$arBlogUser = CBlogTools::htmlspecialcharsExArray($arBlogUser);
							$arResult["BlogUser"] = $arBlogUser;
							
							if ($GLOBALS["user_action"] == "Y" && check_bitrix_sessid())
							{
								if(strlen($GLOBALS["cancel"])>0)
									LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arBlog["URL"])));
								if (empty($arBlogUser))
								{
									CBlogUser::Add(
										array(
											"USER_ID" => $arUser["ID"],
											"=LAST_VISIT" => $DB->GetNowFunction(),
											"=DATE_REG" => $DB->GetNowFunction(),
											"ALLOW_POST" => "Y"
										)
									);
								}

								CBlogUser::AddToUserGroup($arUser["ID"], $arBlog["ID"], $GLOBALS["add2groups"], "", BLOG_BY_USER_ID, BLOG_CHANGE);

								$dbCandidate = CBlogCandidate::GetList(
									array(),
									array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUser["ID"])
								);
								if ($arCandidate = $dbCandidate->Fetch())
									CBlogCandidate::Delete($arCandidate["ID"]);
								
								LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS"], array("blog" => $arBlog["URL"])));
							}

							$arResult["ERROR_MESSAGE"] = $errorMessage;
							$arResult["OK_MESSAGE"] = $okMessage;
							$arResult["userName"] = CBlogUser::GetUserName($arBlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]);
							$arResult["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));
							$arResult["arUserGroups"] = CBlogUser::GetUserGroups($arUser["ID"], $arBlog["ID"], "", BLOG_BY_USER_ID);
							$dbBlogGroups = CBlogUserGroup::GetList(
								array("NAME" => "ASC"),
								array("BLOG_ID" => $arBlog["ID"]),
								false,
								false,
								array("ID", "NAME")
							);
							while ($arBlogGroups = $dbBlogGroups->GetNext())
								$arResult["Groups"][] = $arBlogGroups;
						}
						else
							$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_RIGHT");
					}
					else
						$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
				}
				else
					$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
			}
			else
			{
				$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
			}
		}
		else
		{
			$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_USER");
		}
	}
	else
		$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_USER");
}
else
{
	$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
}
	
$this->IncludeComponentTemplate();
?>