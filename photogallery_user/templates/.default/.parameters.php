<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arTemplateParameters = array(
	"MODERATE" => array(
		"NAME" => GetMessage("P_MODERATE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),
	"SHOW_ONLY_PUBLIC" => array(
		"NAME" => GetMessage("P_SHOW_ONLY_PUBLIC"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "Y"),

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
		"DEFAULT" => "0"),
	"SLIDER_COUNT_CELL" => array(
		"NAME" => GetMessage("P_SLIDER_COUNT_CELL"),
		"TYPE" => "STRING",
		"DEFAULT" => "4"),
);
?>