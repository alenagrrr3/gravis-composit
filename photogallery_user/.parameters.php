<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

$arIBlockType = array();
$rsIBlockType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
while ($arr=$rsIBlockType->Fetch())
{
	if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
	{
		$arIBlockType[$arr["ID"]] = "[".$arr["ID"]."] ".$ar["NAME"];
	}
}

$arIBlock=array();
$rsIBlock = CIBlock::GetList(Array("sort" => "asc"), Array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE"=>"Y"));
while($arr=$rsIBlock->Fetch())
{
	$arIBlock[$arr["ID"]] = "[".$arr["ID"]."] ".$arr["NAME"];
}

$arUGroupsEx = Array();
$dbUGroups = CGroup::GetList($by = "c_sort", $order = "asc");
while($arUGroups = $dbUGroups -> Fetch())
{
	if ($arUGroups["ANONYMOUS"] != "Y")
		$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}
$res = unserialize(COption::GetOptionString("photogallery", "pictures"));
$arSights = array();
if (is_array($res))
{
	foreach ($res as $key => $val)
	{
		$arSights[str_pad($key, 5, "_").$val["code"]] = $val["title"];
	}
}
$arFiles = array(
	"" => "...");
$path = str_replace(array("\\", "//"), "/", $_SERVER['DOCUMENT_ROOT']."/".BX_ROOT."/modules/photogallery/fonts/");
CheckDirPath($path);
$handle = opendir($path);
$file_exist = false;
if ($handle)
{
	while($file = readdir($handle)) 
	{
		if ($file == "." || $file == ".." || !is_file($path.$file))
			continue;
		$file_exist = true;
		$arFiles[$file] = $file;
	}
}
if (!$file_exist)
{
	$arFiles = array(
		"" => GetMessage("P_FONTS_NONE"));
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PAGE_SETTINGS" => array("NAME" => GetMessage("P_PAGE_SETTINGS"), "SORT" => "100"),
		"A_PHOTO_SETTINGS" => array("NAME" => GetMessage("P_PHOTO_SETTINGS"), "SORT" => "150"),
		"RATING_SETTINGS" => array("NAME" => GetMessage("T_IBLOCK_DESC_RATING_SETTINGS")),
		"TAGS_CLOUD" => array("NAME" => GetMessage("T_TAGS_CLOUD"))),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y"),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_IBLOCK"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "Y",
			"VALUES" => $arIBlock),
		"GALLERY_GROUPS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_GALLERY_GROUPS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => Array(1),
			"MULTIPLE" => "Y"),
		"ONLY_ONE_GALLERY" => array(
			"PARENT" => "BASE",
	        "NAME" => GetMessage("P_ONLY_ONE_GALLERY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
/*		"GALLERY_SIZE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "150"),
*/
		"SECTION_SORT_BY" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ID" => "ID",
				"NAME" => GetMessage("IBLOCK_SORT_NAME"),
				"SORT" => GetMessage("IBLOCK_SORT_SORT"), 
				"ELEMENTS_CNT" => GetMessage("IBLOCK_SORT_ELEMENTS_CNT"),
				"UF_DATE" => GetMessage("IBLOCK_SORT_DATE")),
			"DEFAULT" => array("UF_DATE")),
		"SECTION_SORT_ORD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"ASC" => GetMessage("IBLOCK_SORT_ASC"),
				"DESC" => GetMessage("IBLOCK_SORT_DESC")),
			"DEFAULT" => array("DESC")),
		"ELEMENT_SORT_FIELD" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_FIELD"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"shows" => GetMessage("IBLOCK_SORT_SHOWS"),
				"sort" => GetMessage("IBLOCK_SORT_SORT"),
				"timestamp_x" => GetMessage("IBLOCK_SORT_TIMESTAMP"),
				"name" => GetMessage("IBLOCK_SORT_NAME"),
				"id" => GetMessage("IBLOCK_SORT_ID"),
				"rating" => GetMessage("IBLOCK_SORT_RATING"),
				"comments" => GetMessage("IBLOCK_SORT_COMMENTS")),
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "id"),
		"ELEMENT_SORT_ORDER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"asc" => GetMessage("IBLOCK_SORT_ASC"),
				"desc" => GetMessage("IBLOCK_SORT_DESC")),
			"DEFAULT" => "desc"),
	
		"VARIABLE_ALIASES" => Array(
			"USER_ID" => Array("NAME" => GetMessage("USER_ID_DESC")),
			"USER_ALIAS" => Array("NAME" => GetMessage("USER_ALIAS_DESC")),
			"SECTION_ID" => Array("NAME" => GetMessage("SECTION_ID_DESC")),
			"ELEMENT_ID" => Array("NAME" => GetMessage("ELEMENT_ID_DESC")),
			"PAGE_NAME" => Array("NAME" => GetMessage("PAGE_NAME_DESC")),
			"ACTION" => Array("NAME" => GetMessage("ACTION_DESC"))),
			
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("SECTIONS_TOP_PAGE"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()),
				
			"galleries" => array(
				"NAME" => GetMessage("GALLERIES_PAGE"),
				"DEFAULT" => "galleries/#USER_ID#/",
				"VARIABLES" => array()),
			"gallery" => array(
				"NAME" => GetMessage("GALLERY_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/",
				"VARIABLES" => array("USER_ALIAS")),
			"gallery_edit" => array(
				"NAME" => GetMessage("GALLERY_EDIT_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/action/#ACTION#/",
				"VARIABLES" => array("USER_ALIAS", "ACTION")),
				
			"section" => array(
				"NAME" => GetMessage("SECTION_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID")),
			"section_edit" => array(
				"NAME" => GetMessage("SECTION_EDIT_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/action/#ACTION#/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID", "ACTION")),
			"section_edit_icon" => array(
				"NAME" => GetMessage("SECTION_EDIT_ICON_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/icon/action/#ACTION#/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID", "ACTION")),

			"upload" => array(
				"NAME" => GetMessage("UPLOAD_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/action/upload/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID")),
			"detail" => array(
				"NAME" => GetMessage("DETAIL_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID", "ELEMENT_ID")),
			"detail_edit" => array(
				"NAME" => GetMessage("DETAIL_EDIT_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/action/#ACTION#/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID", "ELEMENT_ID")),
			"detail_slide_show" => array(
				"NAME" => GetMessage("DETAIL_SLIDE_SHOW_PAGE"),
				"DEFAULT" => "#USER_ALIAS#/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
				"VARIABLES" => array("USER_ALIAS", "SECTION_ID", "ELEMENT_ID")),
			"detail_list" => array(
				"NAME" => GetMessage("DETAIL_LIST_PAGE"),
				"DEFAULT" => "list/",
				"VARIABLES" => array())),

		"SECTION_PAGE_ELEMENTS" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_SECTION_PAGE_ELEMENT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "10"),
		"ELEMENTS_PAGE_ELEMENTS" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("IBLOCK_ELEMENTS_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => '100'),
		"PAGE_NAVIGATION_TEMPLATE" => array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("P_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"ELEMENTS_USE_DESC_PAGE" => Array(
			"PARENT" => "PAGE_SETTINGS",
			"NAME" => GetMessage("T_ELEMENTS_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
			
		"DATE_TIME_FORMAT_SECTION" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT_SECTION"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT_DETAIL" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT_DETAIL"), "ADDITIONAL_SETTINGS"),
			
		"UPLOAD_MAX_FILE_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => str_replace("#upload_max_filesize#", ini_get('upload_max_filesize'), GetMessage("P_UPLOAD_MAX_FILE_SIZE")),
			"TYPE" => "STRING",
			"DEFAULT" => ini_get('upload_max_filesize')),
		"GALLERY_AVATAR_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_AVATAR_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "50"),
		"ALBUM_PHOTO_THUMBS_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "100"),
		"ALBUM_PHOTO_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ALBUM_PHOTO_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "100"),
		"THUMBS_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_THUMBS_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "120"),
		"PREVIEW_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PREVIEW_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "600"),
		"JPEG_QUALITY1" => Array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY1"),
			"TYPE" => "STRING",
			"DEFAULT" => "95"),
		"JPEG_QUALITY2" => Array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY2"),
			"TYPE" => "STRING",
			"DEFAULT" => "95"),
		"JPEG_QUALITY" => Array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_JPEG_QUALITY"),
			"TYPE" => "STRING",
			"DEFAULT" => "90"),
		"WATERMARK_MIN_PICTURE_SIZE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_WATERMARK_MIN_PICTURE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "200"),
		"ADDITIONAL_SIGHTS" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
			"TYPE" => "LIST",
			"VALUES" => $arSights,
			"DEFAULT" => array(),
			"MULTIPLE" => "Y"),
		"UPLOAD_MAX_FILE" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_UPLOAD_MAX_FILE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"1" => "1",
				"2" => "2",
				"3" => "3",
				"4" => "4",
				"5" => "5",
				"6" => "6",
				"7" => "7",
				"8" => "8",
				"9" => "9",
				"10" => "10"),
			"DEFAULT" => array("2"),
			"MULTIPLE" => "N"),
		"PATH_TO_FONT" => array(
			"PARENT" => "A_PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PATH_TO_FONT"),
			"TYPE" => "LIST",
			"VALUES" => $arFiles,
			"DEFAULT" => array(""),
			"MULTIPLE" => "N"),

		"USE_RATING" => Array(
			"PARENT" => "RATING_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_RATING"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"),
		"SHOW_TAGS" => array(
			"PARENT" => "TAGS_CLOUD",
	        "NAME" => GetMessage("P_SHOW_TAGS"),
			"TYPE" => "CHECKBOX",
			"REFRESH" => (IsModuleInstalled("search") ? "Y" : "N"),
			"DEFAULT" => "N"),
		
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"SET_TITLE" => Array(),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		)
	);

