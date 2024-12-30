<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

global $arrSaveColor;
if (CModule::IncludeModule("vote")) :

$diameter = (intval($_REQUEST["dm"])>0) ? intval($_REQUEST["dm"]) : 150;

$res = CVoteAnswer::GetList($qid,($by="s_counter"),($order="desc"));
$res->NavStart(1000);
$totalRecords = $res->SelectedRowsCount();

$arChart = array(); $color = "";
while ($arAnswer = $res->Fetch())
{
	$arChart[] = Array(
		"COLOR"=>(strlen($arAnswer["COLOR"])>0 ? TrimEx($arAnswer["COLOR"],"#") : ($color = GetNextRGB($color, $totalRecords))),
		"COUNTER" => $arAnswer["COUNTER"]
	);
}

// create an image
$ImageHandle = CreateImageHandle($diameter, $diameter);

// drawing pie chart
Circular_Diagram($ImageHandle, $arChart, "FFFFFF", $diameter, $diameter/2, $diameter/2);

// displaying of the resulting image
ShowImageHeader($ImageHandle);

endif;
?>