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
	$arResult["bSoNet"] = "Y";
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
$arParams["ID"] = IntVal($arParams["ID"]);
$arParams["BLOG_URL"] = preg_replace("/[^a-zA-Z0-9_-]/is", "", Trim($arParams["BLOG_URL"]));

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

$arParams["PATH_TO_POST_EDIT"] = trim($arParams["PATH_TO_POST_EDIT"]);
if(strlen($arParams["PATH_TO_POST_EDIT"])<=0)
	$arParams["PATH_TO_POST_EDIT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post_edit&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_DRAFT"] = trim($arParams["PATH_TO_DRAFT"]);
if(strlen($arParams["PATH_TO_DRAFT"])<=0)
	$arParams["PATH_TO_DRAFT"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=draft&".$arParams["BLOG_VAR"]."=#blog#");
	
$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

$user_id= $USER->GetID();
$arResult["UserID"] = $user_id;
$arResult["enable_trackback"] = COption::GetOptionString("blog","enable_trackback", "Y");
$arResult["preview"] = (strlen($_POST["preview"]) > 0) ? "Y" : "N";
if($bSoNet)
{
		$arFilterblg = Array(
		        "ACTIVE" => "Y",
			"GROUP_ID" => $arParams["GROUP_ID"],
			"GROUP_SITE_ID" => SITE_ID,
			);

	$blogOwnerID = $arParams["USER_ID"];
	if($bGroupMode)
	{
		$arFilterblg["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
		$arResult["perms"] = BLOG_PERMS_DENY;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "view_post"))
			$arResult["perms"] = BLOG_PERMS_READ;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "write_post"))
			$arResult["perms"] = BLOG_PERMS_WRITE;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_GROUP, $arParams["SOCNET_GROUP_ID"], "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W")
			$arResult["perms"] = BLOG_PERMS_FULL;
	}
	else
	{
		$arFilterblg["OWNER_ID"] = $arParams["USER_ID"];
		$arResult["perms"] = BLOG_PERMS_DENY;
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "view_post"))
		{
			$arResult["perms"] = BLOG_PERMS_READ;
		}
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "write_post"))
		{
			$arResult["perms"] = BLOG_PERMS_WRITE;
		}
		if (CSocNetFeaturesPerms::CanPerformOperation($user_id, SONET_ENTITY_USER, $blogOwnerID, "blog", "full_post") || $USER->IsAdmin() || $APPLICATION->GetGroupRight("blog") >= "W" || $blogOwnerID == $user_id)
		{
			$arResult["perms"] = BLOG_PERMS_FULL;
		}
	}
	$dbBl = CBlog::GetList(Array(), $arFilterblg);
	$arBlog = $dbBl ->Fetch();

	if(!empty($arBlog))
	{
		if($arBlog["GROUP_ID"] != $arParams["GROUP_ID"])
			unset($arBlog);
	}
}
else
{
	$arBlog = CBlog::GetByUrl($arParams["BLOG_URL"]);
	if(IntVal($arParams["ID"]) > 0)
		$arResult["perms"] = CBlogPost::GetBlogUserPostPerms($arParams["ID"], $arResult["UserID"]);
	else
		$arResult["perms"] = CBlog::GetBlogUserPostPerms($arBlog["ID"], $arResult["UserID"]);
}

	if((!empty($arBlog) && $arBlog["ACTIVE"] == "Y") || ($bSoNet && empty($arBlog)))
	{
			$arGroup = CBlogGroup::GetByID($arBlog["GROUP_ID"]);
			if($arGroup["SITE_ID"] == SITE_ID || ($bSoNet && empty($arBlog)))
			{

				if(COption::GetOptionString("blog","allow_html", "N") == "Y" && $arBlog["ALLOW_HTML"] == "Y")
					$arResult["allow_html"] = "Y";
				$arResult["Blog"] = $arBlog;
				if($bGroupMode)
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));
				else
					$arResult["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]));

				if(IntVal($arParams["ID"])>0 && $arPost=CBlogPost::GetByID($arParams["ID"]))
				{
					$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
					$arResult["Post"] = $arPost;
					if($arParams["SET_TITLE"]=="Y")
						$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], "".GetMessage("BLOG_POST_EDIT").""));
				}
				else
				{
					$arParams["ID"] = 0;
					if($arParams["SET_TITLE"]=="Y")
						$APPLICATION->SetTitle(str_replace("#BLOG#", $arBlog["NAME"], "".GetMessage("BLOG_NEW_MESSAGE").""));
				}
				if (($arResult["perms"] >= BLOG_PERMS_MODERATE || ($arResult["perms"] >= BLOG_PERMS_WRITE && ($arParams["ID"]==0 || $arPost["AUTHOR_ID"] == $arResult["UserID"]))) && ($arParams["ID"]==0 || $arPost["BLOG_ID"]==$arBlog["ID"]))
				{
					if (($_POST["apply"] || $_POST["save"] || $_POST["do_upload"] || $_POST["draft"]) && $arResult["preview"] != "Y")
					{
						if($bSoNet && empty($arBlog))
						{
							if(IntVal($arParams["GROUP_ID"]))
							{
								$arFields = array(
									"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
									"GROUP_ID" => IntVal($arParams["GROUP_ID"]),
									"ACTIVE" => "Y",
									"ENABLE_COMMENTS" => "Y",
									"ENABLE_IMG_VERIF" => "Y",
									"EMAIL_NOTIFY" => "Y",
									"ENABLE_RSS" => "Y",
									"ALLOW_HTML" => "N",
									"ENABLE_TRACKBACK" => "N",
									"SEARCH_INDEX" => "Y",
									"=DATE_CREATE" => $DB->CurrentTimeFunction(),
									"PERMS_POST" => Array( 
										1 => "I",
										2 => "I" ),
									"PERMS_COMMENT" => Array( 
										1 => "P",
										2 => "P" ),
								);
								
								if($bGroupMode)
								{
									$arGroupSoNet = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
									$arFields["NAME"] = GetMessage("BLG_GRP_NAME")." ".$arGroupSoNet["~NAME"];
									$arFields["URL"] = $arParams["SOCNET_GROUP_ID"]."-blog";
									$arFields["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];
								}
								else
								{
									$rsUser = CUser::GetByID($blogOwnerID);
									$arUser = $rsUser->Fetch();
									$arFields["NAME"] = GetMessage("BLG_NAME")." ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
									$arFields["URL"] = str_replace(" ", "_", $arUser["LOGIN"])."-blog";
									$arFields["OWNER_ID"] = $blogOwnerID;
									
									$urlCheck = preg_replace("/[^a-zA-Z0-9_-]/is", "", $arFields["URL"]);
									if ($urlCheck != $arFields["URL"])
									{
										$arFields["URL"] = "u".$blogOwnerID."-blog";
									}
								}
								$arFields["PATH"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arFields["URL"], "user_id" => $arFields["OWNER_ID"], "group_id" => $arFields["SOCNET_GROUP_ID"]));
								
								$blogID = CBlog::Add($arFields);
								$arBlog = CBlog::GetByID($blogID);
								BXClearCache(True, "/".SITE_ID."/blog/sonet/");
							}
						}
					}
					
				
					if ($_GET["image_upload"] || $_POST["do_upload"])
					{
						$arResult["imageUpload"] = "Y";
						$APPLICATION->RestartBuffer();
						header("Pragma: no-cache");
							
						if ($_POST["do_upload"] && $_FILES["FILE_ID"]["size"] > 0)
						{
							$arFields = array(
								"BLOG_ID"	=> $arBlog["ID"],
								"POST_ID"	=> $arParams["ID"],
								"USER_ID"	=> $arResult["UserID"],
								"=TIMESTAMP_X"	=> $DB->GetNowFunction(),
								"TITLE"		=> $_POST["IMAGE_TITLE"],
								"IMAGE_SIZE"	=> $_FILES["FILE_ID"]["size"]
							);
							$arImage=array_merge(
								$_FILES["FILE_ID"],
								array(
									"MODULE_ID" => "blog",
									"del" => "Y"
								)
							);
							$arFields["FILE_ID"] = $arImage;

							if ($imgID = CBlogImage::Add($arFields))
							{
								$aImg = CBlogImage::GetByID($imgID);
								$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
								$file = CFile::ShowImage($aImg["FILE_ID"], 100, 100, "id='".$aImg["ID"]."' border=0 style=cursor:pointer onclick=\"doInsert('[IMG ID=".$aImg["ID"]."]','',false, '".$aImg["ID"]."')\" title='".GetMessage("BLOG_P_INSERT")."'");
								$file = str_replace("'","\'",$file);
								$file = str_replace("\r"," ",$file);
								$file = str_replace("\n"," ",$file);
								$arResult["ImageModified"] = $file;
								$arResult["Image"] = $aImg;
							}
							else
							{
								if ($ex = $APPLICATION->GetException())
									$arResult["ERROR_MESSAGE"] = $ex->GetString()."<br />";
							}
						}
					}
					else
					{
					
						if (($_POST["apply"] || $_POST["save"]) && $arResult["preview"] != "Y" && empty($_POST["reset"])) // Save on button click
						{
							if(check_bitrix_sessid())
							{
								$TRACKBACK = trim($_POST["TRACKBACK"]);
								InitBVar($_POST["ENABLE_TRACKBACK"]);
								$CATEGORYtmp = Array();
								if(!empty($_POST["TAGS"]))
								{
									$dbCategory = CBlogCategory::GetList(Array(), Array("BLOG_ID" => $arBlog["ID"]));
									while($arCategory = $dbCategory->Fetch())
									{
										$arCatBlog[ToLower($arCategory["NAME"])] = $arCategory["ID"];
									}
									$tags = explode (",", $_POST["TAGS"]);
									foreach($tags as $tg)
									{
										$tg = trim($tg);
										if(!in_array($arCatBlog[ToLower($tg)], $CATEGORYtmp))
										{
											if(IntVal($arCatBlog[ToLower($tg)]) > 0)
											{
											
												$CATEGORYtmp[] = $arCatBlog[ToLower($tg)];
											}
											else
											{
											
												$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID" => $arBlog["ID"], "NAME" => $tg));
												BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/category/");
											}
										}
									}
								}
								elseif (!empty($_POST["CATEGORY_ID"]))
								{
									foreach($_POST["CATEGORY_ID"] as $v)
									{
										if(substr($v, 0, 4) == "new_")
										{
											$CATEGORYtmp[] = CBlogCategory::Add(array("BLOG_ID"=>$arBlog["ID"],"NAME"=>substr($v, 4
)));
											BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/category/");
										}
										else
											$CATEGORYtmp[] = $v;
									}
								}
								else
									$CATEGORY_ID = "";
								$CATEGORY_ID = implode(",", $CATEGORYtmp);
								
								if(strlen($_POST["DATE_PUBLISH_DEF"]) > 0)
									$DATE_PUBLISH = $_POST["DATE_PUBLISH_DEF"];
								elseif (strlen($_POST["DATE_PUBLISH"])<=0)
									$DATE_PUBLISH = ConvertTimeStamp(time(),"FULL");
								else
									$DATE_PUBLISH = $_POST["DATE_PUBLISH"];
								if(strlen($_POST["draft"]) > 0)
									$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_DRAFT;
								elseif(strlen($_POST["PUBLISH_STATUS"]) <= 0)
									$PUBLISH_STATUS = BLOG_PUBLISH_STATUS_PUBLISH;
								else
									$PUBLISH_STATUS = $_POST["PUBLISH_STATUS"];
									

								$arFields=array(
									"TITLE"			=> $_POST["POST_TITLE"],
									"DETAIL_TEXT"		=> (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]),
									"DETAIL_TEXT_TYPE"	=> (COption::GetOptionString("blog","allow_html", "N") == "Y" && $arBlog["ALLOW_HTML"] == "Y") ? $_POST["POST_MESSAGE_TYPE"] : "text",
									"DATE_PUBLISH"		=> $DATE_PUBLISH,
									"PUBLISH_STATUS"	=> $PUBLISH_STATUS,
									"ENABLE_TRACKBACK"	=> $_POST["ENABLE_TRACKBACK"],
									"CATEGORY_ID"		=> $CATEGORY_ID,
									"FAVORITE_SORT" => (IntVal($_POST["FAVORITE_SORT"]) > 0) ? IntVal($_POST["FAVORITE_SORT"]) : 0,
									"ATTACH_IMG" => "",
									"PATH" => CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => "#post_id#", "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"]))
								);
									
								if ($_POST["blog_perms"]==1)
								{
									if ($_POST["perms_p"][1] > BLOG_PERMS_READ)
										$_POST["perms_p"][1] = BLOG_PERMS_READ;
									//if ($_POST["perms_c"][1] > BLOG_PERMS_READ)
										//$_POST["perms_c"][1] = BLOG_PERMS_READ;

									$arFields["PERMS_POST"] = $_POST["perms_p"];
									$arFields["PERMS_COMMENT"] = $_POST["perms_c"];
								}
								else
								{
									$arFields["PERMS_POST"] = array();
									$arFields["PERMS_COMMENT"] = array();
								}

								if(is_array($_POST["IMAGE_ID_title"]))
								{
									foreach($_POST["IMAGE_ID_title"] as $imgID => $imgTitle)
									{
										$aImg = CBlogImage::GetByID($imgID);
										$aImg = CBlogTools::htmlspecialcharsExArray($aImg);
										if ($aImg["BLOG_ID"]==$arBlog["ID"] && $aImg["POST_ID"]==$arParams["ID"])
										{
											if ($_POST["IMAGE_ID_del"][$imgID])
											{
												CBlogImage::Delete($imgID);
												$arFields["DETAIL_TEXT"] = str_replace("[IMG ID=$imgID]","",$arFields["DETAIL_TEXT"]);
											}
											else
												CBlogImage::Update($imgID, array("TITLE"=>$imgTitle));
										}
									}
								}
								
								if (count($arParams["POST_PROPERTY"]) > 0)
								{
									$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arParams["ID"], LANGUAGE_ID);
									foreach ($arPostFields as $FIELD_NAME => $arPostField)
									{
										if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
											continue;
				
										if($arPostField["EDIT_IN_LIST"]=="Y")
										{
											if($arPostField["USER_TYPE"]["BASE_TYPE"]=="file")
											{
												$old_id = $_POST[$arPostField["FIELD_NAME"]."_old_id"];
												if(is_array($old_id))
												{
													$arFields[$arPostField["FIELD_NAME"]] = array();
													foreach($old_id as $key=>$value)
													{
														$arFields[$arPostField["FIELD_NAME"]][$key] = array(
															"name" => $_FILES[$arPostField["FIELD_NAME"]]["name"][$key],
															"type" => $_FILES[$arPostField["FIELD_NAME"]]["type"][$key],
															"tmp_name" => $_FILES[$arPostField["FIELD_NAME"]]["tmp_name"][$key],
															"error" => $_FILES[$arPostField["FIELD_NAME"]]["error"][$key],
															"size" => $_FILES[$arPostField["FIELD_NAME"]]["size"][$key],
															"del" => is_array($_POST[$arPostField["FIELD_NAME"]."_del"]) && in_array($value, $_POST[$arPostField["FIELD_NAME"]."_del"]),
															"old_id" => $value,
														);
													}
												}
												else
												{
													$arFields[$arPostField["FIELD_NAME"]] = $_FILES[$arPostField["FIELD_NAME"]];
													$arFields[$arPostField["FIELD_NAME"]]["del"] = $_POST[$arPostField["FIELD_NAME"]."_del"];
													$arFields[$arPostField["FIELD_NAME"]]["old_id"] = $old_id;
												}
											}
											else
											{
												$arFields[$arPostField["FIELD_NAME"]] = $_POST[$arPostField["FIELD_NAME"]];
											}
										}
									}
								}

								$bAdd = false;
								if ($arParams["ID"] > 0)
								{
									$arOldPost = CBlogPost::GetByID($arParams["ID"]);
									$newID = CBlogPost::Update($arParams["ID"], $arFields);
								}
								else
								{
									$arFields["=DATE_CREATE"] = $DB->GetNowFunction();
									$arFields["AUTHOR_ID"] = $arResult["UserID"];
									$arFields["BLOG_ID"] = $arBlog["ID"];
									
									$newID = CBlogPost::Add($arFields);
									$bAdd = true;
									$bNeedMail = false;
								}
								
								if($bAdd == true || ($arOldPost["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT && $arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_DRAFT))
								{
									if ($newID && $arBlog["EMAIL_NOTIFY"]=="Y" && $arResult["UserID"] != $arBlog["OWNER_ID"]) // Send notification to email
									{
										$bNeedMail = true;
										$BlogUser = CBlogUser::GetByID($arResult["UserID"], BLOG_BY_USER_ID);
										$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);
										$res = CUser::GetByID($arBlog["OWNER_ID"]);
										$arOwner = $res->GetNext();
										$dbUser = CUser::GetByID($arResult["UserID"]);
										$arUser = $dbUser->GetNext();
										$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]); 
										$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
										$text4mail = $arFields["DETAIL_TEXT"];
										if($arFields["DETAIL_TEXT_TYPE"] == "html")
											$text4mail = HTMLToTxt($text4mail);

										$arImages = Array();
										$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>0, "BLOG_ID"=>$arBlog['ID']));
										while ($arImage = $res->Fetch())
											$arImages[$arImage['ID']] = $arImage['FILE_ID'];
											
										$text4mail = $parserBlog->convert4mail($text4mail, $arImages);
										$dbSite = CSite::GetByID(SITE_ID);
										$arSite = $dbSite -> Fetch();
										$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
										if (strlen($serverName) <=0)
										{
											if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
												$serverName = SITE_SERVER_NAME;
											else
												$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
										}


										CEvent::Send(
											"NEW_BLOG_MESSAGE",
											SITE_ID,
											array(
												"BLOG_ID"		=> $arBlog["ID"],
												"BLOG_NAME"		=> htmlspecialcharsBack($arBlog["NAME"]),
												"BLOG_URL"		=> $arBlog["URL"],
												"MESSAGE_TITLE"	=> $arFields["TITLE"],
												"MESSAGE_TEXT"	=> $text4mail,
												"MESSAGE_DATE"	=> $arFields["DATE_PUBLISH"],
												"MESSAGE_PATH"	=> "http://".$serverName.CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST"]), array("blog" => $arBlog["URL"], "post_id" => $newID, "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])),
												"AUTHOR"		=> $AuthorName,
												"EMAIL_FROM"	=> COption::GetOptionString("main","email_from", "nobody@nobody.com"),
												"EMAIL_TO"		=> $arOwner["EMAIL"]
											)
										);
									}
									
									if($bSoNet)
									{
										if($arFields["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_DRAFT)
										{
											if (!$bNeedMail)
											{
												$BlogUser = CBlogUser::GetByID($arResult["UserID"], BLOG_BY_USER_ID);
												$BlogUser = CBlogTools::htmlspecialcharsExArray($BlogUser);
												$res = CUser::GetByID($arBlog["OWNER_ID"]);
												$arOwner = $res->GetNext();
												$dbUser = CUser::GetByID($arResult["UserID"]);
												$arUser = $dbUser->GetNext();
												$AuthorName = CBlogUser::GetUserName($BlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]); 
												$parserBlog = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
												$text4mail = $arFields["DETAIL_TEXT"];
												
												if($arFields["DETAIL_TEXT_TYPE"] == "html")
													$text4mail = HTMLToTxt($text4mail);
							
												$arImages = Array();
												$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>0, "BLOG_ID"=>$arBlog['ID']));
												while ($arImage = $res->Fetch())
													$arImages[$arImage['ID']] = $arImage['FILE_ID'];
													
												$text4mail = $parserBlog->convert4mail($text4mail, $arImages);
												$dbSite = CSite::GetByID(SITE_ID);
												$arSite = $dbSite -> Fetch();
												$serverName = htmlspecialcharsEx($arSite["SERVER_NAME"]);
												if (strlen($serverName) <=0)
												{
													if (defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME)>0)
														$serverName = SITE_SERVER_NAME;
													else
														$serverName = COption::GetOptionString("main", "server_name", "www.bitrixsoft.com");
												}
											}
											
											if($arFields["DETAIL_TEXT_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y" && $arBlog["ALLOW_HTML"] == "Y")
											{
												$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
												if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
													$arAllow["VIDEO"] = "N";
												$text4message = $parserBlog->convert($arFields["DETAIL_TEXT"], true, $arImages, $arAllow);
											}
											else
											{
												$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
												if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
													$arAllow["VIDEO"] = "N";
												$text4message = $parserBlog->convert($arFields["DETAIL_TEXT"], true, $arImages, $arAllow);
											}

											$arSoFields = Array(
													"EVENT_ID" => "blog",
													"=LOG_DATE" => $DB->CurrentTimeFunction(),
													//"TITLE_TEMPLATE" => GetMessage("BLG_SONET_TITLE"),
													//"TITLE" => $arFields["TITLE"],
													"TITLE_TEMPLATE" => $AuthorName." ".GetMessage("BLG_SONET_TITLE"),
													"TITLE" => $arFields['TITLE'],
													"MESSAGE" => $text4message,
													"TEXT_MESSAGE" => $text4mail,
													"MODULE_ID" => "blog",
													"CALLBACK_FUNC" => false
												);

											
											if($bGroupMode)
											{
												$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
												$arSoFields["ENTITY_ID"] = $arParams["SOCNET_GROUP_ID"];
												$arSoFields["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"], "post_id" => $newID));
											}
											else
											{
												$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_USER;
												$arSoFields["ENTITY_ID"] = $arParams["USER_ID"];
												$arSoFields["URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"], "post_id" => $newID));
											}
											CSocNetLog::Add($arSoFields);
										}
									}
								}
							
								if ($newID > 0) // Record saved successfully
								{
									CBlogPostCategory::DeleteByPostID($newID);
									foreach($CATEGORYtmp as $v)
										CBlogPostCategory::Add(Array("BLOG_ID" => $arBlog["ID"], "POST_ID" => $newID, "CATEGORY_ID"=>$v));
								
									$DB->Query("UPDATE b_blog_image SET POST_ID=".$newID." WHERE BLOG_ID=".$arBlog["ID"]." AND POST_ID=0", true);
									
									if (strlen($TRACKBACK)>0)
									{
										$arPingUrls = explode("\n",$TRACKBACK);
										CBlogTrackback::SendPing($newID, $arPingUrls);
									}
									$arParams["ID"] = $newID;
									
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/first_page/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/calendar/");
									BXClearCache(True, "/".SITE_ID."/blog/last_messages/");
									BXClearCache(True, "/".SITE_ID."/blog/commented_posts/");
									BXClearCache(True, "/".SITE_ID."/blog/popular_posts/");
									BXClearCache(True, "/".SITE_ID."/blog/last_comments/");
									BXClearCache(True, "/".SITE_ID."/blog/groups/".$arBlog["GROUP_ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/trackback/".$arParams["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/comment/".$arParams["ID"]."/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_out/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/rss_all/");
									BXClearCache(True, "/".SITE_ID."/blog/rss_sonet/");
									BXClearCache(True, "/".SITE_ID."/blog/rss_all/");
									BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/favorite/");
									
									if ($arParams["ID"] > 0)
										BXClearCache(True, "/".SITE_ID."/blog/".$arBlog["URL"]."/post/".$arParams["ID"]."/");
										
									if($bSoNet)
									{
										if($bGroupMode)
											CSocNetGroup::SetLastActivity($arParams["SOCNET_GROUP_ID"]);
									}

									if (strlen($_POST["apply"])<=0)
									{
										if($arFields["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT || strlen($_POST["draft"]) > 0)
											LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
										else
										{
											if($bGroupMode)
												LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
											else
												LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
										}
									}
									else
										LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST_EDIT"], array("blog" => $arBlog["URL"], "post_id"=>$newID, "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
								}
								else
								{
									if ($ex = $APPLICATION->GetException())
										$arResult["ERROR_MESSAGE"] = $ex->GetString()."<br />";
									else
										$arResult["ERROR_MESSAGE"] = "Error saving data to database.<br />";
								}
							}
							else
								$arResult["ERROR_MESSAGE"] = GetMessage("BPE_SESS");
						}
						elseif($_POST["reset"])
						{
							if($_POST["PUBLISH_STATUS"] == BLOG_PUBLISH_STATUS_DRAFT)
								LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DRAFT"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
							else
							{
								if($bGroupMode)
									LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
								else
									LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"], "user_id" => $arBlog["OWNER_ID"], "group_id" => $arParams["SOCNET_GROUP_ID"])));
							}
						}

						if ($arParams["ID"] > 0 && strlen($arResult["ERROR_MESSAGE"])<=0 && $arResult["preview"] != "Y") // Edit post
						{
							$arResult["PostToShow"]["TITLE"] = $arPost["TITLE"];
							$arResult["PostToShow"]["DETAIL_TEXT"] = $arPost["DETAIL_TEXT"];
							$arResult["PostToShow"]["~DETAIL_TEXT"] = $arPost["~DETAIL_TEXT"];
							$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] = $arPost["DETAIL_TEXT_TYPE"];
							$arResult["PostToShow"]["PUBLISH_STATUS"] = $arPost["PUBLISH_STATUS"];
							$arResult["PostToShow"]["ENABLE_TRACKBACK"] = $arPost["ENABLE_TRACKBACK"] == "Y";
							$arResult["PostToShow"]["ATTACH_IMG"] = $arPost["ATTACH_IMG"];
							$arResult["PostToShow"]["DATE_PUBLISH"] = $arPost["DATE_PUBLISH"];
							$arResult["PostToShow"]["CATEGORY_ID"] = $arPost["CATEGORY_ID"];
							$arResult["PostToShow"]["FAVORITE_SORT"] = $arPost["FAVORITE_SORT"];

							$res = CBlogUserGroupPerms::GetList(array("ID"=>"DESC"),array("BLOG_ID"=>$arBlog["ID"],"POST_ID"=>$arParams["ID"]));
							while($arPerms = $res->Fetch())
							{
								if ($arPerms["AUTOSET"]=="N")
									$arResult["PostToShow"]["ExtendedPerms"] = "Y";
								if ($arPerms["PERMS_TYPE"]=="P")
									$arResult["PostToShow"]["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
								elseif ($arPerms["PERMS_TYPE"]=="C")
									$arResult["PostToShow"]["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
							}
						}
						else
						{
							$arResult["PostToShow"]["TITLE"] = htmlspecialcharsEx($_POST["POST_TITLE"]);
							$arResult["PostToShow"]["CATEGORY_ID"] = $_POST["CATEGORY_ID"];
							$arResult["PostToShow"]["CategoryText"] = $_POST["TAGS"];
							$arResult["PostToShow"]["DETAIL_TEXT_TYPE"] = htmlspecialcharsEx($_POST["POST_MESSAGE_TYPE"]);
							$arResult["PostToShow"]["DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : htmlspecialcharsEx($_POST["POST_MESSAGE"]));
							$arResult["PostToShow"]["~DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : $_POST["POST_MESSAGE"]);
							$arResult["PostToShow"]["PUBLISH_STATUS"] = htmlspecialcharsEx($_POST["PUBLISH_STATUS"]);
							$arResult["PostToShow"]["ENABLE_TRACKBACK"] = htmlspecialcharsEx($_POST["ENABLE_TRACKBACK"]);
							$arResult["PostToShow"]["TRACKBACK"] = htmlspecialcharsEx($_POST["TRACKBACK"]);
							$arResult["PostToShow"]["DATE_PUBLISH"] = $_POST["DATE_PUBLISH"] ? htmlspecialcharsEx($_POST["DATE_PUBLISH"]) : ConvertTimeStamp(time(),"FULL");
							$arResult["PostToShow"]["FAVORITE_SORT"] = htmlspecialcharsEx($_POST["FAVORITE_SORT"]);
							
							if ($_POST["apply"] || $_POST["save"] || $arResult["preview"] == "Y")
							{
								$arResult["PostToShow"]["arUGperms_p"] = $_POST["perms_p"];
								$arResult["PostToShow"]["arUGperms_c"] = $_POST["perms_c"];
								$arResult["PostToShow"]["ExtendedPerms"] = (IntVal($_POST["blog_perms"])==1 ? "Y" : "N");
							}
							else
							{
								$res = CBlogUserGroupPerms::GetList(array("ID"=>"DESC"),array("BLOG_ID"=>$arBlog["ID"],"POST_ID"=>0));
								while($arPerms = $res->Fetch())
								{
									if ($arPerms["PERMS_TYPE"]=="P")
										$arResult["PostToShow"]["arUGperms_p"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
									elseif ($arPerms["PERMS_TYPE"]=="C")
										$arResult["PostToShow"]["arUGperms_c"][$arPerms["USER_GROUP_ID"]] = $arPerms["PERMS"];
								}
							}
						}
						$arResult["BLOG_POST_PERMS"] = $GLOBALS["AR_BLOG_POST_PERMS"];
						$arResult["BLOG_COMMENT_PERMS"] = $GLOBALS["AR_BLOG_COMMENT_PERMS"];
						$arResult["UserGroups"] = array();
						$res = CBlogUserGroup::GetList(array(),$arFilter=array("BLOG_ID"=>$arBlog["ID"]));
						while ($aUGroup = $res->GetNext())
							$arResult["UserGroups"][] = $aUGroup;


						$arSelectFields = array("ID", "SMILE_TYPE", "TYPING", "IMAGE", "DESCRIPTION", "CLICKABLE", "SORT", "IMAGE_WIDTH", "IMAGE_HEIGHT", "LANG_NAME");
						$total = 0;
						$arSmiles = array();
						$res = CBlogSmile::GetList(array("SORT"=>"ASC","ID"=>"DESC"), array("SMILE_TYPE"=>"S","CLICKABLE"=>"Y","LANG_LID"=>LANGUAGE_ID), false, false, $arSelectFields);
						while ($arr = $res->GetNext())
						{
							$total++;
							list($type)=explode(" ",$arr["TYPING"]);
							$arr["TYPE"]=str_replace("'","\'",$type);
							$arr["TYPE"]=str_replace("\\","\\\\",$type);
							$arSmiles[] = $arr;
						}
						$arResult["Smiles"] = $arSmiles;
						$arResult["SmilesCount"] = $total;
						
						$arFilter = array(
							"POST_ID"=>$arParams["ID"], 
							"BLOG_ID"=>$arBlog["ID"]
							);
						if ($arParams["ID"]==0)
							$arFilter["USER_ID"] = $arResult["UserID"];
					
						$arResult["Images"] = Array();;
						$res = CBlogImage::GetList(array("ID"=>"ASC"), $arFilter);
						while($aImg = $res->GetNext())
						{
							$aImg["FileShow"] = CFile::ShowImage($aImg["FILE_ID"], 100, 100, "id='".$aImg["ID"]."' border=0 style=cursor:pointer onclick=\"doInsert('[IMG ID=".$aImg["ID"]."]','',false, '".$aImg["ID"]."')\" title='".GetMessage("BLOG_P_INSERT")."'");
							$arResult["Images"][] = $aImg;
						}
						$i=0;
						
						if(strpos($arResult["PostToShow"]["CATEGORY_ID"], ",")!==false)
							$arResult["PostToShow"]["CATEGORY_ID"] = explode(",", $arResult["PostToShow"]["CATEGORY_ID"]);

						$arResult["Category"] = Array();
						
						
						if(strlen($arResult["PostToShow"]["CategoryText"]) <= 0)
						{
							$res = CBlogCategory::GetList(array("NAME"=>"ASC"),array("BLOG_ID"=>$arBlog["ID"]));
							while ($arCategory=$res->GetNext())
							{
								if(is_array($arResult["PostToShow"]["CATEGORY_ID"]))
								{
									if(in_array($arCategory["ID"], $arResult["PostToShow"]["CATEGORY_ID"]))
										$arCategory["Selected"] = "Y";
								}
								else
								{
									if($arCategory["ID"]==$arResult["PostToShow"]["CATEGORY_ID"])
										$arCategory["Selected"] = "Y";
								}
								if($arCategory["Selected"] == "Y")
									$arResult["PostToShow"]["CategoryText"] .= $arCategory["~NAME"].", ";

								$arResult["Category"][$arCategory["ID"]] = $arCategory;
							}
							$arResult["PostToShow"]["CategoryText"] = substr($arResult["PostToShow"]["CategoryText"], 0, strlen($arResult["PostToShow"]["CategoryText"])-2);
						}
						
						$arResult["POST_PROPERTIES"] = array("SHOW" => "N");
			
						if (!empty($arParams["POST_PROPERTY"]))
						{
							$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", $arParams["ID"], LANGUAGE_ID);
			
							if (count($arParams["POST_PROPERTY"]) > 0)
							{
								foreach ($arPostFields as $FIELD_NAME => $arPostField)
								{
									if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
										continue;
									$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
									$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
									$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
									$arResult["POST_PROPERTIES"]["DATA"][$FIELD_NAME] = $arPostField;
								}
							}
							if (!empty($arResult["POST_PROPERTIES"]["DATA"]))
								$arResult["POST_PROPERTIES"]["SHOW"] = "Y";
						}
						$arResult["CUR_PAGE"] = urlencode($APPLICATION->GetCurPageParam());

						if($arResult["preview"] == "Y")
						{
							$arResult["postPreview"]["TITLE"] = $arResult["PostToShow"]["TITLE"];
							$arResult["postPreview"]["CATEGORY_ID"] = $arResult["PostToShow"]["CATEGORY_ID"];
							$arResult["postPreview"]["DETAIL_TEXT"] = (($_POST["POST_MESSAGE_TYPE"] == "html")? $_POST["POST_MESSAGE_HTML"] : htmlspecialcharsEx($_POST["POST_MESSAGE"]));
							$arResult["postPreview"]["POST_MESSAGE_TYPE"] = htmlspecialcharsEx($_POST["POST_MESSAGE_TYPE"]);
							$arResult["postPreview"]["DATE_PUBLISH"] = $arResult["PostToShow"]["DATE_PUBLISH"];
							$arResult["postPreview"]["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arResult["postPreview"]["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
							$arResult["postPreview"]["FAVORITE_SORT"] = htmlspecialcharsEx($arResult["FAVORITE_SORT"]);
							
							if (!empty($_POST["CATEGORY_ID"]))
							{
								foreach($_POST["CATEGORY_ID"] as $v)
								{
									
									if(substr($v, 0, 4) == "new_")
										$arResult["Category"][$v] = Array("ID" => $v, "NAME" => substr($v, 4), "Selected" => "Y");
								}
							}

							$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
							
							$res = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost['ID'], "BLOG_ID"=>$arBlog['ID']));
							while ($arImage = $res->Fetch())
								$arImages[$arImage['ID']] = $arImage['FILE_ID'];
								
							if($arResult["postPreview"]["POST_MESSAGE_TYPE"] == "html" && COption::GetOptionString("blog","allow_html", "N") == "Y")
							{								
								$arAllow = array("HTML" => "Y", "ANCHOR" => "Y", "IMG" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y", "QUOTE" => "Y", "CODE" => "Y");
								if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
									$arAllow["VIDEO"] = "N";

								$arResult["postPreview"]["textFormated"] = $p->convert($arResult["postPreview"]["DETAIL_TEXT"], false, $arImages, $arAllow);
							}
							else
							{
								$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "Y");
								if(COption::GetOptionString("blog","allow_video", "Y") != "Y")
									$arAllow["VIDEO"] = "N";
								$arResult["postPreview"]["textFormated"] = $p->convert($arResult["postPreview"]["DETAIL_TEXT"], false, $arImages, $arAllow);
							}

							$arResult["postPreview"]["BlogUser"] = CBlogUser::GetByID($arResult["UserID"], BLOG_BY_USER_ID); 
							$arResult["postPreview"]["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arResult["postPreview"]["BlogUser"]);
							$dbUser = CUser::GetByID($arResult["UserID"]);
							$arResult["postPreview"]["arUser"] = $dbUser->GetNext();
							$arResult["postPreview"]["AuthorName"] = CBlogUser::GetUserName($arResult["postPreview"]["BlogUser"]["ALIAS"], $arResult["postPreview"]["arUser"]["NAME"], $arResult["postPreview"]["arUser"]["LAST_NAME"], $arResult["postPreview"]["arUser"]["LOGIN"]);
							
							$arResult["postPreview"]["BlogUser"]["AVATAR_file"] = CFile::GetFileArray($arResult["postPreview"]["BlogUser"]["AVATAR"]);
							if ($arResult["postPreview"]["BlogUser"]["AVATAR_file"] !== false)
								$arResult["postPreview"]["BlogUser"]["AVATAR_img"] = CFile::ShowImage($arResult["postPreview"]["BlogUser"]["AVATAR_file"]["SRC"], 150, 150, "border=0 align='right'");

							if(strlen($arResult["PostToShow"]["CategoryText"]) > 0)
							{
								$arCatTmp = explode(",", $arResult["PostToShow"]["CategoryText"]);
								if(is_array($arCatTmp))
								{
									foreach($arCatTmp as $v)
										$arResult["postPreview"]["Category"][] = Array("NAME" => trim($v));
								}
							}
							elseif(strlen($arResult["postPreview"]["CATEGORY_ID"])>0)
							{
								foreach($arResult["postPreview"]["CATEGORY_ID"] as $v)
								{
									if(strlen($v)>0)
									{
										$arResult["postPreview"]["Category"][] = $arResult["Category"][$v];
									}
								}
							}
						}
					}
				}
				else
					$arResult["FATAL_MESSAGE"] = GetMessage("BLOG_ERR_NO_RIGHTS");
			}
			else
				$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
	}
	else
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_MES_NO_BLOG");
	
$this->IncludeComponentTemplate();
?>