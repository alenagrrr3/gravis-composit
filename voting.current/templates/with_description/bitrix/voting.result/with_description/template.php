<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?=ShowNote($arResult["OK_MESSAGE"]);?>

<?if (!empty($arResult["VOTE"])):?>
<div class="voting-result-box">


	<?if (strlen($arResult["VOTE"]["TITLE"])>0) : ?>
		<b><?echo $arResult["VOTE"]["TITLE"];?></b><br />
	<?endif;?>

	<?if ($arResult["VOTE"]["DATE_START"]):?>
		<br /><?=GetMessage("VOTE_START_DATE")?>:&nbsp;<?echo $arResult["VOTE"]["DATE_START"]?>
	<?endif;?>

	<?if ($arResult["VOTE"]["DATE_END"] && $arResult["VOTE"]["DATE_END"]!="31.12.2030 23:59:59"):?>
			<br /><?=GetMessage("VOTE_END_DATE")?>:&nbsp;<?=$arResult["VOTE"]["DATE_END"]?>
	<?endif;?>

	<br /><?=GetMessage("VOTE_VOTES")?>:&nbsp;<?=$arResult["VOTE"]["COUNTER"]?>

	<?if ($arResult["VOTE"]["LAMP"]=="green"):?>
		<br /><span class="active"><?=GetMessage("VOTE_IS_ACTIVE")?></span>
	<?elseif ($arResult["VOTE"]["LAMP"]=="red"):?>
		<br /><span class="disable"><?=GetMessage("VOTE_IS_NOT_ACTIVE")?></span>
	<?endif;?>

	<br /><br />

	<?if ($arResult["VOTE"]["IMAGE"] !== false):?>
		<img src="<?=$arResult["VOTE"]["IMAGE"]["SRC"]?>" width="<?=$arResult["VOTE"]["IMAGE"]["WIDTH"]?>" height="<?=$arResult["VOTE"]["IMAGE"]["HEIGHT"]?>" hspace="3" vspace="3" align="left" border="0" />
		<?=$arResult["VOTE"]["DESCRIPTION"];?>
		<br clear="left" /><br />
	<?elseif(strlen($arResult["VOTE"]["DESCRIPTION"]) > 0):?>
		<?=$arResult["VOTE"]["DESCRIPTION"];?><br /><br />
	<?endif?>

	<?if (!empty($arResult["QUESTIONS"])):?>

		<?foreach ($arResult["QUESTIONS"] as $arQuestion):?>

			<?if ($arQuestion["IMAGE"] !== false):?>
				<img src="<?=$arQuestion["IMAGE"]["SRC"]?>" width="30" height="30" />
			<?endif?>

			<b><?=$arQuestion["QUESTION"]?></b><br />

			<?if ($arQuestion["DIAGRAM_TYPE"] == "circle"):?>

				<table width="100%">
					<tr>
						<td width="160"><img width="150" height="150" src="<?=$componentPath?>/draw_chart.php?qid=<?=$arQuestion["ID"]?>&dm=150" /></td>
						<td>
							<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
								<table class="vote-bar-table">
									<tr>
										<td><div class="vote-bar-square" style="background-color:#<?=$arAnswer["COLOR"]?>"></div></td>
										<td><?=$arAnswer["COUNTER"]?> (<?=$arAnswer["PERCENT"]?>%)</td>
										<td><?=$arAnswer["MESSAGE"]?></td>
									</tr>
								</table>
							<?endforeach?>
						</td>
					</tr>
				</table>

			<?else://histogram?>

				<table class="vote-answer-table">
				<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>
					<tr>
						<td width="30%"><?=$arAnswer["MESSAGE"]?></td>
						<td width="70%">
							<table class="vote-bar-table">
								<tr>
									<td style="width:<?=($arAnswer["BAR_PERCENT"])?>%;background-color:#<?=$arAnswer["COLOR"]?>"></td>
									<td style="width:<?=(100-$arAnswer["BAR_PERCENT"])?>%;" class="answer-counter"><nobr><?=$arAnswer["COUNTER"]?> (<?=$arAnswer["PERCENT"]?>%)</nobr></td>
								</tr>
							</table>
						</td>
					</tr>
				<?endforeach?>
				</table>

			<?endif?>
			<br />
		<?endforeach?>

	<?endif?>


</div>
<?endif?>