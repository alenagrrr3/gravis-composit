<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
$userProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_BLOG", 0, LANGUAGE_ID);
$blogProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$blogProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", 0, LANGUAGE_ID);
$postProp = array();
if (!empty($arRes))
{
	foreach ($arRes as $key => $val)
		$postProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
}

$arComponentParameters = array(
	"PARAMETERS" => array( 
		"VARIABLE_ALIASES" => Array(
			"blog" => Array(
					"NAME" => GetMessage("BC_BLOG_VAR"),
					"DEFAULT" => "blog",
					),
			"post_id" => Array(
					"NAME" => GetMessage("BC_POST_VAR"),
					"DEFAULT" => "id",
					),
			"user_id" => Array(
					"NAME" => GetMessage("BC_USER_VAR"),
					"DEFAULT" => "id",
					),
			"page" => Array(
					"NAME" => GetMessage("BC_PAGE_VAR"),
					"DEFAULT" => "page",
					),
			"group_id" => Array(
					"NAME" => GetMessage("BC_GROUP_VAR"),
					"DEFAULT" => "id",
					),
			),
		"SEF_MODE" => Array(
			"index" => array(
				"NAME" => GetMessage("BC_SEF_PATH_INDEX"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array(),
			),
			"group" => array(
				"NAME" => GetMessage("BC_SEF_PATH_GROUP"),
				"DEFAULT" => "group/#group_id#.php",
				"VARIABLES" => array("group_id"),
			),
			"blog" => array(
				"NAME" => GetMessage("BC_SEF_PATH_BLOG"),
				"DEFAULT" => "#blog#/",
				"VARIABLES" => array("blog"),
			),
			"user" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER"),
				"DEFAULT" => "user/#user_id#.php",
				"VARIABLES" => array("user_id"),
			),
			"user_friends" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_FRIENDS"),
				"DEFAULT" => "friends/#user_id#.php",
				"VARIABLES" => array("user_id"),
			),
			"search" => array(
				"NAME" => GetMessage("BC_SEF_PATH_SEARCH"),
				"DEFAULT" => "search.php",
				"VARIABLES" => array(),
			),
			"user_settings" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_SETTINGS"),
				"DEFAULT" => "#blog#/user_settings.php",
				"VARIABLES" => array("blog"),
			),
			"user_settings_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_USER_SETTINGS_EDIT"),
				"DEFAULT" => "#blog#/user_settings_edit.php?id=#user_id#",
				"VARIABLES" => array("blog", "user_id"),
			),
			"group_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_GROUP_EDIT"),
				"DEFAULT" => "#blog#/group_edit.php",
				"VARIABLES" => array("blog"),
			),
			"blog_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_BLOG_EDIT"),
				"DEFAULT" => "#blog#/blog_edit.php",
				"VARIABLES" => array("blog"),
			),
			"category_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_CATEGORY_EDIT"),
				"DEFAULT" => "#blog#/category_edit.php",
				"VARIABLES" => array("blog"),
			),
			"post_edit" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST_EDIT"),
				"DEFAULT" => "#blog#/post_edit.php?id=#post_id#",
				"VARIABLES" => array("blog", "post_id"),
			),
			"draft" => array(
				"NAME" => GetMessage("BC_SEF_PATH_DRAFT"),
				"DEFAULT" => "#blog#/draft.php",
				"VARIABLES" => array("blog"),
			),
			"trackback" => array(
				"NAME" => GetMessage("BC_SEF_PATH_TRACKBACK"),
				"DEFAULT" => "={POST_FORM_ACTION_URI.'&blog=#blog#&id=#post_id#&page=trackback'}",
				"VARIABLES" => array("blog", "post_id"),
			),
			"post" => array(
				"NAME" => GetMessage("BC_SEF_PATH_POST"),
				"DEFAULT" => "#blog#/#post_id#.php",
				"VARIABLES" => array("blog", "post_id"),
			),
			"rss" => array(
				"NAME" => GetMessage("BC_SEF_PATH_RSS"),
				"DEFAULT" => "#blog#/rss/#type#",
				"VARIABLES" => array("blog", "type"),
			),
			"rss_all" => array(
				"NAME" => GetMessage("BC_SEF_PATH_RSS_ALL"),
				"DEFAULT" => "rss/#type#/#group_id#",
				"VARIABLES" => array("type", "group_id"),
			),
		),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BC_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/bitrix/images/blog/smile/",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"SET_TITLE" => Array(),
		"CACHE_TIME_LONG"	=>	array(
			"NAME" => GetMessage("BC_CACHE_TIME_LONG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "604800",
			"COLS" => 25,
			"PARENT" => "CACHE_SETTINGS",
		),
		"SET_NAV_CHAIN" => Array(
		  	"NAME" => GetMessage("BC_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BC_MESSAGE_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"PARENT" => "VISUAL",
			),		
		"PERIOD_DAYS" => Array(
				"NAME" => GetMessage("BC_PERIOD_DAYS"),
				"TYPE" => "STRING",
				"DEFAULT" => 30,
				"PARENT" => "VISUAL",
			),
		"MESSAGE_COUNT_MAIN" => Array(
				"NAME" => GetMessage("BC_MESSAGE_COUNT_MAIN"),
				"TYPE" => "STRING",
				"DEFAULT" => "6",
				"PARENT" => "VISUAL",
			),
		"BLOG_COUNT_MAIN" => Array(
				"NAME" => GetMessage("BC_BLOG_COUNT_MAIN"),
				"TYPE" => "STRING",
				"DEFAULT" => "6",
				"PARENT" => "VISUAL",
			),
		"COMMENTS_COUNT" => Array(
				"NAME" => GetMessage("BC_COMMENTS_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
				"PARENT" => "VISUAL",
			),
		"MESSAGE_LENGTH" => Array(
				"NAME" => GetMessage("BC_MESSAGE_LENTH"),
				"TYPE" => "STRING",
				"DEFAULT" => "100",
				"PARENT" => "VISUAL",
			),
		"BLOG_COUNT" => Array(
				"NAME" => GetMessage("BC_BLOG_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 20,
				"PARENT" => "VISUAL",
			),
		"DATE_TIME_FORMAT" => Array(
				"PARENT" => "VISUAL",
				"NAME" => GetMessage("BC_DATE_TIME_FORMAT"),
				"TYPE" => "LIST",
				"VALUES" => CBlogTools::GetDateTimeFormat(),
				"MULTIPLE" => "N",
				"DEFAULT" => $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")),	
				"ADDITIONAL_VALUES" => "Y",
			),		
		"NAV_TEMPLATE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("BB_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"USER_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("USER_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $userProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"BLOG_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BLOG_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $blogProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"BLOG_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("BLOG_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $blogProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"POST_PROPERTY"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"POST_PROPERTY_LIST"=>array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("POST_PROPERTY_LIST"),
			"TYPE" => "LIST",
			"VALUES" => $postProp,
			"MULTIPLE" => "Y",
			"DEFAULT" => array(),	
		),
		"AJAX_MODE" => Array(),
		"USE_ASC_PAGING" => Array(
		  	"NAME" => GetMessage("BC_USE_ASC_PAGING"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "N",
			"DEFAULT" =>"",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"NOT_USE_COMMENT_TITLE" => Array(
		  	"NAME" => GetMessage("BC_NOT_USE_COMMENT_TITLE"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
	),
); 
?>
