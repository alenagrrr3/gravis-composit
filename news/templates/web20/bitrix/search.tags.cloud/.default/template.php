<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($arParams["SHOW_CHAIN"] != "N" && !empty($arResult["TAGS_CHAIN"]))
{
	?><div class="search-tags-chain" <?=$arParams["WIDTH"]?>><?
		foreach ($arResult["TAGS_CHAIN"] as $tags):
			?><a href="<?=$tags["TAG_PATH"]?>"><?=$tags["TAG_NAME"]?></a> <?
			?>[<a href="<?=$tags["TAG_WITHOUT"]?>" class="search-tags-link">x</a>]  <?
		endforeach;
	?></div><?
}

if (is_array($arResult["SEARCH"]))
{
	?><div class="search-tags-cloud" <?=$arParams["WIDTH"]?>><?
		foreach ($arResult["SEARCH"] as $key => $res)
		{
		?><a href="<?=$res["URL"]?>" style="font-size: <?=$res["FONT_SIZE"]?>px; color: #<?=$res["COLOR"]?>;px"><?=$res["NAME"]?></a> <?
		}
	?></div><?
}?>