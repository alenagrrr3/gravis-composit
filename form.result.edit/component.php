<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if (CModule::IncludeModule("form"))
{
	$arDefaultComponentParameters = array(
		"RESULT_ID" => $_REQUEST["RESULT_ID"],
		"EDIT_ADDITIONAL" => "N",
		"EDIT_STATUS" => "N",
		"IGNORE_CUSTOM_TEMPLATE" => "N",
		"USE_EXTENDED_ERRORS" => "N",
	);

	foreach ($arDefaultComponentParameters as $key => $value) if (!is_set($arParams, $key)) $arParams[$key] = $value;

	$arDefaultUrl = array(
		'LIST' => $arParams["SEF_MODE"] == "Y" ? "list/" : "result_list.php",
		'VIEW' => $arParams["SEF_MODE"] == "Y" ? "view/#RESULT_ID#/" : "result_view.php",
	);
	
	foreach ($arDefaultUrl as $action => $url)
	{
		if (strlen($arParams[$action.'_URL']) <= 0)
		{
			if (!is_set($arParams, 'SHOW_'.$action.'_PAGE') || $arParams['SHOW_'.$action.'_PAGE'] == 'Y')
				$arParams[$action.'_URL'] = $url;
		}
	}
	
	if ($arParams["SEF_MODE"] == "Y" && empty($arParams["RESULT_ID"]))
	{
		$arDefaultUrlTemplates404 = array(
			"edit" => "#RESULT_ID#/",
		);

		$arDefaultVariableAliases404 = array(
		);

		$arDefaultVariableAliases = array();

		$arComponentVariables = array("RESULT_ID");

		$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);		
		CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);
		
		$arParams["RESULT_ID"] = intval($arVariables["RESULT_ID"]);
	}
	
	$arResult["FORM_SIMPLE"] = COption::GetOptionString("form", "SIMPLE", "N") == "N" ? "N" : "Y";
	$arResult["bAdmin"] = defined("ADMIN_SECTION") && ADMIN_SECTION===true ? "Y" : "N";

	// if form taken from admin interface - check rights to form module
	if ($arResult["bAdmin"] == "Y")
	{
		$FORM_RIGHT = $APPLICATION->GetGroupRight("form");
		if($FORM_RIGHT<="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
	}
	
	/****************************************************************************/
		
	// if there's result ID try to get form ID
	if (intval($arParams["RESULT_ID"] > 0))
	{
		$DBRes = CFormResult::GetByID($arParams["RESULT_ID"]);
		
		if ($arResultData = $DBRes->Fetch())
		{
			$arParams["WEB_FORM_ID"] = intval($arResultData["FORM_ID"]);
		}
	}

	if (intval($arParams["RESULT_ID"]) <= 0 || intval($arParams["WEB_FORM_ID"]) <= 0) 
	{
		$arResult["ERROR"] = "FORM_RECORD_NOT_FOUND";
	}

	
	if (strlen($arResult["ERROR"]) <= 0)
	{
		// check WEB_FORM_ID and get web form data
		$arParams["WEB_FORM_ID"] = CForm::GetDataByID($arParams["WEB_FORM_ID"], $arResult["arForm"], $arResult["arQuestions"], $arResult["arAnswers"], $arResult["arDropDown"], $arResult["arMultiSelect"], $arResult["bAdmin"] == 'Y' || $arParams["SHOW_ADDITIONAL"] == "Y" || $arParams["EDIT_ADDITIONAL"] == "Y" ? "ALL" : "N");
		
		$arResult["WEB_FORM_NAME"] = $arResult["arForm"]["SID"];
	
		// if wrong WEB_FORM_ID return error;
		if ($arParams["WEB_FORM_ID"] > 0) 
		{
			//  insert chain item
			if (strlen($arParams["CHAIN_ITEM_TEXT"]) > 0)
			{
				$APPLICATION->AddChainItem($arParams["CHAIN_ITEM_TEXT"], $arParams["CHAIN_ITEM_LINK"]);
			}
			
			// check web form rights;
			$arResult["F_RIGHT"] = intval(CForm::GetPermission($arParams["WEB_FORM_ID"]));
			
			// in no form access - return error
			if ($arResult["F_RIGHT"] >= 15)
			{
				//if (!empty($_REQUEST["strFormNote"])) $arResult["FORM_NOTE"] = $_REQUEST["strFormNote"];				
				if (!empty($_REQUEST["formresult"])) 
				{
					$formResult = strtoupper($_REQUEST['formresult']);
					switch ($formResult)
					{
						case 'ADDOK':
							$arResult['FORM_NOTE'] = str_replace("#RESULT_ID#", $arParams["RESULT_ID"], GetMessage('FORM_NOTE_ADDOK'));
						break;
						default:
							$arResult['FORM_NOTE'] = str_replace("#RESULT_ID#", $arParams["RESULT_ID"], GetMessage('FORM_NOTE_EDITOK'));
					}
				}

				if ($arResult["F_RIGHT"]>=20 || ($arResult["F_RIGHT"]>=15 && $USER->GetID()==$arResultData["USER_ID"])) 
				{
					$arResult["arrRESULT_PERMISSION"] = CFormResult::GetPermissions($arParams["RESULT_ID"], $v);
					
					// check result rights
					if (!in_array("EDIT", $arResult["arrRESULT_PERMISSION"])) 
					{
						$arResult["ERROR"] = "FORM_RESULT_ACCESS_DENIED";
					}
					else
					{
						if (!$arResultData)
						{
							$z = CFormResult::GetByID($arParams["RESULT_ID"]);
							$arResult["arResultData"] = $z->Fetch();
						}
						else
						{
							$arResult["arResultData"] = $arResultData;
						}
						
						if ($arResult["arResultData"])
						{
							$arResult["arrVALUES"] = CFormResult::GetDataByIDForHTML($arParams["RESULT_ID"], $arParams["EDIT_ADDITIONAL"]);
						}
						else
						{
							$arResult["ERROR"] = "FORM_RECORD_NOT_FOUND";
						}
					}
				}
				else
				{
					$arResult["ERROR"] = "FORM_ACCESS_DENIED";
				}
					
				$arResult["arForm"]["USE_CAPTCHA"] = "N";
			}
		}
		else
		{
			$arResult["ERROR"] = "FORM_NOT_FOUND";
		}
	}

	// if there's no error
	if (strlen($arResult["ERROR"]) <= 0)
	{
		// ************************************************************* //
		//                                             get/post processing                                             //
		// ************************************************************* //
	
		if (strlen($_REQUEST["web_form_submit"])>0 || strlen($_REQUEST["web_form_apply"])>0)
		{
			$arResult["arrVALUES"] = $_REQUEST;
			
			// check errors
			$arResult["FORM_ERRORS"] = CForm::Check($arParams["WEB_FORM_ID"], $arResult["arrVALUES"], $arParams["RESULT_ID"], "Y", $arParams['USE_EXTENDED_ERRORS']);
			
			if (
				$arParams['USE_EXTENDED_ERRORS'] == 'Y' && (!is_array($arResult["FORM_ERRORS"]) || count($arResult["FORM_ERRORS"]) <= 0)
				||
				$arParams['USE_EXTENDED_ERRORS'] != 'Y' && strlen($arResult["FORM_ERRORS"]) <= 0
			)
			{
				// check session id
				if (check_bitrix_sessid())
				{
					$return = false;
					
					if (CFormResult::Update($arParams["RESULT_ID"], $arResult["arrVALUES"], $arParams["EDIT_ADDITIONAL"]))
					{
						$arResult["FORM_RESULT"] = 'editok';
						
						if (strlen($_REQUEST["web_form_submit"])>0 && !(defined("ADMIN_SECTION") && ADMIN_SECTION===true)) 
						{
							if ($arParams["SEF_MODE"] == "Y")
							{
								//LocalRedirect($arParams["LIST_URL"]."?strFormNote=".urlencode($arResult["FORM_NOTE"]));
								LocalRedirect(
									str_replace(
										array('#WEB_FORM_ID#', '#RESULT_ID#'),
										array($arParams['WEB_FORM_ID'], $arParams["RESULT_ID"]),
										$arParams["LIST_URL"]
									)."?formresult=".urlencode($arResult["FORM_RESULT"])
								);
							}
							else
							{
								//LocalRedirect($arParams["LIST_URL"].(strpos($arParams["LIST_URL"], "?") === false ? "?" : "&")."WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&strFormNote=".urlencode($arResult["FORM_NOTE"]));
								LocalRedirect(
									$arParams["LIST_URL"]
									.(strpos($arParams["LIST_URL"], "?") === false ? "?" : "&")
									."WEB_FORM_ID=".$arParams["WEB_FORM_ID"]
									."&RESULT_ID=".$arParams["RESULT_ID"]
									."&formresult=".urlencode($arResult["FORM_RESULT"])
								);
							}
								
							die();
						}
						
						if (strlen($_REQUEST["web_form_apply"])>0 && !(defined("ADMIN_SECTION") && ADMIN_SECTION===true)) 
						{
							// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
							//LocalRedirect($arParams["EDIT_URL"].(strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")."strFormNote=".urlencode($arResult["FORM_NOTE"]));
							if ($arParams["SEF_MODE"] == "Y")
							{
								//LocalRedirect(str_replace("#RESULT_ID#", $RESULT_ID, $arParams["EDIT_URL"])."?strFormNote=".urlencode($arResult["FORM_NOTE"]));
								/*LocalRedirect(
									str_replace(
										array('#WEB_FORM_ID#', '#RESULT_ID#'),
										array($arParams['WEB_FORM_ID'], $arParams["RESULT_ID"]),
										$arParams["EDIT_URL"]
									)
									.(strpos($arParams["EDIT_URL"], "?") === false ? "?" : "&")
									."formresult=".urlencode($arResult["FORM_RESULT"])
								);
								*/
								LocalRedirect(
									$APPLICATION->GetCurPageParam(
										"formresult=".urlencode($arResult["FORM_RESULT"]),
										array('formresult', 'SEF_APPLICATION_CUR_PAGE_URL')
									)
								);
							}
							else
							{
								LocalRedirect(
									$APPLICATION->GetCurPageParam(
										"WEB_FORM_ID=".$arParams["WEB_FORM_ID"]
										."&RESULT_ID=".$arParams["RESULT_ID"]
										."&formresult=".urlencode($arResult["FORM_RESULT"]),
										array('WEB_FORM_ID', 'RESULT_ID', 'formresult')
									)
								);
							}
							die();
						}
						
						if (defined("ADMIN_SECTION") && ADMIN_SECTION === true)
						{
							if (strlen($_REQUEST["web_form_submit"])>0)
							{
								LocalRedirect(BX_ROOT."/admin/form_result_list.php?lang=".LANG."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&formresult=".urlencode($arResult["FORM_RESULT"]));
							}
							elseif (strlen($_REQUEST["web_form_apply"])>0)
							{
								LocalRedirect(BX_ROOT."/admin/form_result_edit.php?lang=".LANG."&WEB_FORM_ID=".$arParams["WEB_FORM_ID"]."&RESULT_ID=".$arParams["RESULT_ID"]."&form_result=".urlencode($arResult["FORM_RESULT"]));
							}
							die();
						}
					}
					else
						$arResult['FORM_ERRORS'] = $GLOBALS['strError'];
				}
			}
		}
		
		/*
		if (is_array($arResult["FORM_ERRORS"])) 
		{
			$arResult["FORM_ERRORS"] = implode("<br />", $arResult["FORM_ERRORS"]);
		}
		*/
		
		$arResult["isFormErrors"] = 
			(
				is_array($arResult["FORM_ERRORS"]) && count($arResult["FORM_ERRORS"]) > 0
				||
				!is_array($arResult['FORM_ERRORS']) && strlen($arResult["FORM_ERRORS"]) > 0
			)
			? "Y" : "N";

		if ($arResult['isFormErrors'] == 'Y')
		{
			unset($arResult['FORM_RESULT']);
			unset($arResult['FORM_NOTE']);
		}
		
		// ************************************************************* //
		//                                             output                                                                    //
		// ************************************************************* //

		if ($arParams["IGNORE_CUSTOM_TEMPLATE"] == "N" && $arResult["arForm"]["USE_DEFAULT_TEMPLATE"] == "N" && strlen($arResult["arForm"]["FORM_TEMPLATE"]) > 0)
		{
			$FORM = new CFormOutput();
			// initialize template
			$FORM->InitializeTemplate($arParams, $arResult);
		
			// get template
			if ($strReturn = $FORM->IncludeFormCustomTemplate())
			{
				// add icons
				$back_url = $_SERVER['REQUEST_URI'];
				
				$editor = "/bitrix/admin/fileman_file_edit.php?full_src=Y&site=".SITE_ID."&";
				$href = "javascript:window.location='".$editor."path=".urlencode($path)."&lang=".LANGUAGE_ID."&back_url=".urlencode($back_url)."'";
				
				if ($arParams['USE_EXTENDED_ERRORS'] == 'Y')
				$APPLICATION->SetAdditionalCSS($this->GetPath()."/error.css");
				
				if ($APPLICATION->GetShowIncludeAreas() && $USER->IsAdmin())
				{
					$APPLICATION->SetAdditionalCSS($this->GetPath()."/icons.css");
					// define additional icons for Site Edit mode
					$arIcons = array(
						// form template edit icon
						array(
							'URL' => "javascript:".$APPLICATION->GetPopupLink(
								array(
									'URL' => "/bitrix/admin/form_edit.php?bxpublic=Y&from_module=form&lang=".LANGUAGE_ID."&ID=".$FORM->WEB_FORM_ID."&tabControl_active_tab=edit5&back_url=".urlencode($_SERVER["REQUEST_URI"]),
									'PARAMS' => array(
										'width' => 700,
										'height' => 500,
										'resize' => false,
									)
								)
							),
							'ICON' => 'form-edit-tpl',
							'TITLE' => GetMessage("FORM_PUBLIC_ICON_EDIT_TPL")
						),
						
						// form params edit icon
						/*array(
							'URL' => "/bitrix/admin/form_edit.php?lang=".LANGUAGE_ID."&ID=".$FORM->WEB_FORM_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]),
							'ICON' => 'form-edit',
							'TITLE' => GetMessage("FORM_PUBLIC_ICON_EDIT")
						),*/

						array(
							'URL' => "javascript:".$APPLICATION->GetPopupLink(
								array(
									'URL' => "/bitrix/admin/form_edit.php?bxpublic=Y&from_module=form&lang=".LANGUAGE_ID."&ID=".$FORM->WEB_FORM_ID."&back_url=".urlencode($_SERVER["REQUEST_URI"]),
									'PARAMS' => array(
										'width' => 700,
										'height' => 500,
										'resize' => false,
									)
								)
							),
							'ICON' => 'form-edit',
							'TITLE' => GetMessage("FORM_PUBLIC_ICON_EDIT"),
							'DEFAULT' => ($APPLICATION->GetPublicShowMode() != 'configure' ? true : false),
							"MODE" => array("edit", "configure"),
						),
					);
					
					$this->AddIncludeAreaIcons($arIcons);
				}
				
				// output template
				echo $strReturn;
				
				return;
			}
		}

		
		// include CSS with additional icons for Site Edit mode
		if ($APPLICATION->GetShowIncludeAreas() && $USER->IsAdmin())
		{
			$APPLICATION->SetAdditionalCSS($this->GetPath()."/icons.css");
					// define additional icons for Site Edit mode
					$arIcons = array(
						array(
							'URL' => "javascript:".$APPLICATION->GetPopupLink(
								array(
									'URL' => "/bitrix/admin/form_edit.php?bxpublic=Y&from_module=form&lang=".LANGUAGE_ID."&ID=".$arParams['WEB_FORM_ID']."&back_url=".urlencode($_SERVER["REQUEST_URI"]),
									'PARAMS' => array(
										'width' => 700,
										'height' => 500,
										'resize' => false,
									)
								)
							),
							'ICON' => 'form-edit',
							'TITLE' => GetMessage("FORM_PUBLIC_ICON_EDIT"),
							'DEFAULT' => ($APPLICATION->GetPublicShowMode() != 'configure' ? true : false),
							"MODE" => array("edit", "configure"),
						),
					);
					
					$this->AddIncludeAreaIcons($arIcons);
		}
			
		if (intval($arResult["arResultData"]["USER_ID"])>0)
		{
			$rsUser = CUser::GetByID($arResult["arResultData"]["USER_ID"]);
			$arUser = $rsUser->Fetch();
			
			$arResult["RESULT_USER_ID"] = $arResult["arResultData"]["USER_ID"];
			$arResult["RESULT_USER_LOGIN"] = $arUser["LOGIN"];
			$arResult["RESULT_USER_EMAIL"] = $arUser["USER_EMAIL"];
			$arResult["RESULT_USER_FIRST_NAME"] = $arUser["NAME"];
			$arResult["RESULT_USER_LAST_NAME"] = $arUser["LAST_NAME"];
		}
		
		$arResult["isResultStatusChangeAccess"] = in_array("EDIT", $arResult["arrRESULT_PERMISSION"]) ? "Y" : "N";

		$arResult["RESULT_STATUS_FORM"] = $arResult["isResultStatusChangeAccess"] == "Y" ? SelectBox("status_".$arResult["WEB_FORM_NAME"], CFormStatus::GetDropdown($arParams["WEB_FORM_ID"], array("MOVE"), $arResult["RESULT_USER_ID"]), " ", "", "") : "";

		// define variables to assign
		$arResult = array_merge(
			$arResult,
			array(
				"RESULT_ID" => $arParams["RESULT_ID"],
				"WEB_FORM_ID" => $arParams["WEB_FORM_ID"],

				"RESULT_STATUS" => "<span class='".$arResult["arResultData"]["STATUS_CSS"]."'>".$arResult["arResultData"]["STATUS_TITLE"]."</span>",
				
				"RESULT_USER_AUTH" => $arResult["arResultData"]["USER_AUTH"] == "Y" ? "Y" : "N",
				
				"RESULT_DATE_CREATE" => $arResult["arResultData"]["DATE_CREATE"],
				"RESULT_TIMESTAMP_X" => $arResult["arResultData"]["TIMESTAMP_X"],
				
				"RESULT_STAT_GUEST_ID" => $arResult["arResultData"]["STAT_GUEST_ID"],
				"RESULT_STAT_SESSION_ID" => $arResult["arResultData"]["STAT_SESSION_ID"],
				
				"isFormNote"			=> strlen($arResult["FORM_NOTE"]) ? "Y" : "N", // flag "is there a form note"
				"isAccessFormParams"	=> $arResult["F_RIGHT"] >= 25 ? "Y" : "N", // flag "does current user have access to form params"
				"isStatisticIncluded"	=> CModule::IncludeModule('statistic') ? "Y" : "N", // flag "is statistic module included"
				
				"FORM_HEADER" => sprintf( // form header (<form> tag and hidden inputs)
					"<form name=\"%s\" action=\"%s\" method=\"%s\" enctype=\"multipart/form-data\">", 
					$arResult["arForm"]["SID"], POST_FORM_ACTION_URI, "POST"
				),
				
				"FORM_TITLE"			=> trim(htmlspecialchars($arResult["arForm"]["NAME"])), // form title
				
				"FORM_DESCRIPTION" => // form description
					$arResult["arForm"]["DESCRIPTION_TYPE"] == "html" ? 
					trim($arResult["arForm"]["DESCRIPTION"]) : 
					nl2br(htmlspecialchars(trim($arResult["arForm"]["DESCRIPTION"]))),
				
				"isFormTitle"			=> strlen($arResult["arForm"]["NAME"]) > 0 ? "Y" : "N", // flag "does form have title"
				"isFormDescription"		=> strlen($arResult["arForm"]["DESCRIPTION"]) > 0 ? "Y" : "N", // flag "does form have description"
				"isFormImage"			=> intval($arResult["arForm"]["IMAGE_ID"]) > 0 ? "Y" : "N", // flag "does form have image"
				"isUseCaptcha"			=> $arResult["arForm"]["USE_CAPTCHA"] == "Y", // flag "does form use captcha"
				"DATE_FORMAT"			=> CLang::GetDateFormat("SHORT"), // current site date format
				"REQUIRED_SIGN"			=> CForm::ShowRequired("Y"), // "required" sign
				"FORM_FOOTER"			=> "</form>", // form footer (close <form> tag)
			)
		);
			
		// get template vars for form image
		if ($arResult["isFormImage"] == "Y")
		{
			$arResult["FORM_IMAGE"]["ID"] = $arResult["arForm"]["IMAGE_ID"];
			// assign form image url
			$arResult["FORM_IMAGE"]["URL"] = CFile::GetPath($arResult["arForm"]["IMAGE_ID"]);
			
			// check image file existance and assign image data
			if (
				file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["FORM_IMAGE"]["URL"]) 
				&& 
				list(
					$arResult["FORM_IMAGE"]["WIDTH"], 
					$arResult["FORM_IMAGE"]["HEIGHT"], 
					$arResult["FORM_IMAGE"]["TYPE"], 
					$arResult["FORM_IMAGE"]["ATTR"]
				) = @getimagesize($_SERVER["DOCUMENT_ROOT"].$arResult["FORM_IMAGE"]["URL"])
			)
			{
				$arResult["FORM_IMAGE"]["HTML_CODE"] = CFile::ShowImage($arResult["arForm"]["IMAGE_ID"]);
			}
		}
		
		$arResult["QUESTIONS"] = array();
		reset($arResult["arQuestions"]);
		
		// assign questions data
		foreach ($arResult["arQuestions"] as $key => $arQuestion)
		{
			$FIELD_SID = $arQuestion["SID"];
			$arResult["QUESTIONS"][$FIELD_SID] = array(
				"CAPTION" => // field caption
					$arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ? 
					$arResult["arQuestions"][$FIELD_SID]["TITLE"] : 
					nl2br(htmlspecialchars($arResult["arQuestions"][$FIELD_SID]["TITLE"])), 
					
				"IS_HTML_CAPTION"			=> $arResult["arQuestions"][$FIELD_SID]["TITLE_TYPE"] == "html" ? "Y" : "N",
				"REQUIRED"					=> $arResult["arQuestions"][$FIELD_SID]["REQUIRED"] == "Y" ? "Y" : "N", 
				"IS_INPUT_CAPTION_IMAGE"	=> intval($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]) > 0 ? "Y" : "N",
			);
			
			// ******************************** customize answers ***************************** //
			
			$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"] = array();
			
			if (is_array($arResult["arAnswers"][$FIELD_SID]))
			{
				$res = "";
			
				reset($arResult["arAnswers"][$FIELD_SID]);
				if (is_array($arResult["arDropDown"][$FIELD_SID])) reset($arResult["arDropDown"][$FIELD_SID]);
				if (is_array($arResult["arMutiselect"][$FIELD_SID])) reset($arResult["arMutiselect"][$FIELD_SID]);

				$show_dropdown = "N";
				$show_multiselect = "N";

				foreach ($arResult["arAnswers"][$FIELD_SID] as $key => $arAnswer)
				{
					//echo "<pre>".$FIELD_SID." ".$key." "; print_r($arAnswer); echo "</pre>";
					if ($arAnswer["FIELD_TYPE"]=="dropdown" && $show_dropdown=="Y") continue;
					if ($arAnswer["FIELD_TYPE"]=="multiselect" && $show_multiselect=="Y") continue;
					
					$res = "";
					
					switch ($arAnswer["FIELD_TYPE"]) 
					{
						case "radio":
							if (strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"".$ans_id."\"";
							}
							else
							{
								$ans_id = "";
							}
						
							$value = CForm::GetRadioValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);
							
							if (strlen($arResult["FORM_ERRORS"]) > 0)
							{
								if (
									strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false 
									|| 
									strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
									{
										$arAnswer["FIELD_PARAM"] = eregi_replace("checked|selected", "", $arAnswer["FIELD_PARAM"]);
									}
							}							
							
							$input = CForm::GetRadioField(
								$FIELD_SID,
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);
							
							
							if (strlen($ans_id) > 0)
							{
								$res .= $input;
								$res .= "<label for=\"".$ans_id."\">".$arAnswer["MESSAGE"]."</label>";
							}
							else
							{
								$res .= "<label>".$input.$arAnswer["MESSAGE"]."</label>";
							}
							
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "checkbox":
							if (strpos($arAnswer["FIELD_PARAM"], "id=") === false)
							{
								$ans_id = $arAnswer["ID"];
								$arAnswer["FIELD_PARAM"] .= " id=\"".$ans_id."\"";
							}
							else
							{
								$ans_id = "";
							}					
						
							$value = CForm::GetCheckBoxValue($FIELD_SID, $arAnswer, $arResult["arrVALUES"]);

							if (strlen($arResult["FORM_ERRORS"]) > 0)
							{
								if (
									strpos(strtolower($arAnswer["FIELD_PARAM"]), "selected")!==false 
									|| 
									strpos(strtolower($arAnswer["FIELD_PARAM"]), "checked")!==false)
									{
										$arAnswer["FIELD_PARAM"] = eregi_replace("checked|selected", "", $arAnswer["FIELD_PARAM"]);
									}
							}
							
							$input = CForm::GetCheckBoxField(
								$FIELD_SID,
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);
								
							
							if (strlen($ans_id) > 0)
							{
								$res .= $input."<label for=\"".$ans_id."\">".$arAnswer["MESSAGE"]."</label>";
							}
							else
							{
								$res .= "<label>".$input.$arAnswer["MESSAGE"]."</label>";
							}
							
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "dropdown":
							if ($show_dropdown!="Y")
							{
								$value = CForm::GetDropDownValue($FIELD_SID, $arResult["arDropDown"], $arResult["arrVALUES"]);
								
								if (strlen($arResult["FORM_ERROR"]) > 0)
									for ($i=0;$i<=count($arDropDown[$FIELD_SID]["param"])-1;$i++)
										$arDropDown[$FIELD_SID]["param"][$i] = eregi_replace("checked|selected", "", $arDropDown[$FIELD_SID]["param"][$i]);
										
								$res .= CForm::GetDropDownField(
									$FIELD_SID,
									$arResult["arDropDown"][$FIELD_SID],
									$value,
									$arAnswer["FIELD_PARAM"]);
								$show_dropdown = "Y";
							}
							
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "multiselect":
							if ($show_multiselect!="Y")
							{
								$value = CForm::GetMultiSelectValue($FIELD_SID, $arResult["arMultiSelect"], $arResult["arrVALUES"]);
								
								if (strlen($arResult["FORM_ERROR"]) > 0)
									for ($i=0;$i<=count($arMultiSelect[$FIELD_SID]["param"])-1;$i++)
										$arMultiSelect[$FIELD_SID]["param"][$i] = eregi_replace("checked|selected", "", $arMultiSelect[$FIELD_SID]["param"][$i]);								
								$res .= CForm::GetMultiSelectField(
									$FIELD_SID,
									$arResult["arMultiSelect"][$FIELD_SID],
									$value,
									$arAnswer["FIELD_HEIGHT"],
									$arAnswer["FIELD_PARAM"]);
								$show_multiselect = "Y";
							}
							
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "text":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							
							$value = CForm::GetTextValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetTextField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
							
						case "hidden":

							$value = CForm::GetHiddenValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetHiddenField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;							
							
							break;
							
						case "password":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							
							$value = CForm::GetPasswordValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetPasswordField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "email":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							$value = CForm::GetEmailValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetEmailField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);
							
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;							
							
							break;
						case "url":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							$value = CForm::GetUrlValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetUrlField(
								$arAnswer["ID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "textarea":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $arAnswer["MESSAGE"];
							}
							
							if (intval($arAnswer["FIELD_WIDTH"]) <= 0) $arAnswer["FIELD_WIDTH"] = "40";
							if (intval($arAnswer["FIELD_HEIGHT"]) <= 0) $arAnswer["FIELD_HEIGHT"] = "5";
							
							$value = CForm::GetTextAreaValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetTextAreaField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_HEIGHT"],
								$arAnswer["FIELD_PARAM"],
								$value
								);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "date":
							if (strlen(trim($arAnswer["MESSAGE"]))>0) 
							{
								$res .= $arAnswer["MESSAGE"];
							}
							$value = CForm::GetDateValue($arAnswer["ID"], $arAnswer, $arResult["arrVALUES"]);
							$res .= CForm::GetDateField(
								$arAnswer["ID"],
								$arResult["arForm"]["SID"],
								$value,
								$arAnswer["FIELD_WIDTH"],
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res." (".CSite::GetDateFormat("SHORT").")";
							
							break;
						case "image":
							if ($arFile = CFormResult::GetFileByAnswerID($arParams["RESULT_ID"], $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									if ($arFile["USER_FILE_IS_IMAGE"]=="Y") 
									{
										$res .= CFile::ShowImage($arFile["USER_FILE_ID"], 0, 0, "border=0", "", true);
										$res .= "<br />"; 
										$res .= '<input type="checkbox" value="Y" name="form_image_'.$arAnswer['ID'].'_del" id="form_image_'.$arAnswer['ID'].'_del" /><label for="form_image_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';
									} //endif;
								} //endif;
							} // endif
						
							$res .= CForm::GetFileField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								"IMAGE",
								0,
								"",
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
						case "file":
							
							if ($arFile = CFormResult::GetFileByAnswerID($arParams["RESULT_ID"], $arAnswer["ID"]))
							{
								if (intval($arFile["USER_FILE_ID"])>0)
								{
									$res .= "<a title=\"".GetMessage("FORM_VIEW_FILE")."\" target=\"_blank\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."\">".htmlspecialchars($arFile["USER_FILE_NAME"])."</a>&nbsp;(";
									$a = array("b", "Kb", "Mb", "Gb");
									$pos = 0;
									$size = $arFile["USER_FILE_SIZE"];
									while($size>=1024) {$size /= 1024; $pos++;}
									$res .= round($size,2)." ".$a[$pos];
									$res .= ")&nbsp;&nbsp;[&nbsp;<a title=\"".str_replace("#FILE_NAME#", $arFile["USER_FILE_NAME"], GetMessage("FORM_DOWNLOAD_FILE"))."\" class=\"tablebodylink\" href=\"/bitrix/tools/form_show_file.php?rid=".$arParams["RESULT_ID"]."&hash=".$arFile["USER_FILE_HASH"]."&lang=".LANGUAGE_ID."&action=download\">".GetMessage("FORM_DOWNLOAD")."</a>&nbsp;]<br />";
									$res .= '<input type="checkbox" value="Y" name="form_file_'.$arAnswer['ID'].'_del" id="form_file_'.$arAnswer['ID'].'_del" /><label for="form_file_'.$arAnswer['ID'].'_del">'.GetMessage('FORM_DELETE_FILE').'</label><br />';
									
									$res .= "<br />"; 
								} //endif;
							} //endif;							
							
							
							$res .= CForm::GetFileField(
								$arAnswer["ID"],
								$arAnswer["FIELD_WIDTH"],
								"FILE",
								0,
								"",
								$arAnswer["FIELD_PARAM"]);
								
							$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
							
							break;
					} //endswitch;
				} //endwhile;
				
				
			} //endif(is_array($arAnswers[$FIELD_SID]));
			elseif (is_array($arResult["arQuestions"][$FIELD_SID]) && $arResult["arQuestions"][$FIELD_SID]["ADDITIONAL"] == "Y")
			{
			
				$res = "";
				
				switch ($arResult["arQuestions"][$FIELD_SID]["FIELD_TYPE"])
				{
					case "text":
						$value = CForm::GetTextAreaValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetTextAreaField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"],
							"60",
							"5",
							"",
							$value
							);
							
						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
						
						break;
					case "integer":
						$value = CForm::GetTextValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetTextField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], 
							$value);
							
						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res;
						
						break;
					case "date":
						$value = CForm::GetDateValue("ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"], array(), $arResult["arrVALUES"]);
						$res .= CForm::GetDateField(
							"ADDITIONAL_".$arResult["arQuestions"][$FIELD_SID]["ID"],
							$arResult["arForm"]["SID"],
							$value);
							
						$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"][] = $res." (".CSite::GetDateFormat("SHORT").")";
						
						break;
				} //endswitch;
			}
			
			$arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"] = implode("<br />", $arResult["QUESTIONS"][$FIELD_SID]["HTML_CODE"]);
			
			// ******************************************************************************* //
			
			if ($arResult["QUESTIONS"][$FIELD_SID]["IS_INPUT_CAPTION_IMAGE"] == "Y")
			{
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ID"] = $arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"];
				//$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"] = CFile::GetPath($FORM->arQuestions[$FIELD_SID]["IMAGE_ID"]);
				
				// assign field image path
				$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"] = CFile::GetPath($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]);
				
				// check image file existance and assign image data
				if (
					file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"]) 
					&& 
					list(
						$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["WIDTH"], 
						$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HEIGHT"], 
						$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["TYPE"], 
						$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["ATTR"]
					) = @getimagesize($_SERVER["DOCUMENT_ROOT"].$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["URL"])
				)
				{
					$arResult["QUESTIONS"][$FIELD_SID]["IMAGE"]["HTML_CODE"] = CFile::ShowImage($arResult["arQuestions"][$FIELD_SID]["IMAGE_ID"]);
				}
			}
			
			// get answers raw structure
			$arResult["QUESTIONS"][$FIELD_SID]["STRUCTURE"] = $arResult["arAnswers"][$FIELD_SID];
			
			// nullify value
			$arResult["QUESTIONS"][$FIELD_SID]["VALUE"] = "";
		}

		if ($arResult["isFormErrors"] == "Y")
		{
			ob_start();
			if ($arParams['USE_EXTENDED_ERRORS'] == 'N' || !is_array($arResult['FORM_ERRORS']))
				ShowError($arResult["FORM_ERRORS"]);
			else
				ShowError(implode('<br />', $arResult["FORM_ERRORS"]));
				
			$arResult["FORM_ERRORS_TEXT"] = ob_get_contents();
			ob_end_clean();
		}
		
		$arResult["SUBMIT_BUTTON"] = "<input ".(intval($arResult["F_RIGHT"]) < 10 ? "disabled=\"disabled\"" : "")." type=\"submit\" name=\"web_form_submit\" value=\"".(strlen(trim($arResult["arForm"]["BUTTON"])) <= 0 ? GetMessage("FORM_ADD") : $arResult["arForm"]["BUTTON"])."\" />";
		$arResult["APPLY_BUTTON"] = "<input type=\"hidden\" name=\"web_form_apply\" value=\"Y\" /><input type=\"submit\" name=\"web_form_apply\" value=\"".GetMessage("FORM_APPLY")."\" />";
		$arResult["RESET_BUTTON"] = "<input type=\"reset\" value=\"".GetMessage("FORM_RESET")."\" />";
		$arResult["REQUIRED_STAR"] = $arResult["REQUIRED_SIGN"];
		
		// include default template
		
		$this->IncludeComponentTemplate();
		
		
	}
	else
	{
		echo ShowError(GetMessage($arResult["ERROR"]));
	}
}
else
{
	echo ShowError(GetMessage("FORM_MODULE_NOT_INSTALLED"));
}
?>