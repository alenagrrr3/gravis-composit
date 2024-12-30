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

$arComponentParameters = array(
	"GROUPS" => array(
	),
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
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}'),
		"SECTION_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => ''),
		"ELEMENT_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["ELEMENT_ID"]}'),
		"ELEMENT_CODE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_CODE"),
			"TYPE" => "STRING",
			"DEFAULT" => ''),
		"BEHAVIOUR" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_BEHAVIOUR"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"SIMPLE" => GetMessage("IBLOCK_BEHAVIOUR_SIMPLE"),
				"USER" => GetMessage("IBLOCK_BEHAVIOUR_USER")),
			"DEFAULT" => "SIMPLE",
			"REFRESH" => "Y"),
		"USER_ALIAS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_USER_ALIAS"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["USER_ALIAS"]}'),
			
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
			"DEFAULT" => "sort"),
		"ELEMENT_SORT_ORDER" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_SORT_ORDER"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"asc" => GetMessage("IBLOCK_SORT_ASC"),
				"desc" => GetMessage("IBLOCK_SORT_DESC")),
			"DEFAULT" => "asc"),
			
			
		"SECTION_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SECTION_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "section.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#"),
		"DETAIL_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
		"DETAIL_EDIT_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_EDIT_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "detail_edit.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
		"UPLOAD_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "upload.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ACTION=upload"),
				
		"USE_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => Array(1),
			"MULTIPLE" => "Y"),
			
		"COMMENTS_TYPE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_COMMENTS_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"none" => "",
				"blog" => GetMessage("P_COMMENTS_TYPE_BLOG"),
				"forum" => GetMessage("P_COMMENTS_TYPE_FORUM")),
			"DEFAULT" => Array("none")),
		"DATE_TIME_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"SHOW_TAGS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_SHOW_TAGS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		
		"SET_TITLE" => Array(),
		"DISPLAY_PANEL" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
	),
);
if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEARCH_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SEARCH_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "search.php");
}

?>