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
			"VALUES" => $arIBlock,
			"REFRESH" => "Y"),
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
		"SECTION_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_SECTION_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SECTION_ID"]}'),
		"ELEMENT_LAST_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"none" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_NONE"),
				"count" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_COUNT"),
				"time" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_TIME"),
				"period" => GetMessage("IBLOCK_ELEMENT_LAST_TYPE_PERIOD"),
			),
			"DEFAULT" => "none",
			"REFRESH" => "Y")
	)
);

if($arCurrentValues["ELEMENT_LAST_TYPE"] == "count")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_COUNT"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENTS_LAST_COUNT"),
		"TYPE" => "STRING",
		"DEFAULT" => '30');
}
elseif ($arCurrentValues["ELEMENT_LAST_TYPE"] == "time")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("IBLOCK_ELEMENTS_LAST_TIME"),
		"TYPE" => "STRING",
		"DEFAULT" => '30');
}
elseif ($arCurrentValues["ELEMENT_LAST_TYPE"] == "period")
{
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME_FROM"] = array(
		"PARENT" => "BASE",
		"NAME" => str_replace("#FORMAT_DATETIME#", FORMAT_DATETIME, GetMessage("IBLOCK_ELEMENTS_LAST_TIME_FROM")),
		"TYPE" => "STRING",
		"DEFAULT" => '');
	$arComponentParameters["PARAMETERS"]["ELEMENTS_LAST_TIME_TO"] = array(
		"PARENT" => "BASE",
		"NAME" => str_replace("#FORMAT_DATETIME#", FORMAT_DATETIME, GetMessage("IBLOCK_ELEMENTS_LAST_TIME_TO")),
		"TYPE" => "STRING",
		"DEFAULT" => '');
	
}

$arComponentParameters["PARAMETERS"] = array_merge(
	$arComponentParameters["PARAMETERS"], 
	array(
		"USE_DESC_PAGE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_USE_DESC_PAGE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
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
		
		"ADDITIONAL_SIGHTS" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_ADDITIONAL_SIGHTS"),
			"TYPE" => "LIST",
			"VALUES" => $arSights,
			"DEFAULT" => array(),
			"MULTIPLE" => "Y"),
			
		"PICTURES_SIGHT" => array(
			"PARENT" => "PHOTO_SETTINGS",
			"NAME" => GetMessage("P_PICTURES_SIGHT"),
			"TYPE" => "LIST",
			"VALUES" => array_merge(array("" => "..."), $arSights),
			"DEFAULT" => array()),
		
		"DETAIL_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "detail.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
		"DETAIL_SLIDE_SHOW_URL" => array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_DETAIL_SLIDE_SHOW_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "slide_show.php?".($arCurrentValues["BEHAVIOUR"] == "USER" ? "USER_ALIAS=#USER_ALIAS#" : "").
				"SECTION_ID=#SECTION_ID#&ELEMENT_ID=#ELEMENT_ID#"),
		
		"PAGE_ELEMENTS" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("IBLOCK_PAGE_ELEMENTS"),
			"TYPE" => "STRING",
			"DEFAULT" => '100'),
			
		"PAGE_NAVIGATION_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("IBLOCK_PAGE_NAVIGATION_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => ''),
			
		"USE_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"GROUP_PERMISSIONS" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => Array(1),
			"MULTIPLE" => "Y",
		),
		"COMMENTS_TYPE" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_COMMENTS_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => array(
				"none" => "",
				"blog" => GetMessage("P_COMMENTS_TYPE_BLOG"),
				"forum" => GetMessage("P_COMMENTS_TYPE_FORUM")),
			"DEFAULT" => Array("none")),
		"SET_TITLE" => Array(),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"DATE_TIME_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
	)
);
if (IsModuleInstalled("search"))
{
	$arComponentParameters["PARAMETERS"]["SEARCH_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_SEARCH_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "search.php");
}
if($arCurrentValues["BEHAVIOUR"] == "USER")
{
	$arComponentParameters["PARAMETERS"]["GALLERY_URL"] = array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("IBLOCK_GALLERY_URL"),
			"TYPE" => "STRING",
			"DEFAULT" => "gallery.php?USER_ALIAS=#USER_ALIAS#");
	$arComponentParameters["PARAMETERS"]["GALLERY_SIZE"] = array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("P_GALLERY_SIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "");

}
?>