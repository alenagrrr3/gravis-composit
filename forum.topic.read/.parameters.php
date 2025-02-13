<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	"PARAMETERS" => array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"TID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TID"]}'),
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
			
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
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_MESSAGE_MOVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_MOVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_move.php?FID=#FID#&TID=#TID#&MID_ARRAY=#MID_ARRAY#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		"URL_TEMPLATES_TOPIC_NEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_NEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_new.php?FID=#FID#"),
		"URL_TEMPLATES_SUBSCR_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_SUBSCR_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "subscr_list.php?FID=#FID#"),
		"URL_TEMPLATES_TOPIC_MOVE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_TOPIC_MOVE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_move.php?FID=#FID#&TID=#TID#"),	
		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_PM_EDIT" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PM_EDIT_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "pm_edit.php"),
		"URL_TEMPLATES_MESSAGE_SEND" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_SEND_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message_send.php?UID=#UID#"),
		"URL_TEMPLATES_RSS" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RSS_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rss.php?TYPE=#TYPE#&MODE=#MODE#&IID=#IID#"),
		"URL_TEMPLATES_USER_POST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_USER_POST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "user_post.php?UID=#UID#&mode=#mode#"),
			
		"PAGEN" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGEN"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal($GLOBALS["NavNum"] + 1)),
		"PATH_TO_SMILE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DEFAULT_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"DEFAULT" => "/bitrix/images/forum/smile/"),
		"MESSAGES_PER_PAGE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_MESSAGES_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => COption::GetOptionString("forum", "MESSAGES_PER_PAGE", "10")),
		"DATE_FORMAT" => CForumParameters::GetDateFormat(GetMessage("F_DATE_FORMAT"), "ADDITIONAL_SETTINGS"),
		"DATE_TIME_FORMAT" => CForumParameters::GetDateTimeFormat(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"PAGE_NAVIGATION_TEMPLATE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ""),
		"PAGE_NAVIGATION_WINDOW" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_PAGE_NAVIGATION_WINDOW"),
			"TYPE" => "STRING",
			"DEFAULT" => "11"),
		"WORD_LENGTH" => CForumParameters::GetWordLength(),
		"IMAGE_SIZE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_IMAGE_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => 300),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_DISPLAY_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		
		"SET_TITLE" => Array(),
		"CACHE_TIME" => Array(),
	)
);
?>