<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (strlen($arResult["NAV_STRING"]) > 0):?>
	<?=$arResult["NAV_STRING"]?><br /><br />
<?endif?>

<?foreach ($arResult["VOTES"] as $arVote):?>

	<div class="voting-list-box">

		<div class="float-links">
			<?if ($arVote["LAMP"]=="green" && $arVote["MAX_PERMISSION"]>=2):?>
				[&nbsp;<a href="<?=$arVote["VOTE_FORM_URL"]?>"><?=GetMessage("VOTE_VOTING")?></a>&nbsp;]
			<?endif;?>

			<?if ($arVote["MAX_PERMISSION"]>=1):?>
				&nbsp;&nbsp;[&nbsp;<a href="<?=$arVote["VOTE_RESULT_URL"]?>"><?=GetMessage("VOTE_RESULTS")?></a>&nbsp;]
			<?endif;?>
		</div>

		<?if (strlen($arVote["TITLE"])>0) : ?>
			<div align="left"><b><?echo $arVote["TITLE"];?></b></div><br />
		<?endif;?>

		<?if ($arVote["DATE_START"]):?>
			<br /><?=GetMessage("VOTE_START_DATE")?>:&nbsp;<?echo $arVote["DATE_START"]?>
		<?endif;?>

		<?if ($arVote["DATE_END"] && $arVote["DATE_END"]!="31.12.2030 23:59:59"):?>
				<br /><?=GetMessage("VOTE_END_DATE")?>:&nbsp;<?=$arVote["DATE_END"]?>
		<?endif;?>
		
		<br /><?=GetMessage("VOTE_VOTES")?>:&nbsp;<?=$arVote["COUNTER"]?>
		
		<?if ($arVote["LAMP"]=="green"):?>
			<br /><span class="active"><?=GetMessage("VOTE_IS_ACTIVE")?></span>
		<?elseif ($arVote["LAMP"]=="red"):?>
			<br /><span class="disable"><?=GetMessage("VOTE_IS_NOT_ACTIVE")?></span>
		<?endif;?>

		<br /><br />

		<?if($arVote["IMAGE"] !== false):?>
			<img src="<?=$arVote["IMAGE"]["SRC"]?>" width="<?=$arVote["IMAGE"]["WIDTH"]?>" height="<?=$arVote["IMAGE"]["HEIGHT"]?>" hspace="3" vspace="3" align="left" border="0" />
			<?=$arVote["DESCRIPTION"];?>
			<br clear="left" />
		<?else:?>
			<?=$arVote["DESCRIPTION"];?>
		<?endif?>


	</div>
	<br />

<?endforeach?>

<?=$arResult["NAV_STRING"]?>