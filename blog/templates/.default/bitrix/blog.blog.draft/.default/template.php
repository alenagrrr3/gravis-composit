<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!$this->__component->__parent || empty($this->__component->__parent->__name) || $this->__component->__parent->__name != "bitrix:blog"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/blog/templates/.default/themes/blue/style.css');
endif;
?>
<div id="blog-draft-content">
<?
if(!empty($arResult["OK_MESSAGE"]))
{
	?>
	<div class="blog-notes">
		<div class="blog-note-text">
			<ul>
			<?
			foreach($arResult["OK_MESSAGE"] as $v)
			{
				?>
				<li><?=$v?></li>
				<?
			}
			?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["MESSAGE"]))
{
	?>
	<div class="blog-textinfo">
		<div class="blog-textinfo-text">
			<ul>
			<?
			foreach($arResult["MESSAGE"] as $v)
			{
				?>
				<li><?=$v?></li>
				<?
			}
			?>
			</ul>
		</div>
	</div>
	<?
}
if(!empty($arResult["ERROR_MESSAGE"]))
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul>
			<?
			foreach($arResult["ERROR_MESSAGE"] as $v)
			{
				?>
				<li><?=$v?></li>
				<?
			}
			?>
			</ul>
		</div>
	</div>
	<?
}
if(strlen($arResult["FATAL_ERROR"])>0)
{
	?>
	<div class="blog-errors">
		<div class="blog-error-text">
			<ul>
				<?=$arResult["FATAL_ERROR"]?>
			</ul>
		</div>
	</div>
	<?
}
elseif(count($arResult["POST"])>0)
{
	foreach($arResult["POST"] as $CurPost)
	{
		?>
			<div class="blog-post">
				<h2 class="blog-post-title"><a href="<?=$CurPost["urlToEdit"]?>"><?=$CurPost["TITLE"]?></a></h2>
				<div class="blog-post-info">
					<div class="blog-post-date"><?=$CurPost["DATE_PUBLISH_FORMATED"]?></div>
				</div>
				<div class="blog-post-content">
					<?=$CurPost["TEXT_FORMATED"]?>
					<?if($CurPost["POST_PROPERTIES"]["SHOW"] == "Y"):?>
						<p>
						<?foreach ($CurPost["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField):?>
						<?if(strlen($arPostField["VALUE"])>0):?>
						<b><?=$arPostField["EDIT_FORM_LABEL"]?>:</b>&nbsp;<?$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));?>
						<?endif;?>
						<?endforeach;?>
						</p>
					<?endif;?>
				</div>
				
				<div class="blog-post-meta">
					<div class="blog-post-meta-util">
						<span class="blog-post-comments-link"><a href="<?=$CurPost["urlToEdit"]?>#comment"><?=GetMessage("BLOG_BLOG_BLOG_COMMENTS")?></a> <a href="<?=$CurPost["urlToEdit"]?>"><?=IntVal($CurPost["NUM_COMMENTS"]);?></a></span>
						<span class="blog-post-views-link"><a href="<?=$CurPost["urlToEdit"]?>"><?=GetMessage("BLOG_BLOG_BLOG_VIEWS")?></a> <a href="<?=$CurPost["urlToEdit"]?>"><?=IntVal($CurPost["VIEWS"]);?></a></span>
						<?if(strLen($CurPost["urlToDelete"])>0):?>
							<span class="blog-post-delete-link"><a href="javascript:if(confirm('<?=GetMessage("BLOG_MES_DELETE_POST_CONFIRM")?>')) window.location='<?=$CurPost["urlToDelete"]."&".bitrix_sessid_get()?>'"><?=GetMessage("BLOG_MES_DELETE")?></a></span>
						<?endif;?>
					</div>

					<div class="blog-post-tag">
						<?
						if(!empty($CurPost["CATEGORY"]))
						{
							echo GetMessage("BLOG_BLOG_BLOG_CATEGORY");
							$i=0;
							foreach($CurPost["CATEGORY"] as $v)
							{
								if($i!=0)
									echo ",";
								?> <a href="<?=$v["urlToCategory"]?>"><?=$v["NAME"]?></a><?
								$i++;
							}
						}
						?>
	
					</div>
				</div>
			</div>
		<?
	}
}
else
	echo GetMessage("B_B_DRAFT_NO_MES");
?>	
</div>