<?
//Functions
function _GetDictionaryInfoEx($arDictionary)
{
	$arID = $arReturn = Array();
	foreach ($arDictionary as $code => $arDic)
	{
		if ($arDic[1] == 0)
			$arReturn[$arDic[0]."_NAME"] = $arReturn[$arDic[0]."_DESC"] = $arReturn[$arDic[0]."_SID"] = "";
		else
			$arID[] = $arDic[1];
	}

	if (!empty($arID))
	{
		$rs = CTicketDictionary::GetList($v1, $v2, array("ID"=> $arID), $v3);
		while ($ar = $rs->Fetch())
		{
			if (array_key_exists($ar["C_TYPE"], $arDictionary))
			{
				$dic = $arDictionary[$ar["C_TYPE"]][0];
				$arReturn[$dic."_NAME"] = htmlspecialchars($ar["NAME"]);
				$arReturn[$dic."_DESC"] = htmlspecialchars($ar["DESCR"]);
				$arReturn[$dic."_SID"] = htmlspecialchars($ar["SID"]);
			}
		}
	}

	return $arReturn;
}

function _GetDropDownDictionary($TYPE, &$TICKET_DICTIONARY)
{
	$arReturn = Array();

	if (array_key_exists($TYPE, $TICKET_DICTIONARY))
	{
		foreach ($TICKET_DICTIONARY[$TYPE] as $key => $value)
		{
			$arReturn[$key] = htmlspecialchars($value["NAME"]);
		}
	}

	return $arReturn;
}


function _GetUserInfo($USER_ID, $CODE)
{
	static $arUsers;

	$arReturn = Array(
		$CODE."_NAME" =>"",
		$CODE."_LOGIN" =>""
	);

	if (is_array($arUsers) && array_key_exists($USER_ID, $arUsers))
	{
		$arReturn = Array(
			$CODE."_NAME" => $arUsers[$USER_ID]["NAME"],
			$CODE."_LOGIN" => $arUsers[$USER_ID]["LOGIN"]
		);
	}
	elseif ($USER_ID > 0)
	{
		$rsUser = CUser::GetByID($USER_ID);
		if ($arUser = $rsUser->GetNext())
		{
			$arUsers[$USER_ID] = Array(
				"NAME" => $arUser["NAME"]." ".$arUser["LAST_NAME"],
				"LOGIN" => $arUser["LOGIN"]
			);

			$arReturn = Array(
				$CODE."_NAME" => $arUser["NAME"]." ".$arUser["LAST_NAME"],
				$CODE."_LOGIN" => $arUser["LOGIN"]
			);
		}
	}

	return $arReturn;
}

?>