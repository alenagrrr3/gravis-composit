<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$allNum = count($arResult["arMap"]);
$colNum = ceil($allNum / $arParams["COL_NUM"]);

if (is_array($arResult["arMap"]))
{
?>
<table class="map-columns">
<tr>
	<td>
<?
	$level = -1;
	$counter = 0;
	foreach ($arResult["arMap"] as $arItem)
	{
		if ($arItem["LEVEL"] < $level)
		{
			for ($i = $arItem["LEVEL"]; $i<$level; $i++)
			{
			?></ul>
<?
			}
		}
	
		if ($counter >= $colNum)
		{
			if ($arItem["LEVEL"] == 0)
			{
				$counter = 0;
			?>
</ul></td><td><ul class="map-level-0">
<?
			}
		}

		if ($arItem["LEVEL"] > $level)
		{
		?><ul class="map-level-<?=$arItem["LEVEL"]?>">
<?
		}

	
		$level = $arItem["LEVEL"];
		?><li><a href="<?=$arItem["FULL_PATH"]?>" ><?=$arItem["NAME"]?></a><?if ($arParams["SHOW_DESCRIPTION"] == "Y" && strlen($arItem["DESCRIPTION"]) > 0) {?><div><?=$arItem["DESCRIPTION"]?></div><?}?>
		
		</li>
<?
		$counter++;
	}
	
	for ($i = $level; $i>=0; $i--)
	{
	?></ul>
<?
	}

?>
	</td>
</tr>
</table>
<?
	
}
?>