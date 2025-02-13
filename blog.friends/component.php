<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("blog"))
{
	ShowError(GetMessage("BLOG_MODULE_NOT_INSTALL"));
	return;
}

$arParams["ID"] = IntVal($arParams["ID"]);

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("B_B_FR_TITLE"));

$arParams["MESSAGE_COUNT"] = IntVal($arParams["MESSAGE_COUNT"])>0 ? IntVal($arParams["MESSAGE_COUNT"]): 20;
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
	$arParams["PATH_TO_BLOG_CATEGORY"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=blog&".$arParams["BLOG_VAR"]."=#blog#"."&calegory=#category#");
	
$arParams["PATH_TO_POST"] = trim($arParams["PATH_TO_POST"]);
if(strlen($arParams["PATH_TO_POST"])<=0)
	$arParams["PATH_TO_POST"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=post&".$arParams["BLOG_VAR"]."=#blog#&".$arParams["POST_VAR"]."=#post_id#");

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if(strlen($arParams["PATH_TO_USER"])<=0)
	$arParams["PATH_TO_USER"] = htmlspecialchars($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_SMILE"] = strlen(trim($arParams["PATH_TO_SMILE"]))<=0 ? false : trim($arParams["PATH_TO_SMILE"]);
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")) : $arParams["DATE_TIME_FORMAT"]);

if(IntVal($arParams["ID"])>0)
{
	$arBlogUser = CBlogUser::GetByID($arParams["ID"], BLOG_BY_USER_ID);
	$arBlogUser = CBlogTools::htmlspecialcharsExArray($arBlogUser);

	if ($arBlogUser)
	{
		if ($USER->IsAuthorized()
			&& $USER->GetID() == $arBlogUser["USER_ID"])
		{
			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(GetMessage("B_B_FR_TITLES"));
		}
		else
		{
			$dbUser = CUser::GetByID($arBlogUser["USER_ID"]);
			$arUser = $dbUser->GetNext();

			if($arParams["SET_TITLE"]=="Y")
				$APPLICATION->SetTitle(str_replace("#NAME#", CBlogUser::GetUserName($arBlogUser["ALIAS"], $arUser["NAME"], $arUser["LAST_NAME"], $arUser["LOGIN"]), GetMessage("B_B_FR_TITLE_OF")));
		}

		$dbList = CBlogUser::GetUserFriendsList($arParams["ID"], $USER->GetID(), $USER->IsAuthorized(), $arParams["MESSAGE_COUNT"]);
		$arResult["FRIENDS_POSTS"] = Array();
		while($arList = $dbList->Fetch())
		{
			$arPost = CBlogPost::GetByID($arList["ID"]);
			$arPost = CBlogTools::htmlspecialcharsExArray($arPost);
			$arBlog = CBlog::GetByID($arPost["BLOG_ID"]);
			$arBlog = CBlogTools::htmlspecialcharsExArray($arBlog);
			$arPost["urlToPost"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_POST"], array("blog" => $arBlog["URL"], "post_id"=>$arPost["ID"]));
			$arPost["urlToAuthor"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arPost["AUTHOR_ID"]));
			if($arPost["AUTHOR_ID"] == $arBlog["OWNER_ID"])
			{
				$arPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arBlog["URL"]));
			}
			else
			{
				$arOwnerBlog = CBlog::GetByOwnerID($arPost["AUTHOR_ID"]);
                                $arPost["urlToBlog"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG"], array("blog" => $arOwnerBlog["URL"]));
			}
			
			$p = new blogTextParser(false, $arParams["PATH_TO_SMILE"]);
			$arImage = array();
			$dbImage = CBlogImage::GetList(array("ID"=>"ASC"),array("POST_ID"=>$arPost["ID"], "BLOG_ID"=>$arBlog["ID"]));
			while ($arImage = $dbImage->Fetch())
				$arImages[$arImage["ID"]] = $arImage["FILE_ID"];
				
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
			
			$arPost["BlogUser"] = CBlogUser::GetByID($arPost["AUTHOR_ID"], BLOG_BY_USER_ID); 
			$arPost["BlogUser"] = CBlogTools::htmlspecialcharsExArray($arPost["BlogUser"]);
			$dbUser = CUser::GetByID($arPost["AUTHOR_ID"]);
			$arPost["arUser"] = $dbUser->GetNext();
			$arPost["AuthorName"] = CBlogUser::GetUserName($arPost["BlogUser"]["ALIAS"], $arPost["arUser"]["NAME"], $arPost["arUser"]["LAST_NAME"], $arPost["arUser"]["LOGIN"]);
			
			if (preg_match("/(\[CUT\])/i",$arPost["DETAIL_TEXT"]) || preg_match("/(<CUT>)/i",$arPost["DETAIL_TEXT"]))
				$arPost["CUT"] = "Y";

			if(strlen($arPost["CATEGORY_ID"])>0)
			{
				$arCategory = explode(",",$arPost["CATEGORY_ID"]);
				foreach($arCategory as $v)
				{
					if(IntVal($v)>0)
					{
						$arCatTmp = CBlogTools::htmlspecialcharsExArray(CBlogCategory::GetByID($v));
						$arCatTmp["urlToCategory"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_BLOG_CATEGORY"], array("blog" => $arBlog["URL"], "category_id" => $v));
						$arPost["Category"][] = $arCatTmp;
					}
				}
			}
			$arPost["DATE_PUBLISH_FORMATED"] = date($arParams["DATE_TIME_FORMAT"], MakeTimeStamp($arPost["DATE_PUBLISH"], CSite::GetDateFormat("FULL")));
			$arResult["FRIENDS_POSTS"][] = array("POST" => $arPost, "BLOG" => $arBlog);
		}
	}
	else
		$arResult["FATAL_MESSAGE"] = GetMessage("B_B_FR_NO_USER");
}
else
	$arResult["FATAL_MESSAGE"] = GetMessage("B_B_FR_NO_USER");

$this->IncludeComponentTemplate();
?>