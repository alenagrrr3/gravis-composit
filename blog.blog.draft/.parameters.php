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
		"PATH_TO_BLOG_CATEGORY" => Array(
			"NAME" => GetMessage("BBD_PATH_TO_BLOG_CATEGORY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_POST_EDIT" => Array(
			"NAME" => GetMessage("BBD_PATH_TO_POST_EDIT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "URL_TEMPLATES",
		),
		"PATH_TO_SMILE" => Array(
			"NAME" => GetMessage("BBD_PATH_TO_SMILE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"BLOG_VAR" => Array(
			"NAME" => GetMessage("BBD_BLOG_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"POST_VAR" => Array(
			"NAME" => GetMessage("BBD_POST_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"PAGE_VAR" => Array(
			"NAME" => GetMessage("BBD_PAGE_VAR"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "VARIABLE_ALIASES",
		),
		"BLOG_URL" => Array(
			"NAME" => GetMessage("BBD_BLOG_URL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$blog}",
			"COLS" => 25,
			"PARENT" => "DATA_SOURCE",
		),
		"SET_NAV_CHAIN" => Array(
		  	"NAME" => GetMessage("BBD_SET_NAV_CHAIN"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"VALUE" => "Y",
			"DEFAULT" =>"Y",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" =>Array(),
		"DATE_TIME_FORMAT" => Array(
				"PARENT" => "VISUAL",
				"NAME" => GetMessage("BC_DATE_TIME_FORMAT"),
				"TYPE" => "LIST",
				"VALUES" => CBlogTools::GetDateTimeFormat(),
				"MULTIPLE" => "N",
				"DEFAULT" => $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")),	
				"ADDITIONAL_VALUES" => "Y",
			),		

	)
);
?>