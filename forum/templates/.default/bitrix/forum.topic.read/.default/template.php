<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/forum.interface/templates/.default/script.js"></script>', true);
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arResult["SHOW_ICQ"] = (COption::GetOptionString("forum", "SHOW_ICQ_CONTACT", "N") != "Y") ? "N" : ($arParams["SEND_ICQ"] > "A" ? "Y" : "N");
$arResult["SHOW_MAIL"] = ($arParams["SEND_MAIL"] > "A" ? "Y" : "N");
$arParams["AJAX_TYPE"] = ($arParams["AJAX_TYPE"] == "Y" ? "Y" : "N");
$arParams["SHOW_RSS"] = ($arParams["SHOW_RSS"] == "N" ? "N" : "Y");
if ($arParams["SHOW_RSS"] == "Y"):
	$arParams["SHOW_RSS"] = (!$USER->IsAuthorized() ? "Y" : (CForumNew::GetUserPermission($arParams["FID"], array(2)) > "A" ? "Y" : "N"));
	$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" href="'.$arResult["URL"]["RSS"].'" />');
endif;
$iIndex = rand();
if ($_SERVER['REQUEST_METHOD'] == "POST"):
	$message = $_POST["message_id"];
	$action = strToUpper($_POST["ACTION"]);
else:
	$message = $_GET["message_id"];
	$action = strToUpper($_GET["ACTION"]);
endif;
$message = (is_array($message) ? $message : array($message));
/********************************************************************
				/Input params
********************************************************************/
if ($arParams["AJAX_TYPE"] == "Y")
	IncludeAJAX();

if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
?>
	<div class="forum-new-post">
		<a href="#postform"><span><?=GetMessage("F_REPLY")?></span></a>
	</div>
<?
endif;
?>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-options">
<?
if ($arParams["SHOW_RSS"] == "Y"):
?>
		<span class="forum-option-feed"><a href="<?=$arResult["URL"]["RSS"]?>">RSS</a></span>
<?
endif;
if ($USER->IsAuthorized() && empty($arResult["USER"]["SUBSCRIBE"])):
	if ($arParams["SHOW_RSS"] == "Y"):
		?>&nbsp;&nbsp;<?
	endif;
?>
	<span class="forum-option-subscribe"><a title="<?=GetMessage("F_SUBSCRIBE_TITLE")?>" href="<?
		?><?=$APPLICATION->GetCurPageParam("TOPIC_SUBSCRIBE=Y&".bitrix_sessid_get(), array("FORUM_SUBSCRIBE", "FORUM_SUBSCRIBE_TOPIC", "sessid"))?><?
			?>"><?=GetMessage("F_SUBSCRIBE")?></a></span>
<?
endif;
?>
	</div>
	<div class="forum-header-title"><span><?
	if ($arResult["TOPIC"]["STATE"] != "Y"):
		?><span class="forum-header-title-closed">[ <span><?=GetMessage("F_CLOSED")?></span> ]</span> <?
	endif;
	?><?=trim($arResult["TOPIC"]["TITLE"])?><?
 		if (strlen($arResult["TOPIC"]["DESCRIPTION"])>0):
			?>, <?=trim($arResult["TOPIC"]["DESCRIPTION"])?><?
		endif;
	
	?></span></div>
</div>
<?
if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y"):
?>
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
	?>onsubmit="return Validate(this)" name="MESSAGES_<?=$iIndex?>" id="MESSAGES_<?=$iIndex?>">
<?
endif;
?>
<div class="forum-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner">
<?
$iCount = 0;
foreach ($arResult["MESSAGE_LIST"] as $res):
$iCount++;
?>
			<table cellspacing="0" border="0" class="forum-post-table <?=($iCount == 1 ? "forum-post-first " : (
				 $iCount == count($arResult["MESSAGE_LIST"]) ? "forum-post-last " : ""))?><?=($iCount%2 == 1 ? "forum-post-odd " : "forum-post-even ")?><?
				 ?><?=($res["APPROVED"] == "Y" ? "" : " forum-post-hidden ")?> <?=(in_array($res["ID"], $message) ? " forum-post-selected " : "")?>" id="message<?=$res["ID"]?>">
				<tbody>
					<tr>
						<td class="forum-cell-user">
							<div class="forum-user-info">
<?
		if ($res["AUTHOR_ID"] > 0):