if($arCurrentValues["USE_PERMISSIONS"]!="Y")
	unset($arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"]);

if($arCurrentValues["USE_RATING"]=="Y")
{
	$arComponentParameters["PARAMETERS"]["MAX_VOTE"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_MAX_VOTE"),
		"TYPE" => "STRING",
		"DEFAULT" => "5");
	$arComponentParameters["PARAMETERS"]["VOTE_NAMES"] = array(
		"PARENT" => "RATING_SETTINGS",
		"NAME" => GetMessage("IBLOCK_VOTE_NAMES"),
		"TYPE" => "STRING",
		"VALUES" => array(),
		"MULTIPLE" => "Y",
		"DEFAULT" => array("1","2","3","4","5"),
		"ADDITIONAL_VALUES" => "Y");
}

if (IsModuleInstalled("blog") || IsModuleInstalled("forum"))
{
	$arComponentParameters["GROUPS"]["REVIEW_SETTINGS"] = array(
		"NAME" => GetMessage("T_IBLOCK_DESC_REVIEW_SETTINGS"));

	$arComponentParameters["PARAMETERS"]["USE_COMMENTS"] = array(
			"PARENT" => "REVIEW_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_COMMENTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y");

	if ($arCurrentValues["USE_COMMENTS"]=="Y")
	{
		$arr = array();
		if (IsModuleInstalled("blog"))
			$arr["blog"] = GetMessage("P_COMMENTS_TYPE_BLOG");
		if (IsModuleInstalled("forum"))
			$arr["forum"] = GetMessage("P_COMMENTS_TYPE_FORUM");

		$arComponentParameters["PARAMETERS"]["COMMENTS_TYPE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_COMMENTS_TYPE"),
				"TYPE" => "LIST",
				"VALUES" => $arr,
				"DEFAULT" => "blog",
				"REFRESH" => "Y");

		$arCurrentValues["COMMENTS_TYPE"] = ($arCurrentValues["COMMENTS_TYPE"] == "forum" ? "forum" : "blog");

		if (IsModuleInstalled("blog") && $arCurrentValues["COMMENTS_TYPE"]=="blog")
		{
			$arBlogs = array();
			if(CModule::IncludeModule("blog"))
			{
				$rsBlog = CBlog::GetList();
				while($arBlog=$rsBlog->Fetch())
				{
					$arBlogs[$arBlog["URL"]] = $arBlog["NAME"];
					$url = $arBlog["URL"];
				}
			}

			$arComponentParameters["PARAMETERS"]["BLOG_URL"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_BLOG_URL"),
				"TYPE" => "LIST",
				"VALUES" => $arBlogs,
				"DEFAULT" => $url);
			$arComponentParameters["PARAMETERS"]["COMMENTS_COUNT"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25);
			$arComponentParameters["PARAMETERS"]["PATH_TO_USER"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_PATH_TO_USER"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["PATH_TO_BLOG"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("P_PATH_TO_BLOG"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/blog/smile/");
		}
		elseif (IsModuleInstalled("forum") && $arCurrentValues["COMMENTS_TYPE"]=="forum")
		{
			$arForum = array();
			$fid = 0;
			if (CModule::IncludeModule("forum"))
			{
				$db_res = CForumNew::GetList(array(), array());
				if ($db_res && ($res = $db_res->GetNext()))
				{
					do
					{
						$arForum[intVal($res["ID"])] = $res["NAME"];
						$fid = intVal($res["ID"]);
					}while ($res = $db_res->GetNext());
				}
			}
			$arComponentParameters["PARAMETERS"]["FORUM_ID"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_FORUM_ID"),
				"TYPE" => "LIST",
				"VALUES" => $arForum,
				"DEFAULT" => $fid);
			$arComponentParameters["PARAMETERS"]["PATH_TO_SMILE"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PATH_TO_SMILE"),
				"TYPE" => "STRING",
				"DEFAULT" => "/bitrix/images/forum/smile/");
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_READ"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_READ_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["URL_TEMPLATES_PROFILE_VIEW"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
				"TYPE" => "STRING",
				"DEFAULT" => "");
			$arComponentParameters["PARAMETERS"]["USE_CAPTCHA"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_USE_CAPTCHA"),
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"DEFAULT" => "Y");
			$arComponentParameters["PARAMETERS"]["SHOW_LINK_TO_FORUM"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_SHOW_LINK_TO_FORUM"),
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"DEFAULT" => "Y");
			$arComponentParameters["PARAMETERS"]["PREORDER"] = Array(
				"PARENT" => "REVIEW_SETTINGS",
				"NAME" => GetMessage("F_PREORDER"),
				"TYPE" => "CHECKBOX",
				"MULTIPLE" => "N",
				"DEFAULT" => "Y");
		}
	}
}

