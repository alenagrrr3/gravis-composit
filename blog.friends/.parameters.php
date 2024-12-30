<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("blog"))
	return false;

$arComponentParameters = Array(
	"GROUPS" => array(
		"VARIABLE_ALIASES" => array(
			"NAME" => GetMessage("B_VARIABLE_ALIASES"),
		),
	),
	"PARAMETERS" => Array(
		"MESSAGE_COUNT" => Array(
				"NAME" => GetMessage("BF_MESSAGE_COUNT"),
				"TYPE" => "STRING",
				"DEFAULT" => 25,
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
		"PATH_TO_BLOG" => Array(
			"NAME" => GetMessage("BF_PATH_TO_BLOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_BLOG_CATEGORY" => Array(
			"NAME" => GetMessage("BF_PATH_TO_BLOG_CATEGORY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST" => Array(
			"NAME" => GetMessage("BF_PATH_TO_POST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST_EDIT" => Array(
			"NAME" => GetMessage("BF_PATH_TO_POST_EDIT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_USER" => Array(
			"NAME" => GetMessage("BF_PATH_TO_USER"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SMILE" => Array(
			"NAME" => GetMessage("BF_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BF_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BF_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"USER_VAR" => Array(
			"NAME" => GetMessage("BF_USER_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BF_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"ID" => Array(
			"NAME" => GetMessage("BF_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$user_id}",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"SET_TITLE" =>Array(),
	)
);
?>