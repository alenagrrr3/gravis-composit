<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("vote"))
{
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
}

$arParams["CHANNEL_SID"] = trim(str_replace("-", "", $arParams["CHANNEL_SID"]));
$arParams["CHANNEL_SID"] = (preg_match("~^[A-Za-z0-9_]+$~", $arParams["CHANNEL_SID"]) ? $arParams["CHANNEL_SID"] : "");

$arParams["VOTE_FORM_TEMPLATE"] = trim($arParams["VOTE_FORM_TEMPLATE"]);
$arParams["VOTE_FORM_TEMPLATE"] = (strlen($arParams["VOTE_FORM_TEMPLATE"])>0 ? htmlspecialchars($arParams["VOTE_FORM_TEMPLATE"]) : "vote_new.php?VOTE_ID=#VOTE_ID#");

$arParams["VOTE_RESULT_TEMPLATE"] = trim($arParams["VOTE_RESULT_TEMPLATE"]);
$arParams["VOTE_RESULT_TEMPLATE"] = (strlen($arParams["VOTE_RESULT_TEMPLATE"])>0 ? htmlspecialchars($arParams["VOTE_RESULT_TEMPLATE"]) : "vote_result.php?VOTE_ID=#VOTE_ID#");

$rsVotes = GetVoteList($arParams["CHANNEL_SID"]);
$rsVotes->NavStart(10);

$arResult = Array(
	"VOTES" => Array(),
	"NAV_STRING" => $rsVotes->GetPageNavString(GetMessage("VOTE_PAGES")),
);

while ($arVote = $rsVotes->GetNext())
{
	$arUrl = Array(
		"VOTE_RESULT_URL" => CComponentEngine::MakePathFromTemplate($arParams["VOTE_RESULT_TEMPLATE"], Array("VOTE_ID" => $arVote["ID"])),
		"VOTE_FORM_URL" => CComponentEngine::MakePathFromTemplate($arParams["VOTE_FORM_TEMPLATE"], Array("VOTE_ID" => $arVote["ID"])),
	);

	$arResult["VOTES"][] = $arVote + $arUrl + Array("IMAGE" => CFile::GetFileArray($arVote["IMAGE_ID"]));
}

$this->IncludeComponentTemplate();
?>