<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
$arParams["SORT_BY1"] = (strlen($arParams["SORT_BY1"])>0 ? $arParams["SORT_BY1"] : "DATE_PUBLISH");
$arParams["SORT_ORDER1"] = (strlen($arParams["SORT_ORDER1"])>0 ? $arParams["SORT_ORDER1"] : "DESC");
$arParams["SORT_BY2"] = (strlen($arParams["SORT_BY2"])>0 ? $arParams["SORT_BY2"] : "ID");
$arParams["SORT_ORDER2"] = (strlen($arParams["SORT_ORDER2"])>0 ? $arParams["SORT_ORDER2"] : "DESC");

$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));
$arParams["YEAR"] = (IntVal($arParams["YEAR"])>0 ? IntVal($arParams["YEAR"]) : false);
$arParams["MONTH"] = (IntVal($arParams["MONTH"])>0 ? IntVal($arParams["MONTH"]) : false);
$arParams["DAY"] = (IntVal($arParams["DAY"])>0 ? IntVal($arParams["DAY"]) : false);
$arParams["CATEGORY_ID"] = (IntVal($arParams["CATEGORY_ID"])>0 ? IntVal($arParams["CATEGORY_ID"]) : false);
$arParams["NAV_TEMPLATE"] = (strlen($arParams["NAV_TEMPLATE"])>0 ? $arParams["NAV_TEMPLATE"] : "");

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
{
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	$arParams["CACHE_TIME_LONG"] = intval($arParams["CACHE_TIME_LONG"]);
	if(IntVal($arParams["CACHE_TIME_LONG"]) <= 0 && IntVal($arParams["CACHE_TIME"]) > 0)
		$arParams["CACHE_TIME_LONG"] = $arParams["CACHE_TIME"];

}
else
{
	$arParams["CACHE_TIME"] = 0;	
	$arParams["CACHE_TIME_LONG"] = 0;

}
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$SORT = Array($arParams["SORT_BY1"]=>$arParams["SORT_ORDER1"], $arParams["SORT_BY2"]=>$arParams["SORT_ORDER2"]);
$arSelectFields = Array("ID", "NAME", "DESCRIPTION", "URL", "DATE_CREATE", "DATE_UPDATE", "ACTIVE", "OWNER_ID", "OWNER_NAME", "LAST_POST_DATE", "LAST_POST_ID", "BLOG_USER_AVATAR", "BLOG_USER_ALIAS");

CpageOption::SetOptionString("main", "nav_page_in_session", "N");
if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("BLOG_BLOG_BLOG_TITLE"));

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

$arParams["PATH_TO_BLOG_CATEGORY"] = trim($arParams["PATH_TO_BLOG_CATEGORY"]);
if(strlen($arParams["PATH_TO_BLOG_CATEGORY"])<=0)
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&category=#category_id#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);

