<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//Initialize dropdown lists
$arrDropdown=array();
if(IsModuleInstalled("forum"))
{
	$arrDropdown["forum"] = "[forum] ".GetMessage("SEARCH_FORUM");
}
if(CModule::IncludeModule("iblock"))
{
	$rsType = CIBlockType::GetList(array("sort"=>"asc"), array("ACTIVE"=>"Y"));
	while ($arr=$rsType->Fetch())
	{
		if($ar=CIBlockType::GetByIDLang($arr["ID"], LANGUAGE_ID))
			$arrDropdown["iblock_".$arr["ID"]] = "[iblock_".$arr["ID"]."] ".$ar["~NAME"];
	}
}
if(IsModuleInstalled("blog"))
{
	$arrDropdown["blog"] = "[blog] ".GetMessage("SEARCH_BLOG");
}
if(IsModuleInstalled("socialnetwork"))
{
	$arrDropdown["socialnetwork"] = "[socialnetwork] ".GetMessage("SEARCH_SOCIALNETWORK");
}
if(IsModuleInstalled("intranet"))
{
	$arrDropdown["intranet"] = "[intranet] ".GetMessage("SEARCH_INTRANET_USERS");
}

$arrFilterDropdown=array_merge(
	array("no"=>GetMessage("SEARCH_NO_LIMIT"), "main" => "[main] ".GetMessage("SEARCH_STATIC"))
	,$arrDropdown
	);
$arrFILTER=array();
if(is_array($arCurrentValues["arrFILTER"]))
{
	foreach($arCurrentValues["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			//array_pop($arCurrentValues["arrFILTER"]);
		}
		elseif($strFILTER=="forum" && CModule::IncludeModule("forum"))
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsForum = CForumNew::GetList();
			while($arForum=$rsForum->Fetch())
				$arrFILTER[$strFILTER][$arForum["ID"]]=$arForum["NAME"];
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsIBlock = CIBlock::GetList(array("SORT"=>"ASC"),array("TYPE"=>substr($strFILTER,7)));
			while($arIBlock=$rsIBlock->Fetch())
				$arrFILTER[$strFILTER][$arIBlock["ID"]]=$arIBlock["NAME"];
		}
		elseif($strFILTER=="blog" && CModule::IncludeModule("blog"))
		{
			$arrFILTER[$strFILTER]["all"]=GetMessage("SEARCH_ALL");
			$rsBlog = CBlog::GetList();
			while($arBlog=$rsBlog->Fetch())
				$arrFILTER[$strFILTER][$arBlog["ID"]]=$arBlog["NAME"];
		}
	}
}

$sSectionName = GetMessage("SEARCH_SECTION_NAME");

$arComponentParameters = array(
	"GROUPS" => array(
		"PAGER_SETTINGS" => array(
			"NAME" => GetMessage("SEARCH_PAGER_SETTINGS"),
		),
	),
	"PARAMETERS" => array(
		"AJAX_MODE" => array(),
		"RESTART" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_RESTART"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CHECK_DATES" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"USE_TITLE_RANK" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_USE_TITLE_RANK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"arrWHERE" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_WHERE_DROPDOWN"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arrDropdown,
		),
		"arrFILTER" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("SEARCH_WHERE_FILTER"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arrFilterDropdown,
			"DEFAULT" => "all",
			"REFRESH" => "Y",
		),
		"SHOW_WHERE" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("SEARCH_SHOW_DROPDOWN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"PAGE_RESULT_COUNT" => array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("SEARCH_PAGE_RESULT_COUNT"),
			"TYPE" => "STRING",
			"DEFAULT" => "50",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"PAGER_TITLE" => array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("SEARCH_PAGER_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("SEARCH_RESULTS"),
		),
		"PAGER_SHOW_ALWAYS" => array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("SEARCH_PAGER_SHOW_ALWAYS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"PAGER_TEMPLATE" => array(
			"PARENT" => "PAGER_SETTINGS",
			"NAME" => GetMessage("SEARCH_PAGER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
	),
);
if(is_array($arCurrentValues["arrFILTER"]))
{
	foreach($arCurrentValues["arrFILTER"] as $strFILTER)
	{
		if($strFILTER=="main")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_URL"),
				"TYPE" => "STRING",
				"MULTIPLE" => "Y",
				"ADDITIONAL_VALUES"=>"Y",
				"DEFAULT" => "",
				);
		}
		elseif($strFILTER=="forum")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_FORUM"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif(strpos($strFILTER,"iblock_")===0)
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_IBLOCK_TYPE1").$arrFilterDropdown[$strFILTER].GetMessage("SEARCH_IBLOCK_TYPE2"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
		elseif($strFILTER=="blog")
		{
			$arComponentParameters["PARAMETERS"]["arrFILTER_".$strFILTER]=array(
				"PARENT" => "DATA_SOURCE",
				"NAME" => GetMessage("SEARCH_BLOG"),
				"TYPE" => "LIST",
				"MULTIPLE" => "Y",
				"VALUES" => $arrFILTER[$strFILTER],
				"ADDITIONAL_VALUES"=>"N",
				"DEFAULT" => "all",
				);
		}
	}
}
?>
