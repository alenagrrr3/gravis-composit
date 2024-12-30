<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"SHOW_LINK_ON_MAIN_PAGE" => array(
		"NAME" => GetMessage("P_SHOW_LINK_ON_MAIN_PAGE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"id" => GetMessage("P_LINK_NEW"), 
			"shows" => GetMessage("P_LINK_SHOWS"),
			"rating" => GetMessage("P_LINK_RATING"), 
			"comments" => GetMessage("P_LINK_COMMENTS")),
		"DEFAULT" => array("id", "rating", "comments", "shows"),
		"MULTIPLE" => "Y"),
	"SHOW_ON_MAIN_PAGE" => array(
		"NAME" => GetMessage("P_SHOW_ON_MAIN_PAGE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"none" => GetMessage("P_SHOW_ON_MAIN_PAGE_NONE"), 
			"rating" => GetMessage("P_SHOW_ON_MAIN_PAGE_RATING"), 
			"id" => "ID", 
			"comments" => GetMessage("P_SHOW_ON_MAIN_PAGE_COMMENTS")),
		"DEFAULT" => array("none")),
	"SHOW_ON_MAIN_PAGE_POSITION" => array(
		"NAME" => GetMessage("P_SHOW_ON_MAIN_PAGE_POSITION"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"left" => GetMessage("P_LEFT"), 
			"right" => GetMessage("P_RIGHT")),
		"DEFAULT" => array("right")),
	"SHOW_ON_MAIN_PAGE_TYPE" => array(
		"NAME" => GetMessage("P_SHOW_ON_MAIN_PAGE_TYPE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"none" => GetMessage("P_SHOW_ON_MAIN_PAGE_TYPE_NONE"),
			"count" => GetMessage("P_SHOW_ON_MAIN_PAGE_TYPE_COUNT"), 
			"date" => GetMessage("P_SHOW_ON_MAIN_PAGE_TYPE_DAY")),
		"DEFAULT" => array("none")),
	"SHOW_ON_MAIN_PAGE_COUNT" => array(
		"NAME" => GetMessage("P_SHOW_ON_MAIN_PAGE_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
	"SHOW_PHOTO_ON_DETAIL_LIST" => array(
		"NAME" => GetMessage("P_SHOW_PHOTO_ON_DETAIL_LIST"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"none" => Getmessage("P_SHOW_NONE"),
			"show_period" => GetMessage("P_SHOW_CALENDAR"), 
			"show_count" => GetMessage("P_SHOW_COUNT"), 
			"show_time" => GetMessage("P_SHOW_DAY")),
		"DEFAULT" => array("show_count")),
	"SHOW_PHOTO_ON_DETAIL_LIST_COUNT" => array(
		"NAME" => GetMessage("P_SHOW_PHOTO_ON_DETAIL_LIST_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "500"),

	"PAGE_NAVIGATION_TEMPLATE" => array(
		"NAME" => GetMessage("P_PAGE_NAVIGATION_TEMPLATE"),
		"TYPE" => "STRING",
		"DEFAULT" => ""),
	
	"WATERMARK_COLORS" => Array(
		"NAME" => GetMessage("P_WATERMARK_COLORS"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"FF0000" => GetMessage("P_COLOR_FF0000"), 
			"FFA500" => GetMessage("P_COLOR_FFA500"), 
			"FFFF00" => GetMessage("P_COLOR_FFFF00"), 
			"008000" => GetMessage("P_COLOR_008000"), 
			"00FFFF" => GetMessage("P_COLOR_00FFFF"), 
			"800080" => GetMessage("P_COLOR_800080"), 
			"FFFFFF" => GetMessage("P_COLOR_FFFFFF"),
			"000000" => GetMessage("P_COLOR_000000")),
		"DEFAULT" => array("FF0000", "FFFF00", "FFFFFF", "000000"),
		"ADDITIONAL_VALUES" => "Y",
		"MULTIPLE" => "Y"), 
	"TEMPLATE_LIST" => Array(
		"NAME" => GetMessage("P_TEMPLATE"),
		"TYPE" => "LIST",
		"VALUES" => array(
			".default" => GetMessage("P_TEMLATE_DEFAULT"), 
			"table" => GetMessage("P_TEMLATE_TABLE")),
		"DEFAULT" => array(""),
		"REFRESH" => "Y"), 
	"CELL_COUNT" => array(
		"NAME" => GetMessage("P_TEMPLATE_CELL_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => "0")
);

?>