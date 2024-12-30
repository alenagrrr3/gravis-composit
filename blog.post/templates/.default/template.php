<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div class="blog-post-current">
<?
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
elseif(strlen($arResult["NOTE_MESSAGE"])>0)
{
	?>
	<div class="blog-textinfo">
		<div class="blog-textinfo-text">
			<?=$arResult["NOTE_MESSAGE"]?>
		</div>
	</div>
	<?
}
else
{
	if(!empty($arResult["Post"])>0)
	{
		?>
		<div class="blog-post">
		<h2 class="blog-post-title"><span><?=$arResult["Post"]["TITLE"]?></span></h2>
		<div class="blog-post-info-back">
		<div class="blog-post-info">
			<div class="blog-author"><a class="blog-author-icon" href="<?=$arResult["urlToAuthor"]?>"></a><a href="<?=$arResult["urlToBlog"]?>"><?=$arResult["AuthorName"]?></a></div>
			<div class="blog-post-date"><?=$arResult["Post"]["DATE_PUBLISH_FORMATED"]?></div>
		</div>
		</div>
		<div class="blog-post-content">
			<div class="blog-post-avatar"><?=$arResult["BlogUser"]["AVATAR_img"]?></div>
			<?=$arResult["Post"]["textFormated"]?>
			<br clear="all" />
			<?if($arResult["POST_PROPERTIES"]["SHOW"] == "Y"):?>
				<?foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
				<?if(strlen($arPostField["VALUE"])>0):?>
					<p><b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;
							<?$APPLICATION->IncludeComponent(
								"bitrix:system.field.view", 
								$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
					</p>
				<?endif;?>
				<?endforeach;?>
				</table>
			<?endif;?>
		</div>
			<div class="blog-post-meta">
				<div class="blog-post-meta-util">
					<span class="blog-post-comments-link"><a href=""><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?></a> <a href=""><?=IntVal($arResult["Post"]["NUM_COMMENTS"])?></a></span>
					<span class="blog-post-views-link"><a href=""><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?></a> <a href=""><?=IntVal($arResult["Post"]["VIEWS"])?></a></span>
					
					<?if(strLen($arResult["urlToEdit"])>0):?>
						<span class="blog-post-edit-link"><a href="<?=$arResult["urlToEdit"]?>"><?=GetMessage("BLOG_BLOG_BLOG_EDIT")?></a></span>
					<?endif;?>
					<?if(strLen($arResult["urlToDelete"])>0):?>
						<span class="blog-post-delete-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$arResult["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BLOG_BLOG_BLOG_DELETE")?></a></span>
					<?endif;?>

				</div>

				<?if(!empty($arResult["Category"]))
				{
					?>
					<div class="blog-post-tag">
						<?=GetMessage("BLOG_BLOG_BLOG_CATEGORY")?>
						<?
						$i=0;
						foreach($arResult["Category"] as $v)
						{
							if($i!=0)
								echo ",";
							?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
							$i++;
						}
						?>
					</div>
					<?
				}
				?>
			</div>
		</div>
		<?
	}
	else
		echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
}
?>
</div>