?>
								<div class="forum-user-name"><a href="<?=$res["URL"]["AUTHOR"]?>"><span><?=$res["AUTHOR_NAME"]?></span></a></div>
<?
			if (is_array($res["AVATAR"]) && (strLen($res["AVATAR"]["HTML"]) > 0)):
?>
								<div class="forum-user-avatar"><?
									?><a href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><?
										?><?=$res["AVATAR"]["HTML"]?></a></div>
<?
			else:
?>
								<div class="forum-user-register-avatar"><?
									?><a href="<?=$res["URL"]["AUTHOR"]?>" title="<?=GetMessage("F_AUTHOR_PROFILE")?>"><span><!-- ie --></span></a></div>
<?
			endif;
		else:
?>
								<div class="forum-user-name"><span><?=$res["AUTHOR_NAME"]?></span></div>
								<div class="forum-user-guest-avatar"><!-- ie --></div>
								<div class="forum-guest-avatar"></div>
<?
		endif;
 		
		if (strLen(trim($res["AUTHOR_STATUS"]))):
?>
								<div class="forum-user-status"><span><?=$res["AUTHOR_STATUS"]?></span></div>
<?
		endif;
?>
								<div class="forum-user-additional">
<?
		if (intVal($res["NUM_POSTS"]) > 0):
?>
									<span><?=GetMessage("F_NUM_MESS")?> <span><a href="<?=$res["URL"]["AUTHOR_POSTS"]?>"><?=$res["NUM_POSTS"]?></a></span></span>
<?
		endif;
		
		if (COption::GetOptionString("forum", "SHOW_VOTES", "Y")=="Y" && $res["AUTHOR_ID"] > 0 && $res["NUM_POINTS"] > 0):
?>
									<span><?=GetMessage("F_POINTS")?> <span><?=$res["NUM_POINTS"]?></span><?
			if ($res["VOTES"]["ACTION"] == "VOTE" || $res["VOTES"]["ACTION"] == "UNVOTE"):
									?>&nbsp;(<span class="forum-vote-user"><?
										?><a href="<?=$res["URL"]["AUTHOR_VOTE"]?>" title="<?
											?><?=($res["VOTES"]["ACTION"] == "VOTE" ? GetMessage("F_NO_VOTE_DO") : GetMessage("F_NO_VOTE_UNDO"));?>"><?
											?><?=($res["VOTES"]["ACTION"] == "VOTE" ? "+" : "-");?></a></span>)<?
			endif;
									?></span>
<?
		endif;
		if (strlen($res["~DATE_REG"]) > 0):
?>
									<span><?=GetMessage("F_DATE_REGISTER")?> <span><?=$res["DATE_REG"]?></span></span>
<?
		endif;
		
		if ($arResult["USER"]["PERMISSION"] >= "Q"):
			if ($res["IP_IS_DIFFER"] == "Y"):
?>								
									<span>IP<?=GetMessage("F_REAL_IP")?>: <span><?=$res["AUTHOR_IP"];?> / <?=$res["AUTHOR_REAL_IP"];?></span></span>
<?
			else:
?>								
									<span>IP: <span><?=$res["AUTHOR_IP"];?></span></span>
<?
			endif;
		endif;
?>
								</div>
							</div>
						</td>
						<td class="forum-cell-post">
							<div class="forum-post-date">
								<div class="forum-post-number"><a href="http://<?=$_SERVER["HTTP_HOST"]?><?=$res["URL"]["MESSAGE"]?>#message<?=$res["ID"]?>" <?
									?>onclick="prompt(oText['ml'], this.href); return false;" title="<?=GetMessage("F_ANCHOR")?>" rel="nofollow">#<?=$res["NUMBER"]?></a><?
							if ($arResult["USER"]["PERMISSION"] >= "Q"):
								?>&nbsp;<input type="checkbox" name="message_id[]" value="<?=$res["ID"]?>" id="message_id_<?=$res["ID"]?>_" <?
								if (in_array($res["ID"], $message)):
								?> checked="checked" <?
								endif;
									?> onclick="SelectPost(this.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode)" /><?
							endif;
								?></div>
								<span><?=$res["POST_DATE"]?></span>
							</div>
							<div class="forum-post-entry">
								<div class="forum-post-text" id="message_text_<?=$res["ID"]?>"><?=$res["POST_MESSAGE_TEXT"]?></div>
<?
							if (!empty($res["FILES"])):
?>								
								<div class="forum-post-attachments">
									<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
								foreach ($res["FILES"] as $arFile): 
