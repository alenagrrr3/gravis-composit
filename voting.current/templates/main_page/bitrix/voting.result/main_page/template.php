<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult["VOTE"]) && !empty($arResult["QUESTIONS"])):?>
		<?foreach ($arResult["QUESTIONS"] as $arQuestion):?>
				<b><?=$arQuestion["QUESTION"]?></b><br />
				<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
						<?=$arAnswer["MESSAGE"]?> - <?=$arAnswer["COUNTER"]?> (<?=$arAnswer["PERCENT"]?>%)<br />
						<div class="graph-bar" style="width: <?=$arAnswer["BAR_PERCENT"]?>%;background-color:#<?=$arAnswer["COLOR"]?>">&nbsp;</div>
				<?endforeach?>
				<br />
		<?endforeach?>
<?endif?>