<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (is_array($arResult['VALUE']) && count($arResult['VALUE']) > 0)
{
	if(!CModule::IncludeModule("iblock"))
		return;

	$arValue = array();
	$dbRes = CIBlockSection::GetList(array('left_margin' => 'asc'), array('ID' => $arResult['VALUE']), false);
	while ($arRes = $dbRes->Fetch())
	{
		$arValue[$arRes['ID']] = $arRes['NAME'];
	}
	$arResult['VALUE'] = $arValue;
}

?>