if(strlen($arParams["FILTER_NAME"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["FILTER_NAME"]))
{
	$arFilter = array();
}
else
{
	global $$arParams["FILTER_NAME"];
	$arFilter = ${$arParams["FILTER_NAME"]};
	if(!is_array($arrFilter))
		$arFilter = array();
}


$arResult["ERROR_MESSAGE"] = Array();
$arResultNFCache["OK_MESSAGE"] = Array();
$arResultNFCache["ERROR_MESSAGE"] = Array();
	
if(strlen($arParams["BLOG_URL"]) > 0)
{
	$user_id = IntVal($USER->GetID());
	
	//Message delete
	if (IntVal($_GET["del_id"]) > 0)
	{
		if (check_bitrix_sessid() && CBlogPost::CanUserDeletePost(IntVal($_GET["del_id"]), $user_id))
		{
			$DEL_ID = IntVal($_GET["del_id"]);
			if(CBlogPost::GetByID($DEL_ID))
			{
				if (CBlogPost::Delete($DEL_ID))
				{
					$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DELED");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/first_page/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/calendar/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/post/".$DEL_ID."/");
					BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
					BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
					BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
					BXClearCache(True, "/".SITE_ID."/blog/groups/".$arParams["BLOG_URL"]."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/trackback/".$DEL_ID."/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_out/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/rss_all/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
					BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
					BXClearCache(True, "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/favorite/");
				}
				else
					$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_ERROR");
			}
		}
		else
			$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_MES_DEL_NO_RIGHTS");
	}

	if($_GET["become_friend"]=="Y")
	{
		if($USER->IsAuthorized())
		{
			$arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"]);

			$dbCandidate = CBlogCandidate::GetList(Array(), Array("BLOG_ID"=>$arResult["BLOG"]["ID"], "USER_ID"=>$user_id));
			if($arCandidate = $dbCandidate->Fetch())
			{
				$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY");
			}
			else
			{
				if(CBlog::IsFriend($arResult["BLOG"]["ID"], $user_id))
					$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_3");
				else
				{
					if(CBlogCandidate::Add(Array("BLOG_ID"=>$arResult["BLOG"]["ID"], "USER_ID"=>$user_id)))
						$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ADDED");
					else
						$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ERROR");
				}
			}

			if($arOwnBlog = CBlog::GetByOwnerID($user_id))
			{
				$dbCandidate = CBlogCandidate::GetList(Array(), Array("BLOG_ID"=>$arOwnBlog["ID"], "USER_ID"=>$arResult["BLOG"]["OWNER_ID"]));
				if($arCandidate = $dbCandidate->Fetch())
				{
					$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_2");
				}
				else
				{
					if(CBlog::IsFriend($arOwnBlog["ID"], $arResult["BLOG"]["OWNER_ID"]))
						$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ALREADY_4");
					else
					{
						if(CBlogCandidate::Add(Array("BLOG_ID"=>$arOwnBlog["ID"], "USER_ID"=>$arResult["BLOG"]["OWNER_ID"])))
							$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ADDED_2");
						else
							$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_ERROR_2");
					}
				}
			}
		}
		else
			$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_NEED_AUTH");
	}
	elseif($_GET["become_friend"]=="N")
	{
		if($USER->IsAuthorized())
		{
			$arResult["BLOG"] = CBlog::GetByUrl($arParams["BLOG_URL"]);
			CBlogUser::DeleteFromUserGroup($user_id, $arResult["BLOG"]["ID"], BLOG_BY_USER_ID);

			$dbCandidate = CBlogCandidate::GetList(
				array(),
				array("BLOG_ID" => $arResult["BLOG"]["ID"], "USER_ID" => $user_id)
			);
			if ($arCandidate = $dbCandidate->Fetch())
				CBlogCandidate::Delete($arCandidate["ID"]);

			$arResultNFCache["OK_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_LEAVED");
		}
		else
			$arResultNFCache["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_REQUEST_NEED_AUTH");
	}

	if($GLOBALS["USER"]->IsAuthorized())
		$arUserGroups = CBlogUser::GetUserGroups($user_id, $arParams["BLOG_URL"], "Y", BLOG_BY_USER_ID, "URL");
	else
		$arUserGroups = Array(1);
		
	$numUserGroups = count($arUserGroups);
	for ($i = 0; $i < $numUserGroups - 1; $i++)
	{
		for ($j = $i + 1; $j < $numUserGroups; $j++)
		{
			if ($arUserGroups[$i] > $arUserGroups[$j])
			{
				$tmpGroup = $arUserGroups[$i];
				$arUserGroups[$i] = $arUserGroups[$j];
				$arUserGroups[$j] = $tmpGroup;
			}
		}
	}

	$strUserGroups = "";
	for ($i = 0; $i < $numUserGroups; $i++)
		$strUserGroups .= "_".$arUserGroups[$i];

	if(!isset($_GET["PAGEN_1"]) || IntVal($_GET["PAGEN_1"])<1)
	{
		$CACHE_TIME = $arParams["CACHE_TIME"];
		$cache_path = "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/first_page/";
	}
	else
	{
		$CACHE_TIME = $arParams["CACHE_TIME_LONG"];
		$cache_path = "/".SITE_ID."/blog/".$arParams["BLOG_URL"]."/pages/".IntVal($_GET["PAGEN_1"])."/";
	}
	
	$cache = new CPHPCache;
	$cache_id = "blog_blog_message_".serialize($arParams)."_".CDBResult::NavStringForCache($arParams["MESSAGE_COUNT"])."_".$strUserGroups;

	if ($CACHE_TIME > 0 && $cache->InitCache($CACHE_TIME, $cache_id, $cache_path))
	{
		$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvscript.js"></script>', true);
		$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/silverlight.js"></script>', true); 
		$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/wmvplayer/wmvplayer.js"></script>', true);
		$APPLICATION->AddHeadString('<script type="text/javascript" src="/bitrix/components/bitrix/player/mediaplayer/flvscript.js"></script>', true);
		$Vars = $cache->GetVars();
		foreach($Vars["arResult"] as $k=>$v)
			$arResult[$k] = $v;
		CBitrixComponentTemplate::ApplyCachedData($Vars["templateCachedData"]);	
		$cache->Output();
	}
	else
	{
		if ($CACHE_TIME > 0)
			$cache->StartDataCache($CACHE_TIME, $cache_id, $cache_path);

		if($arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]))
		{
			if($arBlog["ACTIVE"] == "Y")
			{
				$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
				if($arGroup["SITE_ID"] == SITE_ID)
				{
					$arBlog["Group"] = $arGroup;
					$arResult["BLOG"] = $arBlog;
					$arResult["PostPerm"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $user_id);
					if($arResult["PostPerm"] >= BLOG_PERMS_READ)
					{
						$arResult["enable_trackback"] = COption::GetOptionString("blog","enable_trackback", "Y");
						
						$arFilter["PUBLISH_STATUS"] = BLOG_PUBLISH_STATUS_PUBLISH;
						$arFilter[">PERMS"] = "D";
						$arFilter["BLOG_ID"] = $arBlog["ID"];
						
						if($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
						{
							$from = mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]);
							$to = mktime(0, 0, 0, $arParams["MONTH"], ($arParams["DAY"]+1), $arParams["YEAR"]);
							if($to>time())
								$to = time();
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						elseif($arParams["YEAR"] && $arParams["MONTH"])
						{
							$from = mktime(0, 0, 0, $arParams["MONTH"], 1, $arParams["YEAR"]);
							$to = mktime(0, 0, 0, ($arParams["MONTH"]+1), 1, $arParams["YEAR"]);
							if($to>time())
								$to = time();
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						elseif($arParams["YEAR"])
						{
							$from = mktime(0, 0, 0, 1, 1, $arParams["YEAR"]);
							$to = mktime(0, 0, 0, 1, 1, ($arParams["YEAR"]+1));
							if($to>time())
								$to = time();
							$arFilter[">=DATE_PUBLISH"] = ConvertTimeStamp($from, "FULL");
							$arFilter["<DATE_PUBLISH"] = ConvertTimeStamp($to, "FULL");
						}
						else
							$arFilter["<=DATE_PUBLISH"] = ConvertTimeStamp(false, "FULL"); 
						if(IntVal($arParams["CATEGORY_ID"])>0)
						{
							$arFilter["CATEGORY_ID"] = $arParams["CATEGORY_ID"];
							if($arParams["SET_TITLE"] == "Y")
							{
								$arCat = CBlogCategory::GetByID($arFilter["CATEGORY_ID"]);
								$arResult["title"]["category"] = CBlogTools::htmlspecialcharsExArray($arCat);
							}

						}

						$arResult["filter"] = $arFilter;

						$dbPost = CBlogPost::GetList(
							$SORT,
							$arFilter,
							array(
								"DATE_PUBLISH", "ID", "MAX" => "PERMS"
							),
							array("bDescPageNumbering"=>true, "nPageSize"=>$arParams["MESSAGE_COUNT"], "bShowAll" => false)
						);

						$arResult["NAV_STRING"] = $dbPost->GetPageNavString(GetMessage("MESSAGE_COUNT"), $arParams["NAV_TEMPLATE"]);
						$arResult["POST"] = Array();
						$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
						
						while($arPost = $dbPost->GetNext())
						{
							$CurPost = CBlogPost::GetByID($arPost["ID"]);
							$CurPost = CBlogTools::htmlspecialcharsExArray($CurPost);

							if($CurPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
							{
								$CurPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
							}
							else
							{
								$arOwnerBlog = CBlog::GetByOwnerID($CurPost["AUTHOR_ID"]);
								$CurPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"]));
							}
							$CurPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$CurPost["ID"]));
							$CurPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $CurPost["AUTHOR_ID"]));

							$arImage = array();
							$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID']));
							while ($arImage = $res->Fetch())
								$arImages[$arImage['ID']] = $arImage['FILE_ID'];
							
							if($CurPost["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
							{
								$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
								if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
									$arAllow["VIDEO"] = "N";
								$CurPost["TEXT_FORMATED"] = $p->convert($CurPost["~DETAIL_TEXT"], true, $arImages, $arAllow);
							}
							else
							{
								$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
								if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
									$arAllow["VIDEO"] = "N";
								$CurPost["TEXT_FORMATED"] = $p->convert($CurPost["DETAIL_TEXT"], true, $arImages, $arAllow);
							}
							$CurPost["IMAGES"] = $arImages;
							
							$CurPost["BlogUser"] = CBlogUser::GetByID($CurPost["AUTHOR_ID"], BLOG_BY_USER_ID); 
							$CurPost["BlogUser"] = CBlogTools::htmlspecialcharsExArray($CurPost["BlogUser"]);
							$CurPost["BlogUser"]["AVATAR_file"] = CFile::GetFileArray($CurPost["BlogUser"]["AVATAR"]);
							if ($CurPost["BlogUser"]["AVATAR_file"] !== false)
								$CurPost["BlogUser"]["AVATAR_img"] = CFile::ShowImage($CurPost["BlogUser"]["AVATAR_file"]["SRC"], 150, 150, "border=0 align='right'");
							
							$dbUser = CUser::GetByID($CurPost["AUTHOR_ID"]);
							$CurPost["arUser"] = $dbUser->GetNext();
							$CurPost["AuthorName"] = CBlogUser::GetUserName($CurPost["BlogUser"]["ALIAS"], $CurPost["arUser"]["NAME"], $CurPost["arUser"]["LAST_NAME"], $CurPost["arUser"]["LOGIN"]);
							
							if($arResult["PostPerm"]>=BLOG_PERMS_MODERATE || ($arResult["PostPerm"]>=BLOG_PERMS_WRITE && $CurPost["AUTHOR_ID"] == $user_id))
							{
								$CurPost["urlToEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$CurPost["ID"]));
								$CurPost["urlToDelete"] = htmlspecialcharsex($APPLICATION->GetCurPageParam("del_id=".$CurPost["ID"], Array("del_id", "sessid")));
							}
							if (preg_match("/(\[CUT\])/i",$CurPost['DETAIL_TEXT']) || preg_match("/(<CUT>)/i",$CurPost['DETAIL_TEXT']))
								$CurPost["CUT"] = "Y";
							
							if(strlen($CurPost["CATEGORY_ID"])>0)
							{
								$arCategory = explode(",",$CurPost["CATEGORY_ID"]);
								foreach($arCategory as $v)
								{
									if(IntVal($v)>0)
									{
										$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
										$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v));
										$CurPost["CATEGORY"][] = $arCatTmp;
									}
								}
							}
							$CurPost["POST_PROPERTIES"] = array("SHOW" => "N");
				
							if (!empty($arParams["POST_PROPERTY_LIST"]))
							{
								$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $CurPost["ID"], LANGUAGE_ID);
				
								if (count($arParams["POST_PROPERTY_LIST"]) > 0)
								{
									foreach ($arPostFields as $FIELD_NAME => $arPostField)
									{
										if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY_LIST"]))
											continue;
										$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
										$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
										$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
										$CurPost["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
									}
								}
								if (!empty($CurPost["POST_PROPERTIES"]["DATA"]))
									$CurPost["POST_PROPERTIES"]["SHOW"] = "Y";
							}
							$CurPost["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($CurPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
							$arResult["POST"][] = $CurPost;
						}
					}
				}
				else
					$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
			}
			else
				$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		}
		else
			$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");
		
		if ($CACHE_TIME > 0)
			$cache->EndDataCache(array("templateCachedData" => $this->GetTemplateCachedData(), "arResult" => $arResult));
	}

	if($arParams["SET_TITLE"]=="Y")
	{
		$title = $arResult["BLOG"]["NAME"];
		
		if($arParams["SET_NAV_CHAIN"]=="Y")
			$APPLICATION->AddChainItem($arResult["BLOG"]["NAME"], CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_BLOG"]), array("blog" => $arResult["BLOG"]["URL"])));
			
		if(isset($arResult["filter"][">=DATE_PUBLISH"]))
		{
			$title .= " - ".GetMessage("BLOG_BLOG_BLOG_MES_FOR");
			if($arParams["YEAR"] && $arParams["MONTH"] && $arParams["DAY"])
				$title .= ConvertTimeStamp(mktime(0, 0, 0, $arParams["MONTH"], $arParams["DAY"], $arParams["YEAR"]));
			elseif($arParams["YEAR"] && $arParams["MONTH"])
				$title .= GetMessage("BLOG_BLOG_BLOG_M_".$arParams["MONTH"])." ".$arParams["YEAR"]." ".GetMessage("BLOG_BLOG_BLOG_MES_YEAR");
			elseif($arParams["YEAR"])
				$title .= $arParams["YEAR"]." ".GetMessage("BLOG_BLOG_BLOG_MES_YEAR_ONE");
		}
		
		if(isset($arResult["filter"]["CATEGORY_ID"]))
		{
			$title .= " - ".GetMessage("BLOG_BLOG_BLOG_MES_CAT").' "';

			$title .= $arResult["title"]["category"]["NAME"].'"';
		}
		
		$APPLICATION->SetTitle($title);
	}

	if($_GET["become_friend"]!="Y" && !empty($arResult["BLOG"]) && $arResult["PostPerm"] < BLOG_PERMS_READ)
	{
		$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_FRIENDS_ONLY");
		if($USER->IsAuthorized())
			$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_U_CAN").' <a href="'.$APPLICATION->GetCurPageParam('become_friend=Y', Array('become_friend')).'">'.GetMessage("BLOG_BLOG_BLOG_U_CAN1").'</a> '.GetMessage("BLOG_BLOG_BLOG_U_CAN2");
		else
			$arResult["MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NEED_AUTH");
	}
}
else
	$arResult["ERROR_MESSAGE"][] = GetMessage("BLOG_BLOG_BLOG_NO_BLOG");


if(!empty($arResult["ERROR_MESSAGE"]))
{
	foreach($arResult["ERROR_MESSAGE"] as $val)
	{
		if(!in_array($val, $arResultNFCache["ERROR_MESSAGE"]))
			$arResultNFCache["ERROR_MESSAGE"][] = $val;
	}
}
if(!empty($arResult["OK_MESSAGE"]))
{
	foreach($arResult["OK_MESSAGE"] as $val)
	{
		if(!in_array($val, $arResultNFCache["OK_MESSAGE"]))
			$arResultNFCache["OK_MESSAGE"][] = $val;
	}
}
$arResult = array_merge($arResult, $arResultNFCache);

$this->IncludeComponentTemplate();
?>