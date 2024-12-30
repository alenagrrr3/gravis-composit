<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("vote"))
	return;

$arrChannels = Array("-" =>GetMessage("VOTE_SELECT_DEFAULT"));
$rs = CVoteChannel::GetList($v1, $v2, array(), $v3);
while ($arChannel=$rs->GetNext()) 
{
	$arrChannels[$arChannel["SID"]] = "[".$arChannel["SID"]."] ".$arChannel["TITLE"];
}


$arComponentParameters = array(
	"PARAMETERS" => array(
		"CHANNEL_SID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("VOTE_CHANNEL_SID"), 
			"TYPE" => "LIST",
			"VALUES" => $arrChannels,
			"DEFAULT" => ""
		),
		"CACHE_TIME" => Array("DEFAULT" => 3600),
		"AJAX_MODE" => array(),
	)
);
?>