<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['KEY'] = trim($arParams['KEY']);

if (!$arParams['KEY'])
{
	$MAP_KEY = '';
	$strMapKeys = COPtion::GetOptionString('fileman', 'map_google_keys');

	$strDomain = $_SERVER['HTTP_HOST'];
	$wwwPos = strpos($strDomian, 'www.');
	if ($wwwPos === 0)
		$strDomain = substr($strDomain, 4);

	if ($strMapKeys)
	{
		$arMapKeys = unserialize($strMapKeys);
		
		if (array_key_exists($strDomain, $arMapKeys))
			$MAP_KEY = $arMapKeys[$strDomain];
	}
	
	if (!$MAP_KEY)
	{
		ShowError(GetMessage('MYMS_ERROR_NO_KEY'));
		return;
	}
	else
		$arParams['KEY'] = $MAP_KEY;
}

$arParams['MAP_ID'] = 
	(strlen($arParams["MAP_ID"])<=0 || !ereg("^[A-Za-z_][A-Za-z01-9_]*$", $arParams["MAP_ID"])) ? 
	'MAP_'.RandString() : $arParams['MAP_ID']; 

$current_search = $_GET['ys'];

if (($strPositionInfo = $arParams['~MAP_DATA']) && ($arResult['POSITION'] = unserialize($strPositionInfo)))
{
	$arParams['INIT_MAP_LON'] = $arResult['POSITION']['google_lon'];
	$arParams['INIT_MAP_LAT'] = $arResult['POSITION']['google_lat'];
	$arParams['INIT_MAP_SCALE'] = $arResult['POSITION']['google_scale'];

	$this->IncludeComponentTemplate();
}
else
{
	ShowError(GetMessage('MYMS_NO_POSITION'));
	return;
}
?>