if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["search"] = array(
		"NAME" => GetMessage("SEARCH_PAGE"),
		"DEFAULT" => "search/",
		"VARIABLES" => array());
	$arComponentParameters["PARAMETERS"]["SEF_MODE"]["tags"] = array(
		"NAME" => GetMessage("TAGS_PAGE"),
		"DEFAULT" => "tags/",
		"VARIABLES" => array());

	if($arCurrentValues["SHOW_TAGS"]=="Y")
	{
		$arComponentParameters["PARAMETERS"]["TAGS_PAGE_ELEMENTS"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => "50");
		$arComponentParameters["PARAMETERS"]["TAGS_PERIOD"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_PERIOD"),
			"TYPE" => "STRING",
			"DEFAULT" => "");
		$arComponentParameters["PARAMETERS"]["TAGS_INHERIT"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_TAGS_INHERIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
		$arComponentParameters["PARAMETERS"]["TAGS_FONT_MAX"] = array(
			"PARENT" => "TAGS_CLOUD",
			"NAME" => GetMessage("SEARCH_FONT_MAX"),
			"TYPE" => "STRING",
			"DEFAULT" => "30");
		$arComponentParameters["PARAMETERS"]["TAGS_FONT_MIN"] = array(
			"NAME" => GetMessage("SEARCH_FONT_MIN"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "14");
		$arComponentParameters["PARAMETERS"]["TAGS_COLOR_NEW"] = array(
	    	"NAME" => GetMessage("SEARCH_COLOR_NEW"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "486DAA");
		$arComponentParameters["PARAMETERS"]["TAGS_COLOR_OLD"] = array(
			"NAME" => GetMessage("SEARCH_COLOR_OLD"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "STRING",
			"DEFAULT" => "486DAA");
		$arComponentParameters["PARAMETERS"]["TAGS_SHOW_CHAIN"] = array(
			"NAME" => GetMessage("SEARCH_SHOW_CHAIN"),
			"PARENT" => "TAGS_CLOUD",
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y");
	}
}
if (IsModuleInstalled("socialnetwork"))
{
	$arComponentParameters["PARAMETERS"]["ANALIZE_SOCNET_PERMISSION"] = Array(
	"PARENT" => "BASE",
	"NAME" => GetMessage("F_ANALIZE_SOCNET_PERMISSION"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N");
}
?>