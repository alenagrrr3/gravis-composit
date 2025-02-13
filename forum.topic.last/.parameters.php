<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
		"FID" => CForumParameters::GetForumsMultiSelect(GetMessage("F_DEFAULT_FID"), "BASE"),
		"SORT_BY" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_ORD"),
			"TYPE" => "LIST",
			"DEFAULT" => "LAST_POST_DATE",
			"VALUES" => array(
				"TITLE" => GetMessage("F_SHOW_TITLE"),
				"USER_START_NAME" => GetMessage("F_SHOW_USER_START_NAME"),
				"POSTS" => GetMessage("F_SHOW_POSTS"),
				"VIEWS" => GetMessage("F_SHOW_VIEWS"),
				"LAST_POST_DATE" => GetMessage("F_SHOW_LAST_POST_DATE"))),
		"SORT_ORDER" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_BY"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" =>  Array("ASC"=>GetMessage("F_DESC_ASC"), "DESC"=>GetMessage("F_DESC_DESC"))),
		"SORT_BY_SORT_FIRST" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORT_BY_SORT_FIRST"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),

		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		
		"TOPICS_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10"))),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SHOW_FORUM_ANOTHER_SITE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SHOW_FORUM_ANOTHER_SITE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DISPLAY_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
CForumParameters::AddPagerSettings($arComponentParameters, GetMessage("F_TOPICS"));
?>
