<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	'GROUPS' => array(
	),
	'PARAMETERS' => array(
		'KEY' => array(
			'NAME' => GetMessage('MYMS_PARAM_KEY'),
			'TYPE' => 'STRING',
			'PARENT' => 'BASE',
			'DEFAULT' => '',
		),
		
		'INIT_MAP_TYPE' => array(
			'NAME' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE'),
			'TYPE' => 'LIST',
			'VALUES' => array(
				'NORMAL' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_MAP'),
				'SATELLITE' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_SATELLITE'),
				'HYBRID' => GetMessage('MYMS_PARAM_INIT_MAP_TYPE_HYBRID')
			),
			'DEFAULT' => 'NORMAL',
			'ADDITIONAL_VALUES' => 'N',
			'PARENT' => 'BASE',
		),
		
		'MAP_WIDTH' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_WIDTH'),
			'TYPE' => 'STRING',
			'DEFAULT' => '600',
			'PARENT' => 'BASE',
		),
		
		'MAP_HEIGHT' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_HEIGHT'),
			'TYPE' => 'STRING',
			'DEFAULT' => '500',
			'PARENT' => 'BASE',
		),
		
		'CONTROLS' => array(
			'NAME' => GetMessage('MYMS_PARAM_CONTROLS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'LARGE_MAP_CONTROL' => GetMessage('MYMS_PARAM_CONTROLS_LARGE_MAP_CONTROL'), 
				'SMALL_MAP_CONTROL' => GetMessage('MYMS_PARAM_CONTROLS_SMALL_MAP_CONTROL'), 
				'SMALL_ZOOM_CONTROL' => GetMessage('MYMS_PARAM_CONTROLS_SMALL_ZOOM_CONTROL'), 
				'MINIMAP' => GetMessage('MYMS_PARAM_CONTROLS_MINIMAP'), 
				'TYPECONTROL' => GetMessage('MYMS_PARAM_CONTROLS_TYPECONTROL'), 
				'HTYPECONTROL' => GetMessage('MYMS_PARAM_CONTROLS_HTYPECONTROL'), 
				'SCALELINE' => GetMessage('MYMS_PARAM_CONTROLS_SCALELINE')
			),
			
			'DEFAULT' => array('LARGE_MAP_CONTROL', 'HTYPECONTROL', 'MINIMAP', 'SCALELINE'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),
		
		'OPTIONS' => array(
			'NAME' => GetMessage('MYMS_PARAM_OPTIONS'),
			'TYPE' => 'LIST',
			'MULTIPLE' => 'Y',
			'VALUES' => array(
				'ENABLE_SCROLL_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_SCROLL_ZOOM'),
				'ENABLE_DBLCLICK_ZOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DBLCLICK_ZOOM'),
				'ENABLE_CONT_ZOOOM' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_CONT_ZOOM'),
				'ENABLE_DRAGGING' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_DRAGGING'),
				'ENABLE_GOOGLE_BAR' => GetMessage('MYMS_PARAM_OPTIONS_ENABLE_GOOGLE_BAR'),
			),
			
			'DEFAULT' => array('ENABLE_SCROLL_ZOOM', 'ENABLE_DBLCLICK_ZOOM', 'ENABLE_DRAGGING'),
			'PARENT' => 'ADDITIONAL_SETTINGS',
		),

		'MAP_ID' => array(
			'NAME' => GetMessage('MYMS_PARAM_MAP_ID'),
			'TYPE' => 'STRING',
			'DEFAULT' => '',
			'PARENT' => 'ADDITIONAL_SETTINGS',
		
		),
	),
);
?>