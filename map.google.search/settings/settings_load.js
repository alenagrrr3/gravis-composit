var jsGoogleCE = {
	map: null,
	arData: null,
	obForm: null,
	
	currentView: '',
	
	bPositionFixed: true,

	__arValidKeys: ['google_lat', 'google_lon', 'google_scale'],
	
	init: function() 
	{
		jsUtils.loadCSSFile('/bitrix/components/bitrix/map.google.system/templates/.default/style.css');
	
		jsGoogleCE.map = GLOBAL_arMapObjects['system_search_edit'];
		
		jsGoogleCE.arData = arPositionData;
		jsGoogleCE.obForm = document.forms['bx_popup_form_google_map'];
		jsGoogleCE.obForm.onsubmit = jsGoogleCE.__saveChanges;
		
		GEvent.addListener(jsGoogleCE.map, 'moveend', jsGoogleCE.__getPositionValues);
		GEvent.addListener(jsGoogleCE.map, 'maptypechanged', jsGoogleCE.__getPositionValues);
		
		if (!jsGoogleCE.arData.google_lat || !jsGoogleCE.arData.google_lon || !jsGoogleCE.arData.google_scale)
		{
			var obPos = jsGoogleCE.map.getCenter();
			jsGoogleCE.arData.google_lat = obPos.lat();
			jsGoogleCE.arData.google_lon = obPos.lng();
			jsGoogleCE.arData.google_scale = jsGoogleCE.map.getZoom();
			jsGoogleCE.bPositionFixed = false;
		}
		else
		{
			jsGoogleCE.arData.google_scale = parseInt(jsGoogleCE.arData.google_scale);
			jsGoogleCE.bPositionFixed = true;
		}
		
		//alert(2);

		jsGoogleCE.setControlValue('google_lat', jsGoogleCE.arData.google_lat);
		jsGoogleCE.setControlValue('google_lon', jsGoogleCE.arData.google_lon);
		jsGoogleCE.setControlValue('google_scale', jsGoogleCE.arData.google_scale);

		jsGoogleCE.currentView = jsGoogleMess.current_view;
		
		var obType = jsGoogleCE.map.getCurrentMapType();
		jsGoogleCE.setControlValue('google_view', obType.getName());
		
		document.getElementById('bx_restore_position').onclick = jsGoogleCE.restorePositionValues;
		document.getElementById('bx_google_map_controls').style.visibility = 'visible';
		document.getElementById('bx_google_map_address_search').style.visibility = 'visible';
	},
	
	__getPositionValues: function()
	{
		if (jsGoogleCE.bPositionFixed)
			return;
	
		var obPos = jsGoogleCE.map.getCenter();
		jsGoogleCE.arData.google_lat = obPos.lat();
		jsGoogleCE.arData.google_lon = obPos.lng();
		jsGoogleCE.arData.google_scale = jsGoogleCE.map.getZoom();
		
		jsGoogleCE.setControlValue('google_lat', jsGoogleCE.arData.google_lat);
		jsGoogleCE.setControlValue('google_lon', jsGoogleCE.arData.google_lon);
		jsGoogleCE.setControlValue('google_scale', jsGoogleCE.arData.google_scale);
		
		var obCurrentView = jsGoogleCE.map.getCurrentMapType();
		
		jsGoogleCE.currentView = (
			obCurrentView == G_HYBRID_MAP
			? 'HYBRID'
			: (
				obCurrentView == G_SATELLITE_MAP
				? 'SATELLITE'
				: 'NORMAL'
			)
		);
		
		jsGoogleCE.setControlValue('google_view', obCurrentView.getName());
	},
	
	restorePositionValues: function(e)
	{
		jsUtils.PreventDefault(e);
	
		if (jsGoogleCE.currentView && window['G_' + jsGoogleCE.currentView + '_MAP'])
			jsGoogleCE.map.setMapType(window['G_' + jsGoogleCE.currentView + '_MAP']);
		
		jsGoogleCE.map.setZoom(jsGoogleCE.arData.google_scale);
		jsGoogleCE.map.panTo(new GLatLng(jsGoogleCE.arData.google_lat, jsGoogleCE.arData.google_lon));
	},
	
	setFixedFlag: function(value)
	{
		jsGoogleCE.bPositionFixed = value;
		if (!value)
			jsGoogleCE.__getPositionValues();
	},
	
	setControlValue: function(control, value)
	{
		var obControl = jsGoogleCE.obForm['bx_' + control];
		if (null != obControl)
			obControl.value = value;
			
		var obControlOut = document.getElementById('bx_' + control + '_value');
		if (null != obControlOut)
			obControlOut.innerHTML = value;
	},
	
	__checkValidKey: function(key)
	{
		if (Number(key) == key)
			return true;
	
		for (var i = 0, len = jsGoogleCE.__arValidKeys.length; i < len; i++)
		{
			if (jsGoogleCE.__arValidKeys[i] == key)
				return true;
		}
		
		return false;
	},
	
	__serialize: function(obj)
	{
  		if (typeof(obj) == 'object')
  		{
    		var str = '', cnt = 0;
		    for (var i in obj)
		    {
				if (jsGoogleCE.__checkValidKey(i))
				{
					++cnt;
					str += jsGoogleCE.__serialize(i) + jsGoogleCE.__serialize(obj[i]);
				}
		    }
		    
    		str = "a:" + cnt + ":{" + str + "}";
    		
    		return str;
		}
		else if (typeof(obj) == 'boolean')
		{
			return 'b:' + (obj ? 1 : 0) + ';';
		}
		else if (null == obj)
		{
			return 'N;'
		}
		else if (Number(obj) == obj && obj != '' && obj != ' ')
		{
			if (Math.floor(obj) == obj)
				return 'i:' + obj + ';';
			else
				return 'd:' + obj + ';';
    	}
  		else if(typeof(obj) == 'string')
  		{
			obj = obj.replace(/\r\n/g, "\n");
			obj = obj.replace(/\n/g, "###RN###");

			var offset = 0;
			if (window._global_BX_UTF)
			{
				for (var q = 0, cnt = obj.length; q < cnt; q++)
				{
					if (obj.charCodeAt(q) > 127) offset++;
				}
			}
			
  			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	},
	
	__saveChanges: function()
	{
		if (!jsGoogleCE.map) 
			return false;
			
		jsGoogleCE.bAddPointMode = false;
		
		if (jsGoogleCE.arData['PLACEMARKS'])
		{
			var arNewPlacemarks = [];
		
			for(var i = 0, len = jsGoogleCE.arData.PLACEMARKS.length; i < len; i++)
			{
				if (null == jsGoogleCE.arData.PLACEMARKS[i].DELETED)
					arNewPlacemarks[arNewPlacemarks.length] = jsGoogleCE.arData.PLACEMARKS[i];
			}
			
			jsGoogleCE.arData.PLACEMARKS = arNewPlacemarks;
		}
	
		window.jsGoogleCEOpener_search.saveData(jsGoogleCE.__serialize(jsGoogleCE.arData), jsGoogleCE.currentView);
		delete jsGoogleCE.map;
		return false;
	}
}

var jsGoogleCESearch = {
	bInited: false,

	map: null,
	geocoder: null,
	obInput: null,
	timerID: null,
	timerDelay: 1000,
	
	arSearchResults: [],
	
	obOut: null,
	
	__init: function(input)
	{
		if (jsGoogleCESearch.bInited) return;
		
		jsGoogleCESearch.map = jsGoogleCE.map;
		jsGoogleCESearch.obInput = input;
		
		input.form.onsubmit = function() {jsGoogleCESearch.doSearch(); return false;}
		
		input.onfocus = jsGoogleCESearch.showResults;
		input.onblur = jsGoogleCESearch.hideResults;
		
		jsGoogleCESearch.bInited = true;
	},
	
	setTypingStarted: function(input)
	{
		if (!jsGoogleCESearch.bInited)
			jsGoogleCESearch.__init(input);

		jsGoogleCESearch.hideResults();
			
		if (null != jsGoogleCESearch.timerID)
			clearTimeout(jsGoogleCESearch.timerID);
	
		jsGoogleCESearch.timerID = setTimeout(jsGoogleCESearch.doSearch, jsGoogleCESearch.timerDelay);
	},
	
	doSearch: function()
	{
		var value = jsUtils.trim(jsGoogleCESearch.obInput.value);
		if (value.length > 1)
		{
			if (null == jsGoogleCESearch.geocoder)
				jsGoogleCESearch.geocoder = new GClientGeocoder();
		
			jsGoogleCESearch.geocoder.getLocations(value, jsGoogleCESearch.__searchResultsLoad);
		}
	},
	
	handleError: function()
	{
		alert(jsGoogleCE.jsMess.mess_error);
	},
	
	__generateOutput: function()
	{
		var obPos = jsUtils.GetRealPos(jsGoogleCESearch.obInput);
		
		jsGoogleCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsGoogleCESearch.obOut.className = 'bx-google-address-search-results';
		jsGoogleCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
		jsGoogleCESearch.obOut.style.left = obPos.left + 'px';
	},

	__searchResultsLoad: function(obResult)
	{
		var _this = jsGoogleCESearch;
		
		if (!obResult)
		{
			_this.handleError();
		}
		else
		{
			if (null == _this.obOut)
				_this.__generateOutput();
			
			_this.obOut.innerHTML = '';
			_this.clearSearchResults();
			
			if (obResult.Status.code == 200)
				for (var len = 0; obResult.Placemark[len]; len++) {}
			else
				var len = 0;
			
			if (len > 0) 
			{
				for (var i = 0; i < len; i++)
				{
					_this.arSearchResults[i] = new GLatLng(
						obResult.Placemark[i].Point.coordinates[1], 
						obResult.Placemark[i].Point.coordinates[0]
					);
					
					var obListElement = document.createElement('LI');
					
					if (i == 0)
						obListElement.className = 'bx-google-first';

					var obLink = document.createElement('A');
					obLink.href = "javascript:void(0)";
					var obText = obLink.appendChild(document.createElement('SPAN'));
					obText.appendChild(document.createTextNode(obResult.Placemark[i].address));
					
					obLink.BXSearchIndex = i;
					obLink.onclick = _this.__showSearchResult;
					
					obListElement.appendChild(obLink);
					_this.obOut.appendChild(obListElement);
				}
			} 
			else 
			{
				//var str = _this.jsMess.mess_search_empty;
				_this.obOut.innerHTML = '<li class="bx-google-notfound">' + window.jsGoogleMess.nothing_found + '</li>';
			}
			
			_this.showResults();
		}
		
		//_this.map.redraw();
	},
	
	__showSearchResult: function()
	{
		if (null !== this.BXSearchIndex)
		{
			jsGoogleCESearch.map.panTo(jsGoogleCESearch.arSearchResults[this.BXSearchIndex]);
		}
	},
	
	showResults: function()
	{
		if (null != jsGoogleCESearch.obOut)
			jsGoogleCESearch.obOut.style.display = 'block';
	},

	hideResults: function()
	{
		if (null != jsGoogleCESearch.obOut)
		{
			setTimeout("jsGoogleCESearch.obOut.style.display = 'none'", 300);
		}
	},
	
	clearSearchResults: function()
	{
		for (var i = 0; i < jsGoogleCESearch.arSearchResults.length; i++)
		{
			delete jsGoogleCESearch.arSearchResults[i];
		}

		jsGoogleCESearch.arSearchResults = [];
	},
	
	clear: function()
	{
		if (!jsGoogleCESearch.bInited)
			return;
			
		jsGoogleCESearch.bInited = false;
		if (null != jsGoogleCESearch.obOut)
		{
			jsGoogleCESearch.obOut.parentNode.removeChild(jsGoogleCESearch.obOut);
			jsGoogleCESearch.obOut = null;
		}
		
		jsGoogleCESearch.arSearchResults = [];
		jsGoogleCESearch.map = null;
		jsGoogleCESearch.geocoder = null;
		jsGoogleCESearch.obInput = null;
		jsGoogleCESearch.timerID = null;
	}
}
