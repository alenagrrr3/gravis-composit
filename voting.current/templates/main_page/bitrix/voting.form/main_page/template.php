<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?=ShowNote($arResult["OK_MESSAGE"]);?>

<?if (!empty($arResult["QUESTIONS"])):?>

<div class="voting-form-box">
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

	</form>

</div>

<?endif?>