<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("iblock"))
	return;

//IB types
$arIBTypes = array(""=>" ");
$rsIBTypes = CIBlockType::GetList(
	array("SORT"=>"ASC")
);
while($IBType = $rsIBTypes->Fetch())
{
	if($arIBType = CIBlockType::GetByIDLang($IBType["ID"], LANG))
		$arIBTypes[$IBType["ID"]] = $arIBType["~NAME"];
}

//IB
$arIBlocks = array(""=>" ");
$rsIBlocks = CIBlock::GetList(
	array(
		"SORT" => "ASC"
	),
	array(
		"TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"
			?$arCurrentValues["IBLOCK_TYPE"]
			:"")
	)
);
while($Iblock = $rsIBlocks->Fetch())
	$arIBlocks[$Iblock["ID"]] = $Iblock["NAME"];

//Sort
$arSortsBy = array(
	"ASC"=>GetMessage("T_IBLOCK_DESC_ASC"),
	"DESC"=>GetMessage("T_IBLOCK_DESC_DESC")
);
$arSortFields = array(
	"ID"=>GetMessage("T_IBLOCK_DESC_FID"),
	"NAME"=>GetMessage("T_IBLOCK_DESC_FNAME"),
	"ACTIVE_FROM"=>GetMessage("T_IBLOCK_DESC_FACT"),
	"SORT"=>GetMessage("T_IBLOCK_DESC_FSORT"),
	"TIMESTAMP_X"=>GetMessage("T_IBLOCK_DESC_FTSAMP")
);

$arComponentParameters = array(
	"GROUPS" => array(
		"SETTINGS" => array(
			"NAME" => GetMessage("BITRIXTV_GROUP_SETTINGS"),
			"SORT" => 20
		),
		"PREVIEW_TV" => array(
			"NAME" => GetMessage("BITRIXTV_GROUP_PREVIEW_TV"),
			"SORT" => 40
		),
		"PREVIEW_TV_LIST" => array(
			"NAME" => GetMessage("BITRIXTV_GROUP_PREVIEW_TV_PLAYER"),
			"SORT" => 60
		),
	),
	"PARAMETERS" => array(
		"DEFAULT_SMALL_IMAGE" => Array(
			"PARENT" => "PREVIEW_TV_LIST",
			"NAME" => GetMessage("BITRIXTV_SETTING_DEFAULT_SMALL_IMAGE"),
			"TYPE" => "FILE",
			"DEFAULT" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/default_small.png",
			"SORT" => 10,
		),

		"DEFAULT_BIG_IMAGE" => Array(
			"PARENT" => "PREVIEW_TV_LIST",
			"NAME" => GetMessage("BITRIXTV_SETTING_DEFAULT_BIG_IMAGE"),
			"TYPE" => "FILE",
			"DEFAULT" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/default_big.png",
			"SORT" => 20,
		),

		"SORT_BY1" => Array(
			"PARENT" => "PREVIEW_TV_LIST",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBORD1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
			"SORT" => 30,
		),

		"SORT_ORDER1" => Array(
			"PARENT" => "PREVIEW_TV_LIST",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBBY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSortsBy,
			"SORT" => 40,
		),

		"IBLOCK_TYPE" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("BITRIXTV_SETTING_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBTypes,
			"DEFAULT" => "",
			"REFRESH" => "Y",
			"SORT" => 10,
		),

		"IBLOCK_ID" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("BITRIXTV_SETTING_IBLOCK_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => "",
			"REFRESH" => "Y",
			"SORT" => 20,
		),

		"ALLOW_SWF" => Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("BITRIXTV_SETTING_ALLOW_SWF"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"SORT" => 25,
		),

		"CACHE_TIME" =>  Array(
			"DEFAULT" => 3600,
		),
	),
);

