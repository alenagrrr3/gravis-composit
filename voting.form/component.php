<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");

$arParams["VOTE_RESULT_TEMPLATE"] = trim($arParams["VOTE_RESULT_TEMPLATE"]);
$arParams["VOTE_RESULT_TEMPLATE"] = (strlen($arParams["VOTE_RESULT_TEMPLATE"])>0 ? htmlspecialchars($arParams["VOTE_RESULT_TEMPLATE"]) : "vote_result.php?VOTE_ID=#VOTE_ID#");
$arParams["VOTE_ID"] = intval($arParams["VOTE_ID"]);

if ($GLOBALS["VOTING_OK"]=="Y") 
{
	$strNavQueryString = DeleteParam(array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL"));
	if($strNavQueryString <> "") 
		$strNavQueryString = "&".$strNavQueryString;

	LocalRedirect(
		CComponentEngine::MakePathFromTemplate(
			$arParams["VOTE_RESULT_TEMPLATE"]."&VOTE_SUCCESSFULL=Y".$strNavQueryString,
			Array("VOTE_ID" => $arParams["VOTE_ID"])
		)
	);
}

//Cache
$arParams["ADDITIONAL_CACHE_ID"] = (
	isset($arParams["ADDITIONAL_CACHE_ID"]) && strlen($arParams["ADDITIONAL_CACHE_ID"]) > 0 ?
	$arParams["ADDITIONAL_CACHE_ID"] :
	$USER->GetGroups()
);

if (!$this->StartResultCache(false, $arParams["ADDITIONAL_CACHE_ID"]))
	return;

if(!CModule::IncludeModule("vote"))
{
	$this->AbortResultCache();
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
}

//Errors
$strError = "";
$strNote = "";

if ($GLOBALS["VOTING_OK"]=="Y" || $_REQUEST["VOTE_SUCCESSFULL"]=="Y") 
	$strNote .= GetMessage("VOTE_OK")."<br>";

if ($GLOBALS["USER_ALREADY_VOTE"]=="Y")
	$strError .= GetMessage("VOTE_ALREADY_VOTE")."<br>";

if ($GLOBALS["VOTING_LAMP"]=="red") 
	$strError .= GetMessage("VOTE_RED_LAMP")."<br>";

$VOTE_ID = GetVoteDataByID($arParams["VOTE_ID"], $arChannel, $arVote, $arQuestions, $arAnswers, $arDropDown, $arMultiSelect, $arGroupAnswers, "N");

if (intval($VOTE_ID) <= 0)
{
	$this->AbortResultCache();
	ShowError(GetMessage("VOTE_NOT_FOUND"));
	return;
}

if (CVoteChannel::GetGroupPermission($arChannel["ID"]) == 2)
{
	$defaultWidth = "10";
	$defaultHeight = "5";
	$questionSize = count($arQuestions);
	for ($questionIndex = 0; $questionIndex < $questionSize; $questionIndex++)
	{
		$QuestionID = $arQuestions[$questionIndex]["ID"];

		if (!array_key_exists($QuestionID, $arAnswers))
		{
			unset($arQuestions[$questionIndex]);
			unset($arAnswers[$QuestionID]);
			continue;
		}

		$arQuestions[$questionIndex]["ANSWERS"] = Array();

		$foundDropdown = $foundMultiselect = false;

		foreach ($arAnswers[$QuestionID] as $arAnswer)
		{
			if ($arAnswer["FIELD_TYPE"] == 2)
			{
				if ($foundDropdown == false)
				{
					$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array(
						"DROPDOWN" => _GetAnswerArray($QuestionID, $arAnswer["FIELD_TYPE"], $arAnswers),
						"MULTISELECT" => Array(),
					);
					$foundDropdown = true;
				}
			}
			elseif($arAnswer["FIELD_TYPE"] == 3)
			{
				if ($foundMultiselect == false)
				{
					$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array(
						"MULTISELECT" => _GetAnswerArray($QuestionID, $arAnswer["FIELD_TYPE"], $arAnswers),
						"DROPDOWN" => Array(),
					);
					$foundMultiselect = true;
				}
			}
			else
			{
				if ($arAnswer["FIELD_TYPE"] == 4)
				{
					$arAnswer["FIELD_WIDTH"] = (intval($arAnswer["FIELD_WIDTH"]) > 0 ? intval($arAnswer["FIELD_WIDTH"]) : $defaultWidth);
				}
				elseif($arAnswer["FIELD_TYPE"] == 5)
				{
					$arAnswer["FIELD_WIDTH"] = (intval($arAnswer["FIELD_WIDTH"]) > 0 ? intval($arAnswer["FIELD_WIDTH"]) : $defaultWidth);
					$arAnswer["FIELD_HEIGHT"] = (intval($arAnswer["FIELD_HEIGHT"]) > 0 ? intval($arAnswer["FIELD_HEIGHT"]) : $defaultHeight);
				}

				$arQuestions[$questionIndex]["ANSWERS"][] = $arAnswer + Array("DROPDOWN" => Array(), "MULTISELECT" => Array());
			}
		}
		//Images
		$arQuestions[$questionIndex]["IMAGE"] = CFile::GetFileArray($arQuestions[$questionIndex]["IMAGE_ID"]);
	}

	//Vote Image
	$arVote["IMAGE"] = CFile::GetFileArray($arVote["IMAGE_ID"]);
}
else
{
	$arQuestions = Array();
	$arChannel = Array();
	$arVote = Array();
	$strError .= GetMessage("VOTE_ACCESS_DENIED");
}

$arResult = Array(
	"CHANNEL" => $arChannel,
	"VOTE" => $arVote,
	"QUESTIONS" => $arQuestions,
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