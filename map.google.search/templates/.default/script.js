var JCBXGoogleSearch = function(map_id, obOut, jsMess)
{
	var _this = this;
	
	this.map_id = map_id;
	this.map = GLOBAL_arMapObjects[this.map_id];

	this.obOut = obOut;
	
	if (null == this.map)
		return false;

	this.arSearchResults = [];
	this.jsMess = jsMess;
	
	this.__searchResultsLoad = function(obResult)
	{
		if (null == _this.obOut)
			return;

		if (!obResult)
		{
			_this.handleError();
		}
			
		_this.obOut.innerHTML = '';
		_this.clearSearchResults();

		if (obResult.Status.code == 200)
			for (var len = 0; obResult.Placemark[len]; len++) {}
		else
			var len = 0;
			
		var obList = null;
		if (len > 0) 
		{
			obList = document.createElement('UL');
			obList.className = 'bx-google-search-results';
			var str = '';
			str += _this.jsMess.mess_search + ': <b>' + len + '</b> ' + _this.jsMess.mess_found + '.';
			
			for (var i = 0; i < len; i++)
			{
				_this.arSearchResults[i] = new GMarker(new GLatLng(
					obResult.Placemark[i].Point.coordinates[1], 
					obResult.Placemark[i].Point.coordinates[0]
				));
				
				_this.arSearchResults[i].BXTEXT = obResult.Placemark[i].address;

				GEvent.addListener(_this.arSearchResults[i], "click", _this.__pointClick);
				_this.map.addOverlay(_this.arSearchResults[i]);
				
				var obListElement = document.createElement('LI');

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				obLink.appendChild(document.createTextNode(_this.arSearchResults[i].BXTEXT));
				
				obLink.BXSearchIndex = i;
				obLink.onclick = _this.__showSearchResult;
				
				obListElement.appendChild(obLink);
				obList.appendChild(obListElement);
			}
		} 
		else 
		{
			var str = _this.jsMess.mess_search_empty;
		}
		
		_this.obOut.innerHTML = str;
		
		if (null != obList)
		{
			_this.obOut.appendChild(obList);
			_this.BXSearchIndex = 0;
			_this.__showSearchResult(0);
		}
	};
	
	this.__showSearchResult = function(index)
	{
		if (null == index || index.constructor == window.Event);
			index = this.BXSearchIndex;
	
		if (null != index && null != _this.arSearchResults[index])
		{
			_this.arSearchResults[index].openInfoWindow(_this.arSearchResults[index].BXTEXT);
			_this.map.panTo(_this.arSearchResults[index].getLatLng());
		}
	};
	
	this.searchByAddress = function(str)
	{
		//str = jsUtils.trim(str);
		str = str.replace(/^[\s\r\n]+/g, '').replace(/[\s\r\n]+$/g, '');
		if (str.length > 1)
		{
			if (null == this.geocoder)
				this.geocoder = new GClientGeocoder();
		
			this.geocoder.getLocations(str, this.__searchResultsLoad);
		}
	}
}

JCBXGoogleSearch.prototype.__pointClick = function()
{
	this.openInfoWindow(this.BXTEXT);
}

JCBXGoogleSearch.prototype.handleError = function(error)
{
	alert(this.jsMess.mess_error + (error ? ': ' + error.message : ''));
}

JCBXGoogleSearch.prototype.clearSearchResults = function()
{
	for (var i = 0; i < this.arSearchResults.length; i++)
	{
		this.map.removeOverlay(this.arSearchResults[i]);
		delete this.arSearchResults[i];
	}

	this.arSearchResults = [];
}
