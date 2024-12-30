<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("vote"))
{
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
}

if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;

$arParams["CHANNEL_SID"] = trim($arParams["CHANNEL_SID"]);

$obCache = new CPHPCache;
$cache_id = "vote_current_".serialize($arParams); //."_".$USER->GetGroups();
$cache_path = "/".SITE_ID."/current_vote/".$arParams["CHANNEL_SID"]."/";

if (!$obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	//Get vote channel
	$obChannel = CVoteChannel::GetList($by, $order,
		Array(
			"SID"=> $arParams["CHANNEL_SID"], 
			"SID_EXACT_MATCH"=>"Y", 
			"SITE"=> SITE_ID, 
			"ACTIVE"=>"Y"
		),
		$is_filtered
	);

	if (!$arChannel = $obChannel->Fetch())
	{
		ShowError(GetMessage("VOTE_CHANNEL_NOT_FOUND"));
		return;
	}

	//Get current vote
	$obVote = CVote::GetList($by, $order, 
		Array(
			"CHANNEL_ID"=>$arChannel["ID"], 
			"LAMP"=>"green"
		), 
		$is_filtered
	);

	if (!$arVote = $obVote->GetNext())
		return;

	$arResult = array(
		"VOTE" => $arVote,
		"VOTE_ID" => $arVote["ID"],
		"VOTE_RESULT_TEMPLATE" => $APPLICATION->GetCurPageParam(),
		"ADDITIONAL_CACHE_ID" => "current_vote",
	);

	$templateCachedData = $this->GetTemplateCachedData();

	$obCache->StartDataCache();
	$obCache->EndDataCache(
		Array(
			"arResult" => $arResult,
			"templateCachedData" => $templateCachedData
		)
	);
}
else
{
	$arVars = $obCache->GetVars();
	$arResult = $arVars["arResult"];
	$this->SetTemplateCachedData($arVars["templateCachedData"]);
}

$permission = CVoteChannel::GetGroupPermission($arResult["VOTE"]["CHANNEL_ID"]);
if ($permission == 0)
	return;

$voteUserID = intval($GLOBALS["APPLICATION"]->get_cookie("VOTE_USER_ID"));
$isUserVoted = CVote::UserAlreadyVote($arResult["VOTE_ID"], $voteUserID, $arResult["VOTE"]["UNIQUE_TYPE"], $arResult["VOTE"]["KEEP_IP_SEC"]);

if ($GLOBALS["VOTING_OK"] =="Y" || $GLOBALS["USER_ALREADY_VOTE"] =="Y" || $permission == 1 || $isUserVoted)
	$componentPage = "result";
else
	$componentPage = "form";

$this->IncludeComponentTemplate($componentPage);
?>