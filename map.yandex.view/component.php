<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['KEY'] = trim($arParams['KEY']);

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 

if (($strPositionInfo = $arParams['~MAP_DATA']) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	if (is_array($arResult['POSITION']) && is_array($arResult['POSITION']['PLACEMARKS']) && ($cnt = count($arResult['POSITION']['PLACEMARKS'])))
	{
		for ($i = 0; $i < $cnt; $i++)
		{
			$arResult['POSITION']['PLACEMARKS'][$i]['TEXT'] = str_replace('###RN###', "\r\n", $arResult['POSITION']['PLACEMARKS'][$i]['TEXT']);
		}
	}

	$this->IncludeComponentTemplate();
}
else
{
	ShowError(GetMessage('MYMV_NO_POSITION'));
	return;
}
?>