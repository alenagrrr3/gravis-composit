var jsYandexCE = {
	map: null,
	arData: null,
	obForm: null,
	
	currentView: '',
	
	bPositionFixed: true,
	bAddPointMode: false,
	bAddPolyMode: false,
	
	DblClickObserver: null,
	
	__arValidKeys: ['yandex_lat', 'yandex_lon', 'yandex_scale', 'PLACEMARKS', 'LON', 'LAT', 'TEXT'],
	
	__currentPolyLine: null,
	__currentPolyLineObject: null,
	
	//arMapViews: {MAP: jsYandexMess.MAP_VIEW_MAP, SATELLITE: jsYandexMess.MAP_VIEW_SATELLITE, HYBRID: jsYandexMess.MAP_VIEW_HYBRID},
	
	init: function() 
	{
		jsUtils.loadCSSFile('/bitrix/components/bitrix/map.yandex.system/templates/.default/style.css');
	
		jsYandexCE.map = GLOBAL_arMapObjects['system_view_edit'];
		
		jsYandexCE.arData = arPositionData;
		jsYandexCE.obForm = document.forms['bx_popup_form_yandex_map'];
		jsYandexCE.obForm.onsubmit = jsYandexCE.__saveChanges;
		
		
		YMaps.Events.observe(jsYandexCE.map, jsYandexCE.map.Events.Move, jsYandexCE.__getPositionValues);
		//alert(1);
		YMaps.Events.observe(jsYandexCE.map, jsYandexCE.map.Events.Update, jsYandexCE.__getPositionValues);
		//alert(YMaps.Events);
		YMaps.Events.observe(jsYandexCE.map, jsYandexCE.map.Events.ChangeType, jsYandexCE.__getPositionValues);
		
		if (!jsYandexCE.arData.yandex_lat || !jsYandexCE.arData.yandex_lon || !jsYandexCE.arData.yandex_scale)
		{
			var obPos = jsYandexCE.map.getCenter();
			jsYandexCE.arData.yandex_lat = obPos.getLat();
			jsYandexCE.arData.yandex_lon = obPos.getLng();
			jsYandexCE.arData.yandex_scale = jsYandexCE.map.getZoom();
			jsYandexCE.bPositionFixed = false;
		}
		else
		{
			jsYandexCE.bPositionFixed = true;
		}

		jsYandexCE.setControlValue('yandex_lat', jsYandexCE.arData.yandex_lat);
		jsYandexCE.setControlValue('yandex_lon', jsYandexCE.arData.yandex_lon);
		jsYandexCE.setControlValue('yandex_scale', jsYandexCE.arData.yandex_scale);

		jsYandexCE.currentView = jsYandexMess.current_view;
		
		var obType = jsYandexCE.map.getType();
		jsYandexCE.setControlValue('yandex_view', obType.getName());
		
		if (jsYandexCE.arData.PLACEMARKS && jsYandexCE.arData.PLACEMARKS.length > 0)
		{
			for (var i = 0, len = jsYandexCE.arData.PLACEMARKS.length; i < len; i++)
			{
				jsYandexCE.addCustomPoint(jsYandexCE.arData.PLACEMARKS[i], i);
			}
		}
		
		document.getElementById('bx_restore_position').onclick = jsYandexCE.restorePositionValues;
		document.getElementById('bx_yandex_map_controls').style.visibility = 'visible';
		document.getElementById('bx_yandex_map_address_search').style.visibility = 'visible';
	},
	
	__getPositionValues: function()
	{
		if (jsYandexCE.bPositionFixed)
			return;
	
		var obPos = jsYandexCE.map.getCenter();
		jsYandexCE.arData.yandex_lat = obPos.getLat();
		jsYandexCE.arData.yandex_lon = obPos.getLng();
		jsYandexCE.arData.yandex_scale = jsYandexCE.map.getZoom();
		
		jsYandexCE.setControlValue('yandex_lat', jsYandexCE.arData.yandex_lat);
		jsYandexCE.setControlValue('yandex_lon', jsYandexCE.arData.yandex_lon);
		jsYandexCE.setControlValue('yandex_scale', jsYandexCE.arData.yandex_scale);
		
		var obCurrentView = jsYandexCE.map.getType();
		
		jsYandexCE.currentView = (
			obCurrentView == YMaps.MapType.HYBRID
			? 'HYBRID'
			: (
				obCurrentView == YMaps.MapType.SATELLITE
				? 'SATELLITE'
				: 'MAP'
			)
		);
		
		jsYandexCE.setControlValue('yandex_view', obCurrentView.getName());
	},
	
	restorePositionValues: function(e)
	{
		jsUtils.PreventDefault(e);
	
		//alert(jsYandexCE.currentView);
		if (jsYandexCE.currentView && YMaps.MapType[jsYandexCE.currentView])
			jsYandexCE.map.setType(YMaps.MapType[jsYandexCE.currentView]);
		
		jsYandexCE.map.setZoom(jsYandexCE.arData.yandex_scale);
		jsYandexCE.map.panTo(new YMaps.GeoPoint(jsYandexCE.arData.yandex_lon, jsYandexCE.arData.yandex_lat));
	},
	
	setFixedFlag: function(value)
	{
		jsYandexCE.bPositionFixed = value;
		if (!value)
			jsYandexCE.__getPositionValues();
	},
	
	setControlValue: function(control, value)
	{
		var obControl = jsYandexCE.obForm['bx_' + control];
		if (null != obControl)
			obControl.value = value;
			
		var obControlOut = document.getElementById('bx_' + control + '_value');
		if (null != obControlOut)
			obControlOut.innerHTML = value;
	},
	
	__updatePointPosition: function()
	{
		if (null == this.BX_PLACEMARK_INDEX)
			return;
		
		var obPoint = this.getGeoPoint();
		
		jsYandexCE.arData.PLACEMARKS[this.BX_PLACEMARK_INDEX].LON = obPoint.getLng();
		jsYandexCE.arData.PLACEMARKS[this.BX_PLACEMARK_INDEX].LAT = obPoint.getLat();
	},
	
	addPoint: function()
	{
		//jsUtils.PreventDefault(e);
	
		if (jsYandexCE.bAddPointMode)
		{
			jsYandexCE.bAddPointMode = false;
			jsYandexCE.map.enableDblClickZoom();
			document.getElementById('bx_yandex_addpoint_link').style.display = 'block';
			document.getElementById('bx_yandex_addpoint_message').style.display = 'none';
			
			jsYandexCE.DblClickObserver.cleanup();
		}
		else
		{
			jsYandexCE.bAddPointMode = true;
			jsYandexCE.map.disableDblClickZoom();
			document.getElementById('bx_yandex_addpoint_link').style.display = 'none';
			document.getElementById('bx_yandex_addpoint_message').style.display = 'block';
			
			jsYandexCE.DblClickObserver = YMaps.Events.observe(jsYandexCE.map, jsYandexCE.map.Events.DblClick, jsYandexCE.__addPoint);
		}
	},

	__createPlaceMark: function(arPlacemark, index)
	{
		if (null == jsYandexCE.arData.PLACEMARKS)
			jsYandexCE.arData.PLACEMARKS = [];
		
		if (null == index)
		{
			index = jsYandexCE.arData.PLACEMARKS.length;
			jsYandexCE.arData.PLACEMARKS[index] = {
				TEXT: arPlacemark.TEXT
			};
		}
		
		if (null != arPlacemark.POS)
		{
			jsYandexCE.arData.PLACEMARKS[index].LON = arPlacemark.POS.getLng();
			jsYandexCE.arData.PLACEMARKS[index].LAT = arPlacemark.POS.getLat();
		}
		else
		{
			jsYandexCE.arData.PLACEMARKS[index].LAT = arPlacemark.LAT;
			jsYandexCE.arData.PLACEMARKS[index].LON = arPlacemark.LON;
		}

		var obPointView = jsYandexCE.__createPointView();
		obPointView.id = 'BX_PLACEMARK_' + index;
		
		obPointView.BXPlacemark = new YMaps.Placemark(new YMaps.GeoPoint(jsYandexCE.arData.PLACEMARKS[index].LON, jsYandexCE.arData.PLACEMARKS[index].LAT), {draggable: 1});
		obPointView.BXPlacemark.BX_PLACEMARK_INDEX = index;
		
		YMaps.Events.observe(obPointView.BXPlacemark, obPointView.BXPlacemark.Events.DragEnd, jsYandexCE.__updatePointPosition);
		
		var obInput = document.createElement('TEXTAREA');
		obInput.BX_PLACEMARK_INDEX = index;
		obInput.value = arPlacemark.TEXT;
		
		obInput.onkeyup = jsYandexCE.__updatePointView;
		obInput.onblur = jsYandexCE.__updatePointView;
		
		obPointView.BXPlacemark.setBalloonContent(obInput);
		
		var value_view = '';
		if (arPlacemark.TEXT.length > 0)
		{
			var rnpos = arPlacemark.TEXT.indexOf("\n");
			value_view = rnpos <= 0 ? arPlacemark.TEXT : arPlacemark.TEXT.substring(0, rnpos);
			value_view = value_view.replace(/>/g, '&gt;');
			value_view = value_view.replace(/</g, '&lt;');
		}

		jsYandexCE.__updatePointViewText(obPointView, value_view ? value_view : window.jsYandexMess.noname);
		obPointView.BXPlacemark.setIconContent(value_view);		
		
		jsYandexCE.map.addOverlay(obPointView.BXPlacemark);
		
		return obPointView.BXPlacemark;
	},
	
	addCustomPoint: function(arPointInfo, index)
	{
		jsYandexCE.__createPlaceMark({
			TEXT: arPointInfo.TEXT,
			LON: arPointInfo.LON,
			LAT: arPointInfo.LAT
		}, index);
	},
	
	__addPoint: function(obEvent)
	{
		if (!jsYandexCE.bAddPointMode)
			return;
	
		var obPlacemark = jsYandexCE.__createPlaceMark({
			TEXT: '', POS: obEvent.getGeoPoint()
		});
		obPlacemark.openBalloon();
	},
	
	__point_link_hover: function() {this.style.backgroundColor = "#E3E8F7"; this.firstChild.style.display = 'block';},
	__point_link_hout: function() {this.style.backgroundColor = "#FFFFFF"; this.firstChild.style.display = 'none';},
	
	__createPointView: function()
	{
		var obView = document.getElementById('bx_yandex_points').appendChild(document.createElement('LI'));
		
		var obDeleteLink = obView.appendChild(document.createElement('A'));
		//obDeleteLink.style.width = '30px';
		obDeleteLink.href = "javascript: void(0)";
		obDeleteLink.className = 'bx-yandex-delete';
		obDeleteLink.onclick = jsYandexCE.__deletePoint;
		obDeleteLink.style.display = 'none';

		var obLink = obView.appendChild(document.createElement('A'));
		obLink.className = 'bx-yandex-point';
		obLink.href = 'javascript:void(0)';
		obLink.onclick = jsYandexCE.__openPointBalloonFromView;
		obLink.innerHTML = window.jsYandexMess.noname;
		
		obView.onmouseover = jsYandexCE.__point_link_hover;
		obView.onmouseout = jsYandexCE.__point_link_hout;
		
		return obView;
	},
	
	__deletePoint: function(e)
	{
		jsUtils.PreventDefault(e);
	
		var obView = this.parentNode;
		
		jsYandexCE.arData.PLACEMARKS[obView.BXPlacemark.BX_PLACEMARK_INDEX].DELETED = 1;
		
		jsYandexCE.map.removeOverlay(obView.BXPlacemark);
		
		this.parentNode.parentNode.removeChild(this.parentNode);
	},
	
	__deletePoly: function()
	{
		var obView = this.parentNode;
		
		jsYandexCE.arData.POLYLINES[obView.BXPolyline.BX_POLYLINE_INDEX].DELETED = 1;
		jsYandexCE.map.removeOverlay(obView.BXPolyline);
		
		this.parentNode.parentNode.removeChild(this.parentNode);
	},
	
	__updatePointViewText: function(obPointView, str)
	{
		obPointView.firstChild.nextSibling.innerHTML = str;
	},
	
	__openPointBalloonFromView: function(e)
	{
		jsUtils.PreventDefault(e);
	
		if (this.parentNode.BXPlacemark._balloonVisible)
			this.parentNode.BXPlacemark.closeBalloon();
		else
			this.parentNode.BXPlacemark.openBalloon();
	},
	
	__updatePointView: function(e)
	{
		if (null == e)
			e = window.event;
		
		var value = this.value;
		
		var index = this.BX_PLACEMARK_INDEX;

		jsYandexCE.arData.PLACEMARKS[index].TEXT = value;
		
		var rnpos = value.indexOf("\n");

		var value_view = '';
		if (value.length > 0)
			value_view = rnpos <= 0 ? value : value.substring(0, rnpos);

		value_view = value_view.replace(/</g, '&lt;');
		value_view = value_view.replace(/>/g, '&gt;');
			
		var obView = document.getElementById('BX_PLACEMARK_' + index);
		
		jsYandexCE.__updatePointViewText(obView, value_view ? value_view : window.jsYandexMess.noname);
		obView.BXPlacemark.setIconContent(value_view);
		
		if (e.type == 'blur')
			obView.BXPlacemark.closeBalloon();
	},
	
	__checkValidKey: function(key)
	{
		if (Number(key) == key)
			return true;
	
		for (var i = 0, len = jsYandexCE.__arValidKeys.length; i < len; i++)
		{
			if (jsYandexCE.__arValidKeys[i] == key)
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
				if (jsYandexCE.__checkValidKey(i))
				{
					++cnt;
					str += jsYandexCE.__serialize(i) + jsYandexCE.__serialize(obj[i]);
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
		if (!jsYandexCE.map) 
			return false;
			
		jsYandexCE.bAddPointMode = false;
		
		if (jsYandexCE.arData['PLACEMARKS'])
		{
			var arNewPlacemarks = [];
		
			for(var i = 0, len = jsYandexCE.arData.PLACEMARKS.length; i < len; i++)
			{
				if (null == jsYandexCE.arData.PLACEMARKS[i].DELETED)
					arNewPlacemarks[arNewPlacemarks.length] = jsYandexCE.arData.PLACEMARKS[i];
			}
			
			jsYandexCE.arData.PLACEMARKS = arNewPlacemarks;
		}
	
		window.jsYandexCEOpener.saveData(jsYandexCE.__serialize(jsYandexCE.arData), jsYandexCE.currentView);
		delete jsYandexCE.map;
		return false;
	}
}

var jsYandexCESearch = {
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
		if (jsYandexCESearch.bInited) return;
		
		jsYandexCESearch.map = jsYandexCE.map;
		jsYandexCESearch.obInput = input;
		
		input.form.onsubmit = function() {jsYandexCESearch.doSearch(); return false;}
		
		input.onfocus = jsYandexCESearch.showResults;
		input.onblur = jsYandexCESearch.hideResults;
		
		jsYandexCESearch.bInited = true;
	},
	
	setTypingStarted: function(input)
	{
		if (!jsYandexCESearch.bInited)
			jsYandexCESearch.__init(input);

		jsYandexCESearch.hideResults();
			
		if (null != jsYandexCESearch.timerID)
			clearTimeout(jsYandexCESearch.timerID);
	
		jsYandexCESearch.timerID = setTimeout(jsYandexCESearch.doSearch, jsYandexCESearch.timerDelay);
	},
	
	doSearch: function()
	{
		var value = jsUtils.trim(jsYandexCESearch.obInput.value);
		if (value.length > 1)
		{
			var geocoder = new YMaps.Geocoder(value);
		
			YMaps.Events.observe(
				geocoder, 
				geocoder.Events.Load, 
				jsYandexCESearch.__searchResultsLoad
			);
			
			YMaps.Events.observe(
				geocoder, 
				geocoder.Events.Fault, 
				jsYandexCESearch.handleError
			);
		}
	},
	
	handleError: function(error)
	{
		alert(this.jsMess.mess_error + ': ' + error.message);
	},
	
	__generateOutput: function()
	{
		var obPos = jsUtils.GetRealPos(jsYandexCESearch.obInput);
		
		jsYandexCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsYandexCESearch.obOut.className = 'bx-yandex-address-search-results';
		jsYandexCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
		jsYandexCESearch.obOut.style.left = obPos.left + 'px';
	},

	__searchResultsLoad: function(geocoder)
	{
		var _this = jsYandexCESearch;
	
		if (null == _this.obOut)
			_this.__generateOutput();
			
		_this.obOut.innerHTML = '';
		_this.clearSearchResults();
		
		if (len = geocoder.length()) 
		{
			for (var i = 0; i < len; i++)
			{
				_this.arSearchResults[i] = geocoder.get(i);
				
				var obListElement = document.createElement('LI');
				
				if (i == 0)
					obListElement.className = 'bx-yandex-first';

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				var obText = obLink.appendChild(document.createElement('SPAN'));
				obText.appendChild(document.createTextNode(_this.arSearchResults[i].text));
				
				obLink.BXSearchIndex = i;
				obLink.onclick = _this.__showSearchResult;
				
				obListElement.appendChild(obLink);
				_this.obOut.appendChild(obListElement);
			}
		} 
		else 
		{
			//var str = _this.jsMess.mess_search_empty;
			_this.obOut.innerHTML = '<li class="bx-yandex-notfound">' + window.jsYandexMess.nothing_found + '</li>';
		}
		
		_this.showResults();
		
		//_this.map.redraw();
	},
	
	__showSearchResult: function()
	{
		if (null !== this.BXSearchIndex)
		{
			jsYandexCESearch.map.panTo(jsYandexCESearch.arSearchResults[this.BXSearchIndex].getGeoPoint());
			jsYandexCESearch.map.redraw();
		}
	},
	
	showResults: function()
	{
		if (null != jsYandexCESearch.obOut)
			jsYandexCESearch.obOut.style.display = 'block';
	},

	hideResults: function()
	{
		if (null != jsYandexCESearch.obOut)
		{
			setTimeout("jsYandexCESearch.obOut.style.display = 'none'", 300);
		}
	},
	
	clearSearchResults: function()
	{
		for (var i = 0; i < jsYandexCESearch.arSearchResults.length; i++)
		{
			delete jsYandexCESearch.arSearchResults[i];
		}

		jsYandexCESearch.arSearchResults = [];
	},
	
	clear: function()
	{
		if (!jsYandexCESearch.bInited)
			return;
			
		jsYandexCESearch.bInited = false;
		if (null != jsYandexCESearch.obOut)
		{
			jsYandexCESearch.obOut.parentNode.removeChild(jsYandexCESearch.obOut);
			jsYandexCESearch.obOut = null;
		}
		
		jsYandexCESearch.arSearchResults = [];
		jsYandexCESearch.map = null;
		jsYandexCESearch.geocoder = null;
		jsYandexCESearch.obInput = null;
		jsYandexCESearch.timerID = null;
	}
}

