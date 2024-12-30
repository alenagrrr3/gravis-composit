if (!window.BX_GMapAddPlacemark)
{
	window.BX_GMapAddPlacemark = function(arPlacemark, map_id)
	{
		var map = GLOBAL_arMapObjects[map_id];
		
		if (null == map)
			return false;

		if(!arPlacemark.LAT || !arPlacemark.LON)
			return false;
		
		var obPlacemark = new GMarker(new GPoint(arPlacemark.LON, arPlacemark.LAT));
		
		if (null != arPlacemark.TEXT && arPlacemark.TEXT.length > 0)
		{
			obPlacemark.BXTEXT = arPlacemark.TEXT.replace(/\n/g, '<br />');
			GEvent.addListener(obPlacemark, "click", function() 
			{
				obPlacemark.openInfoWindowHtml(obPlacemark.BXTEXT);
				//map.openInfoWindowHtml(obPlacemark.getLatLng(), obPlacemark.BXTEXT);
			});
		}

		map.addOverlay(obPlacemark);
		
		return obPlacemark;
	}
}

if (null == window.BXWaitForMap_view)
{
	function BXWaitForMap_view(map_id)
	{
		if (null == window.GLOBAL_arMapObjects)
			return;
	
		if (window.GLOBAL_arMapObjects[map_id])
			window['BX_SetPlacemarks_' + map_id]();
		else
			setTimeout('BXWaitForMap_view(\'' + map_id + '\')', 300);
	}
}