?>								
									<div class="forum-post-attachment"><?
									?><?$GLOBALS["APPLICATION"]->IncludeComponent(
										"bitrix:forum.interface", "show_file",
										Array(
											"FILE" => $arFile,
											"WIDTH" => $arResult["PARSER"]->image_params["width"],
											"HEIGHT" => $arResult["PARSER"]->image_params["height"],
											"CONVERT" => "N",
											"FAMILY" => "FORUM",
											"SINGLE" => "Y",
											"RETURN" => "N",
											"SHOW_LINK" => "Y"),
										null,
										array("HIDE_ICONS" => "Y"));
									?></div>
<?
								endforeach;
?>
								</div>
<?
							endif;

							if (!empty($res["EDITOR_NAME"])):
							?><div class="forum-post-lastedit">
								<span class="forum-post-lastedit"><?=GetMessage("F_EDIT_HEAD")?> 
									<span class="forum-post-lastedit-user"><?
								if (!empty($res["URL"]["EDITOR"])):
										?><a href="<?=$res["URL"]["EDITOR"]?>"><?=$res["EDITOR_NAME"]?></a><?
								else:
										?><?=$res["EDITOR_NAME"]?><?
								endif;
									?></span> - <span class="forum-post-lastedit-date"><?=$res["EDIT_DATE"]?></span>
<?
								if (!empty($res["EDIT_REASON"])):
?>
								<span class="forum-post-lastedit-reason">(<span><?=$res["EDIT_REASON"]?></span>)</span>
<?
								endif;
?>
							</span></div><?
							endif;
							
							if (strLen($res["SIGNATURE"]) > 0):
?>
								<div class="forum-user-signature">
									<div class="forum-signature-line"></div>
									<span><?=$res["SIGNATURE"]?></span>
								</div>
<?
							endif;
?>
							</div>
						</td>
					</tr>
					<tr>
						<td class="forum-cell-contact">
							<div class="forum-contact-links">
<?
					if ($res["AUTHOR_ID"] > 0 && $USER->IsAuthorized()):
?>
								<span class="forum-contact-message"><a href="<?=$res["URL"]["AUTHOR_PM"]?>" title="<?=GetMessage("F_PRIVATE_MESSAGE_TITLE")?>"><?
									?><?=GetMessage("F_PRIVATE_MESSAGE")?></a></span>&nbsp;&nbsp;
<?
					endif;
					if ($arResult["SHOW_MAIL"] == "Y" && strlen($res["EMAIL"]) > 0):
?>
							<span class="forum-contact-email"><a href="<?=$res["URL"]["AUTHOR_EMAIL"]?>" title="<?=GetMessage("F_EMAIL_TITLE")?>">E-mail</a></span>
<?
					elseif (!($res["AUTHOR_ID"] > 0 && $USER->IsAuthorized())):
?>
							&nbsp;
<?
					endif;
?>
							</div>
						</td>
						<td class="forum-cell-actions">
							<div class="forum-action-links">
<?
				if ($res["NUMBER"] == 1):
					if ($res["PANELS"]["MODERATE"] == "Y"):
						if ($arResult["TOPIC"]["APPROVED"] != "Y"):