if(IsModuleInstalled("intranet"))
{
	$arComponentParameters["PARAMETERS"]["DISPLAY_PANEL"] = Array(
			"PARENT" => "SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_TV_PARAMS_DISPLAY_PANEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		);
}

//Additional params
if(isset($arCurrentValues["IBLOCK_ID"]) && intval($arCurrentValues["IBLOCK_ID"])>0)
{
	//IB properties
	$arProperties = array(""=>" ");
	$rsProp = CIBlockProperty::GetList(
		array(
			"SORT"=>"ASC",
			"NAME"=>"ASC"
		),
		array(
			"ACTIVE"=>"Y",
			"IBLOCK_ID"=>$arCurrentValues["IBLOCK_ID"]
		)
	);
	while ($arr = $rsProp->Fetch())
		$arProperties[$arr['ID']] = $arr['NAME'].((strlen($arr['CODE'])>0)?' ['.$arr['CODE'].']':'');

	//Common properties
	$arComponentParameters["PARAMETERS"]["PATH_TO_FILE"] = Array(
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("BITRIXTV_SETTING_PATH_TO_FILE"),
		"TYPE" => "LIST",
		"VALUES" => $arProperties,
		"DEFAULT" => "",
		"SORT" => 40,
	);
	$arComponentParameters["PARAMETERS"]["DURATION"] = Array(
		"PARENT" => "SETTINGS",
		"NAME" => GetMessage("BITRIXTV_SETTING_DURATION"),
		"TYPE" => "LIST",
		"VALUES" => $arProperties,
		"DEFAULT" => "",
		"SORT" => 50,
	);

	$arComponentParameters["PARAMETERS"]["WIDTH"] = Array(
		"PARENT" => "PREVIEW_TV",
		"NAME" => GetMessage("BITRIXTV_SETTING_PREVIEW_WIDTH"),
		"TYPE" => "STRING",
		"DEFAULT" => "400",
		"SORT" => 10,
	);

	$arComponentParameters["PARAMETERS"]["HEIGHT"] = Array(
		"PARENT" => "PREVIEW_TV",
		"NAME" => GetMessage("BITRIXTV_SETTING_PREVIEW_HEIGHT"),
		"TYPE" => "STRING",
		"DEFAULT" => "300",
		"SORT" => 20,
	);

	$arComponentParameters["PARAMETERS"]["LOGO"] = Array(
		"PARENT" => "PREVIEW_TV",
		"NAME" => GetMessage("BITRIXTV_SETTING_LOGO"),
		"TYPE" => "FILE",
		"DEFAULT" => "",
		"SORT" => 25,
		"DEFAULT" => "/bitrix/components/bitrix/iblock.tv/templates/.default/images/logo.png"
	);

	//Section properties
	$arSecProperties = array(""=>" ");
	$rsPropSec = CIBlockSection::GetList(
		array(
			"LEFT_MARGIN" => 'ASC'),
		array(
			"ACTIVE" => 'Y',
			"IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"])
	);
	while($arr = $rsPropSec->Fetch())
		$arSecProperties[$arr['ID']] = $arr['NAME'].((strlen($arr['CODE'])>0)?' ['.$arr['CODE'].']':'');

	$arComponentParameters["PARAMETERS"]["SECTION_ID"] = Array(
		"PARENT" => "PREVIEW_TV",
		"NAME" => GetMessage("BITRIXTV_SETTING_PREVIEW_TV_SECTION"),
		"TYPE" => "LIST",
		"VALUES" => $arSecProperties,
		"REFRESH" => "Y",
		"DEFAULT" => "",
		"SORT" => 30,
	);

	if(isset($arCurrentValues["SECTION_ID"]) && intval($arCurrentValues["SECTION_ID"])>0)
	{
		$arElProperties = array(""=>" ");
		$rsPropEl = CIBlockElement::GetList(
			array(
				"SORT" => 'ASC',
				"ID" => 'ASC'
			),
			array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"],
				"SECTION_ID" => intval($arCurrentValues["SECTION_ID"])
			)
		);
		while ($arr = $rsPropEl->Fetch())
			$arElProperties[$arr['ID']] = $arr['NAME'].((strlen($arr['CODE'])>0)?' ['.$arr['CODE'].']':'');

		//Preview properties
		$arComponentParameters["PARAMETERS"]["ELEMENT_ID"] = Array(
			"PARENT" => "PREVIEW_TV",
			"NAME" => GetMessage("BITRIXTV_SETTING_PREVIEW_TV_ELEMENT"),
			"TYPE" => "LIST",
			"VALUES" => $arElProperties,
			"DEFAULT" => "",
			"SORT" => 40,
		);
	}
}
?>
