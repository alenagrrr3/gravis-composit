<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/bitrix/desktop/include.php');

$arComponentParameters = Array(
	"GROUPS" => array(
	),
	"PARAMETERS" => Array(
		"ID" => Array(
				"NAME" => GetMessage("CMDESKTOP_PARAMS_ID"),
				"TYPE" => "STRING",
				"DEFAULT" => "holder1",
				"PARENT" => "DATA_SOURCE",
			),
		"CAN_EDIT" => Array(
				"NAME" => GetMessage("CMDESKTOP_PARAMS_CAN_EDIT"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "Y",
				"PARENT" => "DATA_SOURCE",
			),
		"COLUMNS" => Array(
				"NAME" => GetMessage("CMDESKTOP_PARAMS_COLUMNS"),
				"TYPE" => "STRING",
				"DEFAULT" => "3",
				"PARENT" => "DATA_SOURCE",
				"REFRESH"=> "Y"
			),
	)
);

if($arCurrentValues["COLUMNS"]>0)
{
	$d = intval(100/$arCurrentValues["COLUMNS"])."%";

	for($i=0; $i<$arCurrentValues["COLUMNS"]; $i++)
		$arComponentParameters["PARAMETERS"]["COLUMN_WIDTH_".$i] = Array(
				"NAME"=>GetMessage("CMDESKTOP_PARAMS_COLUMN_WITH")." #".($i+1),
				"PARENT" => "DATA_SOURCE",
				"TYPE"=>"STRING",
				"DEFAULT"=>$d,
			);
}

$arComponentParameters["PARAMETERS"]["GADGETS"] = Array(
		"NAME" => GetMessage("CMDESKTOP_PARAMS_GADGETS"),
		"TYPE" => "LIST",
		"DEFAULT" => "ALL",
		"PARENT" => "DATA_SOURCE",
		"MULTIPLE" => "Y",
		"SIZE"=>"10",
		"REFRESH" => "Y",
		"VALUES" => Array("ALL"=>GetMessage("CMDESKTOP_PARAMS_GADGETS_ALL")),
	);

$arGadgets = BXGadget::GetList(true, $arCurrentValues);
foreach($arGadgets as $gd)
{
	$arComponentParameters["PARAMETERS"]["GADGETS"]["VALUES"][$gd["ID"]] = $gd["NAME"];
	if(!is_array($arCurrentValues) || !is_array($arCurrentValues["GADGETS"]) || in_array($gd["ID"], $arCurrentValues["GADGETS"]) || in_array("ALL", $arCurrentValues["GADGETS"]))
	{
		if(is_array($gd["PARAMETERS"]) && count($gd["PARAMETERS"])>0)
		{
			$arComponentParameters["GROUPS"]["G_".$gd["ID"]] = Array("NAME" => GetMessage("CMDESKTOP_PARAMS_GADGET_SET")." \"".$gd["NAME"]."\"");
			foreach($gd["PARAMETERS"] as $id=>$p)
			{
				$p["PARENT"] = "G_".$gd["ID"];
				$arComponentParameters["PARAMETERS"]["G_".$gd["ID"]."_".$id] = $p;
			}
		}

		if(is_array($gd["USER_PARAMETERS"]) && count($gd["USER_PARAMETERS"])>0)
		{
			$arComponentParameters["GROUPS"]["GU_".$gd["ID"]] = Array("NAME" => GetMessage("CMDESKTOP_PARAMS_GADGET_PAR")." \"".$gd["NAME"]."\"");
			foreach($gd["USER_PARAMETERS"] as $id=>$p)
			{
				$p["PARENT"] = "GU_".$gd["ID"];
				$arComponentParameters["PARAMETERS"]["GU_".$gd["ID"]."_".$id] = $p;
			}
		}
	}
}
?>
