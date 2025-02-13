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

$arParams["PATH_TO_USER_SETTINGS_EDIT"] = trim($arParams["PATH_TO_USER_SETTINGS_EDIT"]);
if(strlen($arParams["PATH_TO_USER_SETTINGS_EDIT"])<=0)
	$arParams["PATH_TO_USER_SETTINGS_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_settings_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

if (StrLen($arParams["BLOG_URL"]) > 0)
{
	if($arParams["SET_TITLE"]=="Y")
		$APPLICATION->SetTitle(GetMessage("B_B_US_TITLE"));
		
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
						$APPLICATION->SetTitle(str_replace("#NAME#", $arBlog["NAME"], GetMessage("B_B_US_TITLE_BLOG")));

					$errorMessage = "";
					$okMessage = "";

					if (IntVal($GLOBALS["del_id"]) > 0 && check_bitrix_sessid())
					{
						CBlogUser::AddToUserGroup($GLOBALS["del_id"], $arBlog["ID"], array(), "", BLOG_BY_USER_ID, BLOG_CHANGE);

						$dbCandidate = CBlogCandidate::GetList(
							array(),
							array("BLOG_ID" => $arBlog["ID"], "USER_ID" => IntVal($GLOBALS["del_id"]))
						);
						if ($arCandidate = $dbCandidate->Fetch())
						{
							CBlogCandidate::Delete($arCandidate["ID"]);
							$okMessage = GetMessage("B_B_US_DELETE_OK").".<br />";
						}
					}
					
					if (isset($_REQUEST["add_friend"]) && is_array($_REQUEST["add_friend"]))
					{
						foreach ($_REQUEST["add_friend"] as $key => $friend)
						{
							$arFriendUsers = Array();
							if (StrLen($friend) > 0)
							{
								$arUserID = array();
								$dbUsers = CBlogUser::GetList(
									array(),
									array(
											"GROUP_BLOG_ID" => $arBlog["ID"],
										),
									array("ID", "USER_ID")
									);
								while($arUsers = $dbUsers->Fetch())
									$arFriendUsers[] = $arUsers["USER_ID"];

								$dbSearchUser = CBlog::GetList(array(), array("URL" => $friend), false, false, array("ID", "OWNER_ID"));
								if($arSearchUser = $dbSearchUser->Fetch())
									$arUserID[] = $arSearchUser["OWNER_ID"];

								/*
								$dbSearchUser = CBlog::GetList(array(), array("NAME" => $friend), false, false, array("ID", "OWNER_ID"));
								while(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["OWNER_ID"], $arUserID))
									$arUserID[] = $arSearchUser["OWNER_ID"];
								*/

								$canUseAlias = COption::GetOptionString("blog", "allow_alias", "Y");
								if ($canUseAlias == "Y")
								{
									$dbSearchUser = CBlogUser::GetList(array(), array("ALIAS" => $friend), false, false, array("ID", "USER_ID"));
									if(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["USER_ID"], $arUserID))
										$arUserID[] = $arSearchUser["USER_ID"];
								}

								$dbSearchUser = CUser::GetList(($b = "LOGIN"), ($o = "ASC"), array("LOGIN_EQUAL" => $friend));
								if(($arSearchUser = $dbSearchUser->Fetch()) && !in_array($arSearchUser["ID"], $arUserID))
									$arUserID[] = $arSearchUser["ID"];

								if (count($arUserID) > 0)
								{
									for ($i = 0; $i < count($arUserID); $i++)
									{
										if($arUserID[$i] != $arBlog["OWNER_ID"] && !in_array($arUserID[$i], $arFriendUsers))
										{
											$dbCandidate = CBlogCandidate::GetList(
												array(),
												array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUserID[$i])
											);
											if ($dbCandidate->Fetch())
											{
												$okMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ALREADY_WANT")).".<br />";
											}
											else
											{
												if (CBlogCandidate::Add(array("BLOG_ID" => $arBlog["ID"], "USER_ID" => $arUserID[$i])))
													$okMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ADDED")).".<br />";
												else
													$errorMessage .= str_replace("#NAME#", "[".$arUserID[$i]."] ".htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_ADD_ERROR")).".<br />";
											}
										}
									}
								}
								else
								{
									$errorMessage .= str_replace("#NAME#", htmlspecialcharsex($friend), GetMessage("BLOG_BLOG_ADD_F_POS_NOT_FOUND")).".<br />";
								}
							}
						}
					}

					$arResult["ERROR_MESSAGE"] = $errorMessage;
					$arResult["OK_MESSAGE"] = $okMessage;

					$canUseAlias = COption::GetOptionString("blog", "allow_alias", "Y");
					if ($canUseAlias == "Y")
						$arOrderBy = array("ALIAS" => "ASC", "USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC");
					else
						$arOrderBy = array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC");

					$dbUsers = CBlogCandidate::GetList(
						$arOrderBy,
						array("BLOG_ID" => $arBlog["ID"]),
						false,
						false,
						array("ID", "USER_ID", "BLOG_USER_ALIAS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
					);
					$arResult["Candidate"] = Array();
					while($arUsers = $dbUsers->GetNext())
					{
						$arUsers["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUsers["USER_ID"]));
						$arUsers["NameFormated"] = CBlogUser::GetUserName($arUsers["BLOG_USER_ALIAS"], $arUsers["USER_NAME"], $arUsers["USER_LAST_NAME"], $arUsers["USER_LOGIN"]);
						$arUsers["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS_EDIT"], array("user_id" => $arUsers["USER_ID"], "blog"=>$arBlog["URL"]));
						$arUsers["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arUsers["USER_ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid")));
						$arResult["Candidate"][] = $arUsers;
					}

					$dbUsers = CBlogUser::GetList(
						$arOrderBy,
						array("GROUP_BLOG_ID" => $arBlog["ID"]),
						array("ID", "USER_ID", "ALIAS", "USER_LOGIN", "USER_NAME", "USER_LAST_NAME")
					);
					$arResult["Users"] = Array();
					while($arUsers = $dbUsers->GetNext())
					{
						$arUsers["urlToUser"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUsers["USER_ID"]));
						$arUsers["NameFormated"] = CBlogUser::GetUserName($arUsers["BLOG_USER_ALIAS"], $arUsers["USER_NAME"], $arUsers["USER_LAST_NAME"], $arUsers["USER_LOGIN"]);
						$arUsers["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_SETTINGS_EDIT"], array("user_id" => $arUsers["USER_ID"], "blog"=>$arBlog["URL"]));
						$arUsers["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$arUsers["USER_ID"].'&'.bitrix_sessid_get(), Array("del_id", "sessid")));
											
						$dbUserGroups = CBlogUserGroup::GetList(
							array(),
							array(
								"USER2GROUP_USER_ID" => $arUsers["USER_ID"],
								"BLOG_ID" => $arBlog["ID"]
							),
							false,
							false,
							array("ID", "NAME")
						);
						$bNeedComa = False;
						while ($arUserGroups = $dbUserGroups->GetNext())
						{
							if ($bNeedComa)
								$arUsers["groupsFormated"] .= ", ";
							$arUsers["groups"][] = $arUserGroups;
							$arUsers["groupsFormated"] .= $arUserGroups["NAME"];
							$bNeedComa = True;
						}
						
						$arResult["Users"][] = $arUsers;
					}
				}
				else
				{
					$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_RIGHT");
				}
			}
			else
				$arResult["FATAL_ERROR"] = GetMessage("B_B_US_NO_BLOG");
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
	
$this->IncludeComponentTemplate();
?>