?>
								<span class="forum-action-show"><a href="<?
								 	?><?=$APPLICATION->GetCurPageParam("ACTION=SHOW_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>"><?
									?><?=GetMessage("F_SHOW_TOPIC")?></a></span>
<?

						elseif (false):
?>
								<span class="forum-action-hide"><a href="<?
								 	?><?=$APPLICATION->GetCurPageParam("ACTION=HIDE_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>"><?
									?><?=GetMessage("F_HIDE_TOPIC")?></a></span>
<?
						endif;
					endif;
					if ($res["PANELS"]["DELETE"] == "Y"):
?>
								 &nbsp;&nbsp;<span class="forum-action-delete"><a href="<?
								 	?><?=$APPLICATION->GetCurPageParam("ACTION=DEL_TOPIC&".bitrix_sessid_get(), array("ACTION", "sessid"))?>" <?
								 	?> onclick="return confirm(oText['cdt']);"><?=GetMessage("F_DELETE_TOPIC")?></a></span>
<?
					endif;
					if ($res["PANELS"]["EDIT"] == "Y" && $arResult["USER"]["PERMISSION"] >= "U"):
?>
								 &nbsp;&nbsp;<span class="forum-action-edit"><a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT_TOPIC")?></a></span>
<?
					elseif ($res["PANELS"]["EDIT"] == "Y"):
?>
								 &nbsp;&nbsp;<span class="forum-action-edit"><a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></span>
<?
					endif;
				else:
					if ($res["PANELS"]["MODERATE"] == "Y"):
						if ($res["APPROVED"] == "Y"):
?>
								<span class="forum-action-hide"><a href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_HIDE")?></a></span>&nbsp;&nbsp;
<?
						else:
?>
								<span class="forum-action-show"><a href="<?=$res["URL"]["MODERATE"]?>"><?=GetMessage("F_SHOW")?></a></span>&nbsp;&nbsp;
<?
						endif;
					endif;
					if ($res["PANELS"]["DELETE"] == "Y"):
?>
								 <span class="forum-action-delete"><a href="<?=$res["URL"]["DELETE"]?>" <?
								 	?>onclick="return confirm(oText['cdm']);"><?=GetMessage("F_DELETE")?></a></span>&nbsp;&nbsp;
<?
					endif;
					if ($res["PANELS"]["EDIT"] == "Y"):
?>
								 <span class="forum-action-edit"><a href="<?=$res["URL"]["EDIT"]?>"><?=GetMessage("F_EDIT")?></a></span>&nbsp;&nbsp;
<?
					endif;
			endif;
			
			if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
				if ($res["NUMBER"] == 1):
					?>&nbsp;&nbsp;<?
				endif;
				if ($arResult["FORUM"]["ALLOW_QUOTE"] == "Y"):
?>
								<span class="forum-action-quote"><a href="#postform" <?
									?> onmousedown="if (window['quoteMessageEx']){quoteMessageEx('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>', 'message_text_<?=$res["ID"]?>')}"><?
									?><?=GetMessage("F_QUOTE")?></a></span>
<?
				else:
?>
								<span class="forum-action-reply"><a href="#postform" <?
									?> onmousedown="reply2author('<?=$res["FOR_JS"]["AUTHOR_NAME"]?>,', 'message_text_<?=$res["ID"]?>')"><?
									?><?=GetMessage("F_REPLY")?></a></span>
<?
				endif;
			endif;
?>
							</div>
						</td>
					</tr>
				</tbody>
<?
	if ($iCount < count($arResult["MESSAGE_LIST"])):
?>
			</table>
<?
	endif;
endforeach;
?>
				 <tfoot>
					<tr>
						<td colspan="5" class="forum-column-footer">
							<div class="forum-footer-inner">
<?
if ($arResult["USER"]["RIGHTS"]["MODERATE"] == "Y"):
?>
								<?=bitrix_sessid_post()?>
								<input type="hidden" name="PAGE_NAME" value="read" />
								<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
								<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
								<div class="forum-post-moderate">
									<select name="ACTION">
										<option value=""><?=GetMessage("F_MANAGE_MESSAGES")?></option>
										<option value="HIDE"><?=GetMessage("F_HIDE_MESSAGES")?></option>
										<option value="SHOW"><?=GetMessage("F_SHOW_MESSAGES")?></option>
										<option value="MOVE"><?=GetMessage("F_MOVE_MESSAGES")?></option>
<?
	if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):
?>
										<option value="DEL"><?=GetMessage("F_DELETE_MESSAGES")?></option>
<?
	endif;
?>
									</select>&nbsp;<input type="submit" value="OK" />
								</div>
							</form>
							<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST" <?
								?>onsubmit="return Validate(this)" name="TOPIC_<?=$iIndex?>" id="TOPIC_<?=$iIndex?>">
								<div class="forum-topic-moderate">
									<?=bitrix_sessid_post()?>
									<input type="hidden" name="PAGE_NAME" value="read" />
									<input type="hidden" name="FID" value="<?=$arParams["FID"]?>" />
									<input type="hidden" name="TID" value="<?=$arParams["TID"]?>" />
									
									<select name="ACTION">
									<option value=""><?=GetMessage("F_MANAGE_TOPIC")?></option>
									<option value="<?=($arResult["TOPIC"]["APPROVED"] == "Y" ? "HIDE_TOPIC" : "SHOW_TOPIC")?>"><?
										?><?=($arResult["TOPIC"]["APPROVED"] == "Y" ? GetMessage("F_HIDE_TOPIC") : GetMessage("F_SHOW_TOPIC"))?></option>
									<option value="<?=($arResult["TOPIC"]["SORT"] != 150 ? "SET_ORDINARY" : "SET_TOP")?>"><?
										?><?=($arResult["TOPIC"]["SORT"] != 150 ? GetMessage("F_UNPINN_TOPIC") : GetMessage("F_PINN_TOPIC"))?></option>
									<option value="<?=($arResult["TOPIC"]["STATE"] == "Y" ? "STATE_N" : "STATE_Y")?>"><?
										?><?=($arResult["TOPIC"]["STATE"] == "Y" ? GetMessage("F_CLOSE_TOPIC") : GetMessage("F_OPEN_TOPIC"))?></option>
									<option value="MOVE_TOPIC"><?=GetMessage("F_MOVE_TOPIC")?></option>
<?
	if ($arResult["USER"]["RIGHTS"]["EDIT"] == "Y"):
?>
									<option value="EDIT_TOPIC"><?=GetMessage("F_EDIT_TOPIC")?></option>
									<option value="DEL_TOPIC"><?=GetMessage("F_DELETE_TOPIC")?></option>
<?
	endif;
?>
									</select>&nbsp;<input type="submit" value="OK" />
								</div>
							</form>
<?
else:
?>
							&nbsp;
<?
endif;
?>
			
							</div>
						</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
<?
if ($arResult["USER"]["RIGHTS"]["ADD_MESSAGE"] == "Y"):
?>
	<div class="forum-new-post">
		<a href="#postform"><span><?=GetMessage("F_REPLY")?></span></a>
	</div>
<?
endif;
?>
	<div class="forum-clear-float"></div>
</div>

<?
endif;
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-success">
	<div class="forum-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"], "forum-note-success")?></div>
