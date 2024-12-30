<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");
global $arrSaveColor;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");

//Cache
$arParams["ADDITIONAL_CACHE_ID"] = (
	isset($arParams["ADDITIONAL_CACHE_ID"]) && strlen($arParams["ADDITIONAL_CACHE_ID"]) > 0 ?
	$arParams["ADDITIONAL_CACHE_ID"] :
	$USER->GetGroups()
);

if ($GLOBALS["VOTING_OK"] =="Y" || $GLOBALS["USER_ALREADY_VOTE"] =="Y")
	$this->ClearResultCache($arParams["ADDITIONAL_CACHE_ID"]);
elseif (!$this->StartResultCache(false, $arParams["ADDITIONAL_CACHE_ID"]))
	return;

if(!CModule::IncludeModule("vote"))
{
	$this->AbortResultCache();
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
}

$arParams["VOTE_ID"] = intval($arParams["VOTE_ID"]);
$VOTE_ID = GetVoteDataByID($arParams["VOTE_ID"], $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "Y");

if (intval($VOTE_ID) <= 0)
{
	$this->AbortResultCache();
	ShowError(GetMessage("VOTE_NOT_FOUND"));
	return;
}

//Errors
$strError = $strNote = "";

if ($GLOBALS["VOTING_OK"]=="Y" || $_REQUEST["VOTE_SUCCESSFULL"]=="Y") 
	$strNote .= GetMessage("VOTE_OK")."<br>";

if ($GLOBALS["USER_ALREADY_VOTE"]=="Y")
	$strError .= GetMessage("VOTE_ALREADY_VOTE")."<br>";

if ($GLOBALS["VOTING_LAMP"]=="red") 
	$strError .= GetMessage("VOTE_RED_LAMP")."<br>";

if (CVoteChannel::GetGroupPermission($arChannel["ID"]) >= 1)
{
	$counter = intval($arVote['COUNTER']);
	if ($counter <= 0)
	{
		$counter = 1;
	}
	
	for ($questionIndex = 0, $questionSize = count($arQuestions); $questionIndex < $questionSize; $questionIndex++)
	{
		$questionID = $arQuestions[$questionIndex]["ID"];

		//Include in the result chart
		if ($arQuestions[$questionIndex]["DIAGRAM"] == "N")
		{
			unset($arAnswers[$questionID]);
			unset($arQuestions[$questionIndex]);
			continue;
		}
		elseif (!array_key_exists($questionID, $arAnswers))
		{
			unset($arQuestions[$questionIndex]);
			continue;
		}

		//Calculating the sum and maximum value
		$counterSum = $counterMax = 0;
		foreach ($arAnswers[$questionID] as $arAnswer)
		{
			$counterSum += $arAnswer["COUNTER"];
			$counterMax = ($arAnswer["COUNTER"] > $counterMax ? $arAnswer["COUNTER"] : $counterMax);
		}

		//Sorting answers
		uasort($arAnswers[$questionID], "_vote_answer_sort");

		$color = "";
		foreach ($arAnswers[$questionID] as $answerIndex => $arAnswer)
		{
			$arAnswers[$questionID][$answerIndex]["PERCENT"] = ($counterSum > 0 ? number_format(($arAnswer["COUNTER"]*100/$counter),2,',','') : 0);
			$arAnswers[$questionID][$answerIndex]["BAR_PERCENT"] = ($counterMax > 0 ? round($arAnswer["COUNTER"]*100/$counterMax) : 0);

			if (strlen($arAnswer["COLOR"]) <= 0)
			{
				$color = GetNextRGB($color, count($arAnswers[$questionID]));
				$arAnswers[$questionID][$answerIndex]["COLOR"] = $color;
			}
			else
			{
				$arAnswers[$questionID][$answerIndex]["COLOR"] = TrimEx($arAnswer["COLOR"], "#");
			}
		}

		$arQuestions[$questionIndex]["COUNTER_SUM"] = $counterSum;
		$arQuestions[$questionIndex]["COUNTER_MAX"] = $counterMax;

		//Images
		$arQuestions[$questionIndex]["IMAGE"] = CFile::GetFileArray($arQuestions[$questionIndex]["IMAGE_ID"]);

		//Diagram type
		if (strlen($arParams["QUESTION_DIAGRAM_".$questionID])>0 && $arParams["QUESTION_DIAGRAM_".$questionID]!="-")
			$arQuestions[$questionIndex]["DIAGRAM_TYPE"] = trim($arParams["QUESTION_DIAGRAM_".$questionID]);

		//Answers
		$arQuestions[$questionIndex]["ANSWERS"] = $arAnswers[$questionID];
		unset($arAnswers[$questionID]);
	}

	//Vote Image
	$arVote["IMAGE"] = CFile::GetFileArray($arVote["IMAGE_ID"]);
}
else
{
	$arQuestions = Array();
	$arChannel = Array();
	$arVote = Array();
	$arGroupAnswers = Array();
	$strError .= GetMessage("VOTE_ACCESS_DENIED");
}

$arResult = Array(
	"CHANNEL" => $arChannel,
	"VOTE" => $arVote,
	"QUESTIONS" => $arQuestions,
	"GROUP_ANSWERS" => $arGroupAnswers,
	"CURRENT_PAGE" => htmlspecialchars($APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL"))),
	"ERROR_MESSAGE" => $strError,
	"OK_MESSAGE" => $strNote,
);

unset($arQuestions);
unset($arChannel);
unset($arVote);
unset($arAnswers);
unset($arDropDown);
unset($arMultiSelect);

$this->IncludeComponentTemplate();

?>