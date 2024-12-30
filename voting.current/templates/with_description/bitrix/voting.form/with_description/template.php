<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?=ShowNote($arResult["OK_MESSAGE"]);?>

<?if (!empty($arResult["VOTE"])):?>

<div class="voting-form-box">

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
		<br clear="left" />
	<?else:?>
		<?=$arResult["VOTE"]["DESCRIPTION"];?>
	<?endif?>

	<?if (!empty($arResult["QUESTIONS"])):?>

		<form action="<?=$arResult["CURRENT_PAGE"]?>" method="post">
		<input type="hidden" name="vote" value="Y">
		<input type="hidden" name="PUBLIC_VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>">
		<input type="hidden" name="VOTE_ID" value="<?=$arResult["VOTE"]["ID"]?>">

		<?foreach ($arResult["QUESTIONS"] as $arQuestion):?>

			<?if ($arQuestion["IMAGE"] !== false):?>
				<img src="<?=$arQuestion["IMAGE"]["SRC"]?>" width="30" height="30" />
			<?endif?>

			<b><?=$arQuestion["QUESTION"]?></b><br /><br />

			<?foreach ($arQuestion["ANSWERS"] as $arAnswer):?>

				<?switch ($arAnswer["FIELD_TYPE"]):
					case 0://radio?>
						<label><input type="radio" name="vote_radio_<?=$arAnswer["QUESTION_ID"]?>" value="<?=$arAnswer["ID"]?>" <?=$arAnswer["FIELD_PARAM"]?> />&nbsp;<?=$arAnswer["MESSAGE"]?></label>
						<br />
					<?break?>

					<?case 1://checkbox?>
						<label><input type="checkbox" name="vote_checkbox_<?=$arAnswer["QUESTION_ID"]?>[]" value="<?=$arAnswer["ID"]?>" <?=$arAnswer["FIELD_PARAM"]?> />&nbsp;<?=$arAnswer["MESSAGE"]?></label>
						<br />
					<?break?>

					<?case 2://dropdown?>
						<select name="vote_dropdown_<?=$arAnswer["QUESTION_ID"]?>" <?=$arAnswer["FIELD_PARAM"]?>>
						<?foreach ($arAnswer["DROPDOWN"] as $arDropDown):?>
							<option value="<?=$arDropDown["ID"]?>"><?=$arDropDown["MESSAGE"]?></option>
						<?endforeach?>
						</select><br />
					<?break?>

					<?case 3://multiselect?>
						<select name="vote_multiselect_<?=$arAnswer["QUESTION_ID"]?>[]" <?=$arAnswer["FIELD_PARAM"]?> multiple="multiple">
						<?foreach ($arAnswer["MULTISELECT"] as $arMultiSelect):?>
							<option value="<?=$arMultiSelect["ID"]?>"><?=$arMultiSelect["MESSAGE"]?></option>
						<?endforeach?>
						</select><br />
					<?break?>

					<?case 4://text field?>
						<label><?if (strlen(trim($arAnswer["MESSAGE"]))>0):?>
							<?=$arAnswer["MESSAGE"]?><br />
						<?endif?>
						<input type="text" name="vote_field_<?=$arAnswer["ID"]?>" value="" size="<?=$arAnswer["FIELD_WIDTH"]?>" <?=$arAnswer["FIELD_PARAM"]?> /></label>
						<br />
					<?break?>

					<?case 5://memo?>
						<label><?if (strlen(trim($arAnswer["MESSAGE"]))>0):?>
							<?=$arAnswer["MESSAGE"]?><br />
						<?endif?>
						<textarea name="vote_memo_<?=$arAnswer["ID"]?>" <?=$arAnswer["FIELD_PARAM"]?> cols="<?=$arAnswer["FIELD_WIDTH"]?>" rows="<?=$arAnswer["FIELD_HEIGHT"]?>"></textarea></label>
						<br />
					<?break?>

				<?endswitch?>

			<?endforeach?>
			<br />
		<?endforeach?>

		
		<input type="submit" name="vote" value="<?=GetMessage("VOTE_SUBMIT_BUTTON")?>">&nbsp;&nbsp;
		<input type="reset" value="<?=GetMessage("VOTE_RESET")?>">
		</form>

	<?endif?>

</div>
<?endif?>