</div>
<?
endif;

// View new posts
if ($arResult["VIEW"] == "Y"):
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_VIEW")?></span></div>
</div>

<div class="forum-info-box forum-post-preview">
	<div class="forum-info-box-inner">
		<div class="forum-post-entry">
			<div class="forum-post-text"><?=$arResult["MESSAGE_VIEW"]["TEXT"]?></div>
<?
		if (!empty($arResult["MESSAGE_VIEW"]["FILES"])):
?>								
			<div class="forum-post-attachments">
				<label><?=GetMessage("F_ATTACH_FILES")?></label>
<?
			foreach ($arResult["MESSAGE_VIEW"]["FILES"] as $arFile): 
?>								
				<div class="forum-post-attachment"><?
				?><?$GLOBALS["APPLICATION"]->IncludeComponent(
					"bitrix:forum.interface", "show_file",
					Array(
						"FILE" => $arFile,
						"WIDTH" => $arResult["PARSER"]->image_params["width"],
						"HEIGHT" => $arResult["PARSER"]->image_params["height"],
						"CONVERT" => "N",
						"FAMILY" => "FORUM",
						"SINGLE" => "Y",
						"RETURN" => "N",
						"SHOW_LINK" => "Y"),
					null,
					array("HIDE_ICONS" => "Y"));
				?></div>
<?
			endforeach;
?>
			</div>
<?
		endif;
?>
		</div>
	</div>
</div>
<?
endif;
	

?><script type="text/javascript">
<?if (intVal($arParams["MID"]) > 0):?>
location.hash = 'message<?=$arParams["MID"]?>';
<?endif;?>
if (typeof oText != "object")
	var oText = {};
oText['cdt'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_TOPIC_CONFIRM"))?>';
oText['cdm'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_CONFIRM"))?>';
oText['cdms'] = '<?=CUtil::addslashes(GetMessage("F_DELETE_MESSAGES_CONFIRM"))?>';
oText['ml'] = '<?=CUtil::addslashes(GetMessage("F_ANCHOR_TITLE"))?>';
oText['no_data'] = '<?=CUtil::addslashes(GetMessage('JS_NO_MESSAGES'))?>';
oText['no_action'] = '<?=CUtil::addslashes(GetMessage('JS_NO_ACTION'))?>';
oText['quote_text'] = '<?=CUtil::addslashes(GetMessage("JQOUTE_AUTHOR_WRITES"));?>';

function reply2author(name)
{
	if (document.REPLIER.POST_MESSAGE)
	{
		document.REPLIER.POST_MESSAGE.value += <?=(($arResult["FORUM"]["ALLOW_BIU"] == "Y") ? "'[b]'+name+'[/b]'" : "name")?> + " \n";
	}
	return false;
}
</script>