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
$arParams["PATH_TO_BLOG"] = trim($arParams["PATH_TO_BLOG"]);
if(strlen($arParams["PATH_TO_BLOG"])<=0)
	$arParams["PATH_TO_BLOG"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#");
$arParams["PATH_TO_BLOG_EDIT"] = trim($arParams["PATH_TO_BLOG_EDIT"]);
if(strlen($arParams["PATH_TO_BLOG_EDIT"])<=0)
	$arParams["PATH_TO_BLOG_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog_edit&".$arParams["BLOG_VAR"]."=#blog#");

if (!$USER->IsAuthorized())
{	
	$arResult["NEED_AUTH"] = "Y";
}
else
{

	if(CBlog::CanUserCreateBlog($USER->GetID()))
	{
		$USER_ID = intval($USER->GetID());
		if(strlen($arParams["BLOG_URL"])>0)
		{
			$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] != SITE_ID)
					unset($arBlog);
			}
			else
				unset($arBlog);
		}
		else
		{
			$arBlog = CBlog::GetByOwnerID($USER_ID);
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] != SITE_ID)
					unset($arBlog);
			}
			else
				unset($arBlog);
		}
		if(!empty($arBlog))
		{
			$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
			$arResult["BLOG"] = $arBlog;
		}

		if (CBlog::CanUserManageBlog($arBlog["ID"], IntVal($USER->GetID())) || (CBlog::CanUserCreateBlog($USER->GetID()) && IntVal($arBlog["ID"])<=0))
		{
			$bBlockURL = COption::GetOptionString("blog", "block_url_change", "N") == 'Y' ? true : false;
			if($bBlockURL && !($USER->IsAdmin()) && !empty($arBlog))
				$arResult["BlockURL"] = "Y";

			if ($_POST['reset'])
			{
				LocalRedirect($arResult["urlToBlog"]);
			}
			elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['do_blog'] == "Y" && check_bitrix_sessid())
			{
				if ($_POST['perms_p'][1] > BLOG_PERMS_READ)
					$_POST['perms_p'][1] = BLOG_PERMS_READ;
				if ($_POST['perms_c'][1] > BLOG_PERMS_WRITE)
					$_POST['perms_c'][1] = BLOG_PERMS_WRITE;

				$arFields = array(
					"NAME" => $_POST['NAME'],
					"DESCRIPTION" => $_POST['DESCRIPTION'],
					"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
					"ENABLE_IMG_VERIF" => (($_POST['ENABLE_IMG_VERIF'] == "Y") ? "Y" : "N"),
					"EMAIL_NOTIFY" => (($_POST['EMAIL_NOTIFY'] == "Y") ? "Y" : "N"),
					"ENABLE_RSS" => "Y",
//					"PERMS_POST" => $_POST['perms_p'],
//					"PERMS_COMMENT" => $_POST['perms_c'],
				);
				if(IntVal($_POST['GROUP_ID'])>0)
					$arFields["GROUP_ID"] = IntVal($_POST['GROUP_ID']);

				if ((!$bBlockURL || $USER->IsAdmin() || empty($arBlog)) && strlen($_POST["URL"])>0)
					$arFields["URL"] = $_POST['URL'];

				if (count($arParams["BLOG_PROPERTY"]) > 0)
				{
					$arBlogFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", $arBlog["ID"], LANGUAGE_ID);
					foreach ($arBlogFields as $FIELD_NAME => $arBlogField)
					{
						if (!in_array($FIELD_NAME, $arParams["BLOG_PROPERTY"]))
							continue;

						if($arBlogField["EDIT_IN_LIST"]=="Y")
						{
							if($arBlogField["USER_TYPE"]["BASE_TYPE"]=="file")
							{
								$old_id = $_POST[$arBlogField["FIELD_NAME"]."_old_id"];
								if(is_array($old_id))
								{
									$arFields[$arBlogField["FIELD_NAME"]] = array();
									foreach($old_id as $key=>$value)
									{
										$arFields[$arBlogField["FIELD_NAME"]][$key] = array(
											"name" => $_FILES[$arBlogField["FIELD_NAME"]]["name"][$key],
											"type" => $_FILES[$arBlogField["FIELD_NAME"]]["type"][$key],
											"tmp_name" => $_FILES[$arBlogField["FIELD_NAME"]]["tmp_name"][$key],
											"error" => $_FILES[$arBlogField["FIELD_NAME"]]["error"][$key],
											"size" => $_FILES[$arBlogField["FIELD_NAME"]]["size"][$key],
											"del" => is_array($_POST[$arBlogField["FIELD_NAME"]."_del"]) && in_array($value, $_POST[$arBlogField["FIELD_NAME"]."_del"]),
											"old_id" => $value,
										);
									}
								}
								else
								{
									$arFields[$arBlogField["FIELD_NAME"]] = $_FILES[$arBlogField["FIELD_NAME"]];
									$arFields[$arBlogField["FIELD_NAME"]]["del"] = $_POST[$arBlogField["FIELD_NAME"]."_del"];
									$arFields[$arBlogField["FIELD_NAME"]]["old_id"] = $old_id;
								}
							}
							else
							{
								$arFields[$arBlogField["FIELD_NAME"]] = $_POST[$arBlogField["FIELD_NAME"]];
							}
						}
					}
				}
				
				$arFields["PATH"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => "#blog_url#"));
				
				if (!empty($arBlog))
				{
					
					if (is_array($_POST['group']))
						$arFields["AUTO_GROUPS"] = serialize(array_keys($_POST['group']));
					else
						$arFields["AUTO_GROUPS"] = "";
					
					$newID = CBlog::Update($arBlog["ID"], $arFields);
				}
				else
				{
					$arFields["=DATE_CREATE"] = $DB->CurrentTimeFunction();
					$arFields["ACTIVE"] = "Y";
					$arFields["OWNER_ID"] = $USER->GetID();

					$newID = CBlog::Add($arFields);
				}
				
					
				if(IntVal($newID) > 0)
				{
					$autoGroup = Array();
					if(!empty($_POST["grp_name"]))
					{
						foreach($_POST["grp_name"] as $k => $v)
						{
							if(IntVal($k) > 0)
							{
								if($_POST["grp_delete"][$k] != "Y")
								{
									$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$newID, "NAME" => $v, "!ID" => $k));
									if (!$res->Fetch())
									{
										CBlogUserGroup::Update($k, Array("NAME" => $v));
										if($_POST["group"][$k] == "Y")
											$autoGroup[] = $k;
									}
									else
									{
										$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_GROUP_EXIST", Array("#GROUP_NAME#" => htmlspecialchars($v)));
									}
								}
								else
									CBlogUserGroup::Delete($k);
							}
							else
							{
								$res = CBlogUserGroup::GetList(Array("NAME"=>"ASC"), Array("BLOG_ID"=>$newID, "NAME" => $v));
								if (!$res->Fetch())
								{
									$uGrID = CBlogUserGroup::Add(Array("NAME" => $v, "BLOG_ID" => $newID));
									
									if(IntVal($uGrID) > 0 && $_POST["group"][$k] == "Y")
										$autoGroup[] = $uGrID;							
								}
								else
								{
									$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_GROUP_EXIST", Array("#GROUP_NAME#" => htmlspecialchars($v)));
								}
							}
						}
					}
					
					if (!empty($autoGroup))
						$arFields = Array("AUTO_GROUPS" => serialize($autoGroup));
					else
						$arFields = Array("AUTO_GROUPS" => "");
					$arFields["PERMS_POST"] = $_POST['perms_p'];
					$arFields["PERMS_COMMENT"] = $_POST['perms_c'];
					
					$newID = CBlog::Update($newID, $arFields);
				}

				if (IntVal($newID)>0 && empty($arResult["ERROR_MESSAGE"]))
				{
					$arBlog = CBlog::GetByID($newID);
					$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
					$arResult["BLOG"] = $arBlog;
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
					$arResult["urlToBlogEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_EDIT"], array("blog" => $arBlog["URL"]));
					BXClearCache(True, "/".SITE_ID."/blog/new_blogs/");
					BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog['GROUP_ID']."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arBlog['URL']);
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_blogs/");
					
					if (strlen($_POST['apply'])>0)
						LocalRedirect($arResult["urlToBlogEdit"]);
					else
						LocalRedirect($arResult["urlToBlog"]);
				}
				else
				{
					if ($ex = $APPLICATION->GetException())
						$arResult["ERROR_MESSAGE"][] = $ex->GetString();
					elseif(empty($arResult["ERROR_MESSAGE"]))
						$arResult["ERROR_MESSAGE"][] = GetMessage('BLOG_ERR_SAVE');

					foreach($_POST as $k => $v)
					{
						if(is_array($v))
						{
							foreach($v as $k1 => $v1)
							{
								$arResult["BLOG"][$k1] = htmlspecialchars($v1);
								$arResult["BLOG"]['~'.$k1] = $v1;
							}
						}
						else
						{
							$arResult["BLOG"][$k] = htmlspecialchars($v);
							$arResult["BLOG"]['~'.$k] = $v;						
						}
					}
				}
			}
	
	
			if($arParams["SET_TITLE"]=="Y")
			{
				if (!empty($arBlog))
					$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], GetMessage('BLOG_TOP_TITLE')));
				else
					$APPLICATION->SetTitle(GetMessage('BLOG_NEW_BLOG'));
			}

			$dbBlogGroup = CBlogGroup::GetList(
				array("NAME" => "ASC"),
				array("SITE_ID" => SITE_ID)
			);
			$arBlogGroupTmp = Array();
			while ($arBlogGroup = $dbBlogGroup->GetNext())
			{
				if($arBlogGroup["ID"] == $arResult["BLOG"]["GROUP_ID"])
					$arBlogGroup["SELECTED"] = "Y";
				$arBlogGroupTmp[] = $arBlogGroup;
			}
			$arResult["GROUP"] = $arBlogGroupTmp;

			$arResult["AUTO_GROUPS"] = Array();
			if(!empty($arBlog))
				$arResult["AUTO_GROUPS"] = unserialize($arBlog["AUTO_GROUPS"]);
				
			if(!empty($arBlog))
			{
				$res=CBlogUserGroup::GetList(Array("NAME" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]), array("ID", "NAME", "BLOG_ID", "COUNT" => "USER2GROUP_ID"));
				while ($arGroup=$res->Fetch())
				{
					$arSumGroup[$arGroup["ID"]] = $arGroup["CNT"];
				}

				$res=CBlogUserGroup::GetList(Array("ID" => "ASC"), Array("BLOG_ID" => $arBlog["ID"]));
				$arUGroupTmp = Array();
				while($arUGroup=$res->GetNext())
				{
					if(is_array($arResult["AUTO_GROUPS"]) && in_array($arUGroup["ID"], $arResult["AUTO_GROUPS"]))
						$arUGroup["CHECKED"] = "Y";
					$arUGroup["CNT"] = IntVal($arSumGroup[$arUGroup["ID"]]);
					$arUGroupTmp[] = $arUGroup;
				}
				$arResult["USER_GROUP"] = $arUGroupTmp;
			}
			else
				$arResult["USER_GROUP"][] = Array("ID" => 0, "NAME" => GetMessage('BLOG_FRIENDS'), "CNT" => 0);

			$arResult["BLOG_POST_PERMS"] = $GLOBALS["AR_BLOG_POST_PERMS"];
			$arResult["BLOG_COMMENT_PERMS"] = $GLOBALS["AR_BLOG_COMMENT_PERMS"];

			$arResult["BLOG_PROPERTIES"] = array("SHOW" => "N");
			$arResult["useCaptcha"] = COption::GetOptionString("blog", "captcha_choice", "U");

			if (!empty($arParams["BLOG_PROPERTY"]))
			{
				$arBlogFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", $arBlog["ID"], LANGUAGE_ID);

				if (count($arParams["BLOG_PROPERTY"]) > 0)
				{
					foreach ($arBlogFields as $FIELD_NAME => $arBlogField)
					{
						if (!in_array($FIELD_NAME, $arParams["BLOG_PROPERTY"]))
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

			
			if (!empty($arBlog))
			{
				$res=CBlogUserGroupPerms::GetList(array("ID" => "DESC"),array("BLOG_ID" => $arBlog['ID'], "POST_ID" => 0));
				while($arPerms = $res->Fetch())
				{
					if ($arPerms['PERMS_TYPE']=='P')
						$arResult["BLOG"]["perms_p"][$arPerms['USER_GROUP_ID']] = $arPerms['PERMS'];
					elseif ($arPerms['PERMS_TYPE']=='C')
						$arResult["BLOG"]["perms_c"][$arPerms['USER_GROUP_ID']] = $arPerms['PERMS'];
				}
			}
			else
			{
				$arResult["BLOG"]["perms_p"][1] = BLOG_PERMS_READ;
				$arResult["BLOG"]["perms_p"][2] = BLOG_PERMS_READ;
				$arResult["BLOG"]["perms_c"][1] = BLOG_PERMS_WRITE;
				$arResult["BLOG"]["perms_c"][2] = BLOG_PERMS_WRITE;
			}
	
			if (!empty($arBlog))
				$arResult["CAN_UPDATE"] = "Y";
		}
		else
			$arResult["FATAL_ERROR"][] = GetMessage('BLOG_ERR_NO_RIGHTS');
	}
	else
		$arResult["FATAL_ERROR"][] = GetMessage("BLOG_NOT_RIGHTS_TO_CREATE");
}

$this->IncludeComponentTemplate();
?>