<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-comments">
<?
include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");

if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo">
		<div class="blog-textinfo-text">
			<?=$arResult["MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<?=$arResult["ERROR_MESSAGE"]?>
		</div>
	</div>
	<?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<?=$arResult["FATAL_MESSAGE"]?>
		</div>
	</div>
	<?
}
else
{
	?>
	<script>
	<!--
	if(document.attachEvent && !(navigator.userAgent.toLowerCase().indexOf('opera') != -1))
		var imgLoaded = false;
	else
		var imgLoaded = true;
	function imageLoaded()
	{
		imgLoaded = true;
	}
	//-->
	</script>

	<div id="form_comment_" style="display:none;">
		<div id="form_c_del">
		<div class="blog-comment-form">
		
		<form method="POST" name="form_comment" id="form_comment" action="<?=POST_FORM_ACTION_URI?>">
		<input type="hidden" name="parentId" id="parentId" value="">
		<?=bitrix_sessid_post()?>
		
		<div class="blog-comment-fields">
			<?
			if(empty($arResult["User"]))
			{
				?>
				<div class="blog-comment-field-user">
					<div class="blog-comment-field blog-comment-field-author"><div class="blog-comment-field-text"><label for="user_name"><?=GetMessage("B_B_MS_NAME")?></label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="3" type="text" name="user_name" id="user_name" value="<?=htmlspecialcharsEx($_SESSION["blog_user_name"])?>"></span></div>
					<div class="blog-comment-field-user-sep">&nbsp;</div>
					<div class="blog-comment-field blog-comment-field-email"><div class="blog-comment-field-text"><label for="">E-mail</label><span class="blog-required-field">*</span></div><span><input maxlength="255" size="30" tabindex="4" type="text" name="user_email" id="user_email" value="<?=htmlspecialcharsEx($_SESSION["blog_user_email"])?>"></span></div>
					<div class="blog-clear-float"></div>
				</div>
				<?
			}
			?>
			<div class="blog-comment-field blog-comment-field-bbcode">

				<div class="blog-bbcode-line">
					<a id=bold class="blog-bbcode-bold" href='javascript:simpletag("B")' title="<?=GetMessage("BPC_BOLD")?>"></a>
					<a id=italic class="blog-bbcode-italic" href='javascript:simpletag("I")' title="<?=GetMessage("BPC_ITALIC")?>"></a>
					<a id=under class="blog-bbcode-underline" href='javascript:simpletag("U")' title="<?=GetMessage("BPC_UNDER")?>"></a>
					<a id=strike class="blog-bbcode-strike" href='javascript:simpletag("S")' title="<?=GetMessage("BPC_STRIKE")?>"></a>
					<a id=url class="blog-bbcode-url" href='javascript:tag_url()' title="<?=GetMessage("BPC_HYPERLINK")?>"></a>
					<a id=image class="blog-bbcode-img" href='javascript:tag_image()' title="<?=GetMessage("BLOG_P_INSERT_IMAGE_LINK")?>"></a>
										
					<a id=quote class="blog-bbcode-quote" href='javascript:quoteMessage()' title="<?=GetMessage("BPC_QUOTE")?>"></a>
					<a id=code class="blog-bbcode-code" href='javascript:simpletag("CODE")' title="<?=GetMessage("BPC_CODE")?>"></a>
					<a id=list class="blog-bbcode-list" href='javascript:tag_list()' title="<?=GetMessage("BPC_LIST")?>"></a>
					<a id=FontColor	class="blog-bbcode-color" href='javascript:ColorPicker()' title="<?=GetMessage("BPC_IMAGE")?>"></a>

					<select class="blog-bbcode-font" name="ffont" id="select_font" onchange="alterfont(this.options[this.selectedIndex].value, 'FONT')">
						<option value='0'><?=GetMessage("BPC_FONT")?></option>
						<option value='Arial' style='font-family:Arial'>Arial</option>
						<option value='Times' style='font-family:Times'>Times</option>
						<option value='Courier' style='font-family:Courier'>Courier</option>
						<option value='Impact' style='font-family:Impact'>Impact</option>
						<option value='Geneva' style='font-family:Geneva'>Geneva</option>
						<option value='Optima' style='font-family:Optima'>Optima</option>
						<option value='Verdana' style='font-family:Verdana'>Verdana</option>
					</select>
					<div class="blog-clear-float"></div>
				</div>
								
				<div class="blog-smiles-line">
					<?
					foreach($arResult["Smiles"] as $arSmiles)
					{
						?>
							<img src="/bitrix/images/blog/smile/<?=$arSmiles["IMAGE"]?>" width="<?=$arSmiles["IMAGE_WIDTH"]?>" height="<?=$arSmiles["IMAGE_HEIGHT"]?>" title="<?=$arSmiles["LANG_NAME"]?>" OnClick="emoticon('<?=$arSmiles["TYPE"]?>')" style="cursor:pointer"<?if($arResult["use_captcha"]!==true) echo ' onload="imageLoaded()"'?>>
						<?
					}
					?>
					<div class="blog-clear-float"></div>
				</div>
				<div class="blog-bbcode-closeall"><a id=close_all style=visibility:hidden href='javascript:closeall()' title='<?=GetMessage("BPC_CLOSE_OPENED_TAGS")?>'><?=GetMessage("BPC_CLOSE_ALL_TAGS")?></a></div>
				<div class="blog-clear-float"></div>
			</div>
				
			<div class="blog-comment-field blog-comment-field-text">
				<textarea cols="55" rows="10" tabindex="6" id="comment" onKeyPress="check_ctrl_enter(arguments[0])" name="comment"></textarea>
			</div>
			<?
			if($arResult["use_captcha"]===true)
			{
				?>
				<div class="blog-comment-field blog-comment-field-captcha">
					<div class="blog-comment-field-captcha-label">
						<label for=""><?=GetMessage("B_B_MS_CAPTCHA_SYM")?></label><span class="blog-required-field">*</span><br>
						<input type="hidden" name="captcha_code" id="captcha_code" value="<?=$arResult["CaptchaCode"]?>">
						<input type="text" size="30" name="captcha_word" id="captcha_word" value=""  tabindex="7">
						</div>
					<div class="blog-comment-field-captcha-image"><div id="div_captcha"></div></div>
				</div>
				<?
			}
			?>

			<div class="blog-comment-buttons">
				<input type="hidden" name="post" value="Y">
				<input tabindex="10" value="<?=GetMessage("B_B_MS_SEND")?>" type="submit" name="post">
				<input tabindex="11" name="preview" value="<?=GetMessage("B_B_MS_PREVIEW")?>" type="submit">
			</div>
			
		</div>
		</form>
		</div>
	</div>
	</div>
	<?
	if($arResult["use_captcha"]===true)
	{
		?>
		<div id="captcha_del">
		<script>
			<!--
			var cc;
			if(document.cookie.indexOf('<?echo session_name()?>'+'=') == -1)
				cc = Math.random();
			else
				cc ='<?=$arResult["CaptchaCode"]?>';

			document.write('<img src="/bitrix/tools/captcha.php?captcha_code='+cc+'" width="180" height="40" id="captcha" style="display:none;" onload="imageLoaded()">');
			document.getElementById('captcha_code').value = cc;
			//-->
		</script>
		</div>
		<?
	}
	?>
	<script>
	<!--
	var last_div = '';
	function showComment(key, subject, error, comment, userName, userEmail)
	{
		if(!imgLoaded)
		{
			comment = comment.replace(/\n/g, '\\n');
			comment = comment.replace(/'/g, "\\'");
			comment = comment.replace(/"/g, '\\"');
			setTimeout("showComment('"+key+"', '"+subject+"', '"+error+"', '"+comment+"', '"+userName+"', '"+userEmail+"')", 500);
		}
		else
		{

		<?
		if($arResult["use_captcha"]===true)
		{
			?>
			var im = document.getElementById('captcha');
			document.getElementById('captcha_del').appendChild(im);
			<?
		}
		?>
		var cl = document.getElementById('form_c_del').cloneNode(true);
		var ld = document.getElementById('form_c_del');
		ld.parentNode.removeChild(ld);
		document.getElementById('form_comment_' + key).appendChild(cl);
		document.getElementById('form_c_del').style.display = "block";
		document.form_comment.parentId.value = key;
		document.form_comment.action = document.form_comment.action+"#"+key;

		<?
		if($arResult["use_captcha"]===true)
		{
			?>
			var im = document.getElementById('captcha');
			document.getElementById('div_captcha').appendChild(im);
			im.style.display = "block";
			<?
		}
		?>

		if(subject.length>0 && document.form_comment.subject)
			document.form_comment.subject.value = subject;

		if(error == "Y")
		{
			if(comment.length > 0)
				document.form_comment.comment.value = comment;
			if(userName.length > 0)
				document.form_comment.user_name.value = userName;
			if(userEmail.length > 0)
				document.form_comment.user_email.value = userEmail;
		}
		last_div = key;
		}
		//document.form_comment.comment.focus();
		return false;
	}
	//-->
	</script>
	<?
	function ShowComment($comment, $tabCount=0, $tabSize=2.5, $canModerate=false, $User=Array(), $use_captcha=false, $bCanUserComment=false, $errorComment=false)
	{
		?>
		<a name="<?=$comment["ID"]?>"></a>
		<div class="blog-comment" style="padding-left:<?=$tabCount*$tabSize?>em;">
			<div class="blog-comment-cont">
			<div class="blog-comment-cont-white">
			<div class="blog-comment-info">
				<?
				if(strlen($comment["urlToBlog"])>0)
				{
					?>
					<div class="blog-author"><a class="blog-author-icon" href="<?=$comment["urlToAuthor"]?>"></a><a href="<?=$comment["urlToBlog"]?>"><?=$comment["AuthorName"]?></a></div>
					<?
				}
				elseif(strlen($comment["urlToAuthor"])>0)
				{
					?>
					<div class="blog-author"><a class="blog-author-icon" href="<?=$comment["urlToAuthor"]?>"></a><a href="<?=$comment["urlToAuthor"]?>"><?=$comment["AuthorName"]?></a></div>
					<?
				}
				else
				{
					?>
					<div class="blog-author"><div class="blog-author-icon"></div><?=$comment["AuthorName"]?></div>
					<?
				}
				if(strlen($comment["urlToDelete"])>0)
				{
					?>
					<div class="blog-comment-author-info">
					<?
					if(strlen($comment["AuthorEmail"])>0)
					{
						?>
						(<a href="mailto:<?=$comment["AuthorEmail"]?>"><?=$comment["AuthorEmail"]?></a>)
						<?
					}
					/*
					if($comment["ShowIP"] == "Y")
					{
						?>
						(<?=GetMessage("B_B_MS_FROM")?> <?=$comment["AUTHOR_IP"]?><?if(strlen($comment["AUTHOR_IP1"])>0) echo ', '.$comment["AUTHOR_IP1"];?>)
						<?
					}
					*/
					?>
					</div>
					<?
				}

				?>
				<div class="blog-comment-date"><?=$comment["DateFormated"]?></div>
			</div>
			<div class="blog-comment-content">
				<?=$comment["AVATAR_img"]?>
				<?if(strlen($comment["TitleFormated"])>0)
				{
					?>
					<b><?=$comment["TitleFormated"]?></b><br />
					<?
				}
				?>
				<?=$comment["TextFormated"]?>
				
				<div class="blog-comment-meta">
				<?
				if($bCanUserComment===true)
				{
					?>
					<span class="blog-comment-answer"><a href="javascript:void(0)" onclick="return showComment('<?=$comment["ID"]?>', '<?=$comment["CommentTitle"]?>', '', '', '', '')"><?=GetMessage("B_B_MS_REPLY")?></a></span>
					<span class="blog-vert-separator">|</span>
					<?
				}

				if(IntVal($comment["PARENT_ID"])>0)
				{
					?>
					<span class="blog-comment-parent"><a href="#<?=$comment["PARENT_ID"]?>"><?=GetMessage("B_B_MS_PARENT")?></a></span>
					<span class="blog-vert-separator">|</span>
					<?
				}
				?>
				<span class="blog-comment-link"><a href="#<?=$comment["ID"]?>"><?=GetMessage("B_B_MS_LINK")?></a></span>
				<?
				if(strlen($comment["urlToDelete"])>0)
				{
					?>
					<span class="blog-vert-separator">|</span>
					<span class="blog-comment-delete"><a href="javascript:if(confirm('<?=GetMessage("BPC_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$comment["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BPC_MES_DELETE")?></a></span>
					<?
				}
				?>
				</div>
			</div>
			</div>
			</div>
				<div class="blog-clear-float"></div>

			<?
			if(strlen($errorComment)<=0 && $_POST["parentId"]==$comment["ID"] && strlen($_POST["preview"]) > 0)
			{							
				?><div style="border:1px solid red"><?
					$commentPreview = Array(
							"ID" => "preview",
							"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
							"TextFormated" => $_POST["commentFormated"],
							"AuthorName" => $User["NAME"],
							"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
						);
					ShowComment($commentPreview, ($level+1), 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment);
				?></div><?
			}
			if(strlen($errorComment)>0 && IntVal($_POST["parentId"])==$comment["ID"] && $bCanUserComment===true)
			{							
				?>
				<div class="blog-errors">
					<div class="blog-error-text">
						<?=$errorComment?>
					</div>
				</div>
				<?
			}
			?>
			<div id="form_comment_<?=$comment['ID']?>"></div>
			<?
			if((strlen($errorComment)>0 || strlen($_POST["preview"]) > 0) && IntVal($_POST["parentId"])==$comment["ID"] && $bCanUserComment===true)
			{
				$form1 = str_replace("'","\'",$_POST["comment"]);
				$form1 = str_replace("\r"," ",$form1);
				$form1 = str_replace("\n","\\n'+\n'",$form1);

				$subj = str_replace("'","\'", $_POST["subject"]);
				$user_name = str_replace("'","\'", $_POST["user_name"]);
				$user_email = str_replace("'","\'", $_POST["user_email"]);
				?>
				<script>
				<!--
				var cmt = '<?=$form1?>';
				showComment('<?=$comment["ID"]?>', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
				//-->
				</script>
				<?
			}
			?>
		</div>
		<?
	}

	function RecursiveComments($sArray, $key, $level=0, $first=false, $canModerate=false, $User, $use_captcha, $bCanUserComment, $errorComment)
	{
		if(!empty($sArray[$key]))
		{
			foreach($sArray[$key] as $comment)
			{
				ShowComment($comment, $level, 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment);
				if(!empty($sArray[$comment["ID"]]))
				{
					foreach($sArray[$comment["ID"]] as $key1)
					{
						ShowComment($key1, ($level+1), 2.5, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment);

						if(!empty($sArray[$key1["ID"]]))
						{
							RecursiveComments($sArray, $key1["ID"], ($level+2), false, $canModerate, $User, $use_captcha, $bCanUserComment, $errorComment);
						}
					}
				}
				if($first)
					$level=0;
			}
		}
	}
	?>
	<?
	if($arResult["CanUserComment"])
	{
		$postTitle = "";
		if($arParams["NOT_USE_COMMENT_TITLE"] != "Y")
			$postTitle = "RE: ".str_replace(array("\\", "\"", "'"), array("\\\\", "\\"."\"", "\\'"), $arResult["Post"]["TITLE"]);
		
		?>
		<div class="blog-add-comment"><a name="comment"></a><a href="javascript:void(0)" onclick="return showComment('0', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div>
		<a name="0"></a>
		<?
		if(strlen($arResult["COMMENT_ERROR"]) <= 0 && strlen($_POST["parentId"]) < 2 && IntVal($_POST["parentId"])==0 && strlen($_POST["preview"]) > 0)
		{							
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
						"TextFormated" => $_POST["commentFormated"],
						"AuthorName" => $arResult["User"]["NAME"],
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 2.5, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"]);
			?></div><?
		}
		



		if(strlen($arResult["COMMENT_ERROR"])>0 && strlen($_POST["parentId"]) < 2 && IntVal($_POST["parentId"])==0)
		{
			?>
			<div class="blog-errors">
				<div class="blog-error-text"><?=$arResult["COMMENT_ERROR"]?></div>
			</div>
			<?
		}
		?>
		<div id=form_comment_0></div>
		<?
		if((strlen($arResult["COMMENT_ERROR"])>0 || strlen($_POST["preview"]) > 0) && IntVal($_POST["parentId"]) == 0 && strlen($_POST["parentId"]) < 2)
		{
			$form1 = str_replace("'","\'",$_POST["comment"]);
			$form1 = str_replace("\r"," ",$form1);
			$form1 = str_replace("\n","\\n'+\n'",$form1);

			$subj = str_replace("'","\'", $_POST["subject"]);
			$user_name = str_replace("'","\'", $_POST["user_name"]);
			$user_email = str_replace("'","\'", $_POST["user_email"]);
			?>
			<script>
			<!--
			var cmt = '<?=$form1?>';
			showComment('0', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
			//-->
			</script>
			<?
		}
		
		if($arResult["NEED_NAV"] == "Y")
		{
			?>
			<div class="blog-comment-nav">
				<?=GetMessage("BPC_PAGE")?>&nbsp;<?
				foreach($arResult["PAGES"] as $v)
				{
					echo $v;
				}
				
				
			?>
			</div>
			<?
		}
	}

	RecursiveComments($arResult["CommentsResult"], $arResult["firstLevel"], 0, true, $arResult["canModerate"], $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"]);

	if($arResult["NEED_NAV"] == "Y")
	{
		?>
		<div class="blog-comment-nav">
			<?=GetMessage("BPC_PAGE")?>&nbsp;<?
			foreach($arResult["PAGES"] as $v)
			{
				echo $v;
			}
			
			
		?>
		</div>
		<?
	}

	if($arResult["CanUserComment"] && count($arResult["Comments"])>2)
	{
		?>
		<div class="blog-add-comment"><a href="#comment" onclick="return showComment('00', '<?=$postTitle?>')"><b><?=GetMessage("B_B_MS_ADD_COMMENT")?></b></a><br /></div><a name="00"></a>
		<?
		if(strlen($arResult["COMMENT_ERROR"]) <= 0 && $_POST["parentId"] == "00" && strlen($_POST["parentId"]) > 1 && strlen($_POST["preview"]) > 0)
		{							
			?><div style="border:1px solid red"><?
				$commentPreview = Array(
						"ID" => "preview",
						"TitleFormated" => htmlspecialcharsEx($_POST["subject"]),
						"TextFormated" => $_POST["commentFormated"],
						"AuthorName" => $arResult["User"]["NAME"],
						"DATE_CREATE" => GetMessage("B_B_MS_PREVIEW_TITLE"),
					);
				ShowComment($commentPreview, 0, 2.5, false, $arResult["User"], $arResult["use_captcha"], $arResult["CanUserComment"], $arResult["COMMENT_ERROR"]);
			?></div><?
		}
		
		if(strlen($arResult["COMMENT_ERROR"])>0 && $_POST["parentId"] == "00" && strlen($_POST["parentId"]) > 1)
		{
			?>
			<div class="blog-errors">
				<div class="blog-error-text">
					<?=$arResult["COMMENT_ERROR"]?>
				</div>
			</div>
			<?
		}
		?>

		<div id=form_comment_00></div><br />
		<?
		if((strlen($arResult["COMMENT_ERROR"])>0 || strlen($_POST["preview"]) > 0) && $_POST["parentId"] == "00" && strlen($_POST["parentId"]) > 1)
		{
			$form1 = str_replace("'","\'",$_POST["comment"]);
			$form1 = str_replace("\r"," ",$form1);
			$form1 = str_replace("\n","\\n'+\n'",$form1);

			$subj = str_replace("'","\'", $_POST["subject"]);
			$user_name = str_replace("'","\'", $_POST["user_name"]);
			$user_email = str_replace("'","\'", $_POST["user_email"]);
			?>
			<script>
			<!--
			var cmt = '<?=$form1?>';
			showComment('00', '<?=$subj?>', 'Y', cmt, '<?=$user_name?>', '<?=$user_email?>');
			//-->
			</script>
			<?
		}
	}
}
?>
</div>