function OnGoogleMapSettingsEdit_search(arParams)
{
	if (null != window.jsGoogleCEOpener_search)
	{
		try	{window.jsGoogleCEOpener_search.Close();}catch (e) {}
		window.jsGoogleCEOpener_search = null;
	}

	var oCallBack = function()
	{
		window.jsGoogleCEOpener_search = new JCEditorOpener_search(arParams);
	};

	if (!window.JCPopup)
	{
		jsUtils.loadJSFile('/bitrix/js/main/public_tools.js', oCallBack);
	}
	else
	{
		oCallBack();
	}
}

function JCEditorOpener_search(arParams)
{
	var _this = this;

	var jsOptions = arParams.data.split('||');

	var obButton = document.createElement('BUTTON');
	arParams.oCont.appendChild(obButton);
	
	obButton.innerHTML = jsOptions[1];
	obButton.onclick = function ()
	{
		_this.arElements = arParams.getElements();
		if (!_this.arElements)
			return false;

		var map_key = _this.arElements.KEY.value;
		
		if (jsUtils.trim(map_key) == '')
		{
			alert(jsOptions[2]);
			return false;
		}
		
		_this.pubstyle = jsUtils.loadCSSFile('/bitrix/themes/.default/pubstyles.css');
		
		if (null == window.jsPopup_google_map)
		{
			window.jsPopup_google_map = new JCPopup({suffix: "google_map", zIndex: 2000});
			
			if (!jsPopup_google_map._CloseDialog)
			{
				jsPopup_google_map._CloseDialog = jsPopup_google_map.CloseDialog;
				jsPopup_google_map.CloseDialog = function()
				{
					if (_this.pubstyle && _this.pubstyle.parentNode)
						_this.pubstyle.parentNode.removeChild(_this.pubstyle);
					
					jsPopup_google_map._CloseDialog();
				};
			}
			
			if (jsUtils.IsIE())
			{
				window.jsPopup_google_map.ShowDialog = function(url, arParams)
				{
					if (document.getElementById(this.div_id))
						this.CloseDialog();

					if (!arParams.min_width) arParams.min_width = 250;
					if (!arParams.min_height) arParams.min_height = 200;

					var pos = url.indexOf('?');
					
					if (pos == -1)
						var data = '';
					else
					{
						var data = url.substring(pos+1);
						url = url.substring(0, pos);
					}
					
					data = data.length > 0 ? 'mode=public&' + data : 'mode=public';
					
					this.check_url = url
					this.url = url;
					this.arParams = arParams;
					this.CreateOverlay();
					jsExtLoader.onajaxfinish = window['JCPopup_AjaxAction' + this.suffix];
					jsExtLoader.startPost(url, data);
				}
			}
		}
		
		var strUrl = '/bitrix/components/bitrix/map.google.search/settings/settings.php?lang=' + jsOptions[0] + 
				'&bxpiheight=430' + 
				'&KEY=' + jsUtils.urlencode(map_key) + 
				'&INIT_MAP_TYPE=' + jsUtils.urlencode(_this.arElements.INIT_MAP_TYPE.value) + 
				'&MAP_DATA=' + jsUtils.urlencode(arParams.oInput.value);
		
		window.jsPopup_google_map.ShowDialog(strUrl, {width:'800', height:'550', resize:false});

		return false;
	}
	
	this.saveData = function(strData, view)
	{
		arParams.oInput.value = strData;
		if (null != arParams.oInput.onchange)
			arParams.oInput.onchange();
		
		if (view)
		{
			_this.arElements.INIT_MAP_TYPE.value = view;
			if (null != _this.arElements.INIT_MAP_TYPE.onchange)
				_this.arElements.INIT_MAP_TYPE.onchange();
		}
		
		_this.Close(false);
	}
}

JCEditorOpener_search.prototype.Close = function(e)
{
	if (false !== e)
		jsUtils.PreventDefault(e);

	if (null != window.jsPopup_google_map)
	{
		window.jsPopup_google_map.CloseDialog();
	}
}