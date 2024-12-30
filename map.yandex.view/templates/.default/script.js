if (!window.BX_YMapAddPlacemark)
{
	window.BX_YMapAddPlacemark = function(arPlacemark, map_id)
	{
		var map = GLOBAL_arMapObjects[map_id];
		if (null == map)
			return false;
		
		if(!arPlacemark.LAT || !arPlacemark.LON)
			return false;
		
		var obPlacemark = new YMaps.Placemark(new YMaps.GeoPoint(arPlacemark.LON, arPlacemark.LAT));
		
		if (null != arPlacemark.TEXT && arPlacemark.TEXT.length > 0)
		{
			obPlacemark.setBalloonContent(arPlacemark.TEXT.replace(/\n/g, '<br />'));

			var value_view = '';
			if (arPlacemark.TEXT.length > 0)
			{
				var rnpos = arPlacemark.TEXT.indexOf("\n");
				value_view = rnpos <= 0 ? arPlacemark.TEXT : arPlacemark.TEXT.substring(0, rnpos);
				//value_view = value_view.replace(/>/g, '&gt;');
				//value_view = value_view.replace(/</g, '&lt;');
			}
			
			obPlacemark.setIconContent(value_view);
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