if (!window.XMLHttpRequest)
{
	var XMLHttpRequest = function()
	{
		try { return new ActiveXObject("MSXML3.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP.3.0") } catch(e) {}
		try { return new ActiveXObject("MSXML2.XMLHTTP") } catch(e) {}
		try { return new ActiveXObject("Microsoft.XMLHTTP") } catch(e) {}
	}
}

var allGagdgetHolders = [];
function getGadgetHolder(id)
{
	return allGagdgetHolders[id];
}


function BXGadget(gadgetHolderID, allGadgets)
{
	var _this = this;

	_this.gadgetHolderID = gadgetHolderID;
	_this.allGadgets = allGadgets;

	allGagdgetHolders[_this.gadgetHolderID] = _this;

	_this.menuItems = [];
	for(var gr_id in arGDGroups)
	{
		var items = [];
		for(var _gid in arGDGroups[gr_id]['GADGETS'])
		{
			var gid = arGDGroups[gr_id]['GADGETS'][_gid];
			for(var i in _this.allGadgets)
			{
				if(_this.allGadgets[i]['ID'].toUpperCase() == gid.toUpperCase())
				{
					_this.allGadgets[i]['ONCLICK'] = "getGadgetHolder('"+_this.gadgetHolderID+"').Add('"+_this.allGadgets[i]['ID']+"')";
					items[items.length] = _this.allGadgets[i];
					break;
				}
			}
		}

		_this.menuItems[gr_id] =
		{
			'ID': gr_id,
			'TEXT':	'<div style="text-align: left;"><b>' + arGDGroups[gr_id]['NAME'] + '</b><br>' + arGDGroups[gr_id]['DESCRIPTION']+'</div>',
			'MENU': items
		};
	}

	// Recalc gadgets positions
	_this.gdList = Array();
	_this.gdCols = Array();
	_this.__GDList = function()
	{
		_this.gdList = Array();
		_this.gdCols = Array();
		var GDHolder = document.getElementById("GDHolder_"+_this.gadgetHolderID).rows[0].cells;
		var childElements, l, el, i;
		for(i=0; i < GDHolder.length; i++)
		{
			if(GDHolder[i].id.substring(0, 1) == 's')
			{
				l = Array();
				childElements = GDHolder[i].childNodes;
				for(el in childElements)
				{
					if(!childElements[el])
						continue;
					if(childElements[el].tagName && childElements[el].tagName.toUpperCase() == 'TABLE' && childElements[el].id.substring(0, 1) == 't')
					{
						l[l.length] = childElements[el];
					}
				}
				_this.gdList[_this.gdCols.length] = l;
				GDHolder[i].realPos =jsUtils.GetRealPos(GDHolder[i]);
				_this.gdCols[_this.gdCols.length] = GDHolder[i];
			}
		}
	}

	// Drag'n'drop start
	_this.gdDrag = false;
	_this.mousePos = {x: 0, y: 0};
	_this.zind = 0;

	_this.DragStart = function(n, e)
	{
		if(e)
		{
			if(e.srcElement && e.srcElement.tagName.toLowerCase() == 'a')
				return false;

			if(e.originalTarget && e.originalTarget.tagName.toLowerCase() == 'a')
				return false;
		}

		var antiselect = document.getElementById("antiselect");
		if(antiselect)
		{
			antiselect.style.display = 'block';

		 	var windowSize = jsUtils.GetWindowScrollSize();
			antiselect.style.width = windowSize.scrollWidth + "px";
			antiselect.style.height = windowSize.scrollHeight + "px";
			antiselect.style.opacity = 0.01;
			antiselect.style.filter = 'gray() alpha(opacity=01)';
		}

		_this.__GDList();
		var t = document.getElementById('t'+n);
		var tablePos = jsUtils.GetRealPos(t);
		var d = document.getElementById('d'+n);

		d.style.display = 'block';
		d.width = t.offsetWidth+'px';
		d.style.height = t.offsetHeight+'px';

		t.style.position = 'absolute';
		t.style.width = d.offsetWidth+'px';
		t.style.height = d.offsetHeight+'px';
		t.style.left = tablePos["left"]+20+'px';
		t.style.top = tablePos["top"]+'px';
		t.style.border = '1px solid #777777';
		_this.zind = t.style.zIndex;
		t.style.zIndex = '10000';

		t.style.MozOpacity = 0.60;
		t.style.opacity = 0.60;
		t.style.filter = 'gray() alpha(opacity=60)';

		_this.gdDrag = n;

		_this.mousePos.x = e.clientX + document.body.scrollLeft;
		_this.mousePos.y = e.clientY + document.body.scrollTop;
		return false;
	}

	// Drag'n'drop move
	_this.onMouseMove = function(e)
	{
		if(_this.gdDrag == false)
			return;

		var t = document.getElementById('t'+_this.gdDrag);

		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		t.style.left = parseInt(t.style.left) + x - _this.mousePos.x + 'px';
		t.style.top =  parseInt(t.style.top) + y - _this.mousePos.y + 'px';

		var rRealPos = jsUtils.GetRealPos(t), c, i, te, el = false, mm;
		var center = rRealPos.left + (rRealPos.right - rRealPos.left)/2, center2 = rRealPos.top + (rRealPos.bottom - rRealPos.top)/2;
		for(i=0; i<_this.gdCols.length; i++)
		{
			c = _this.gdCols[i].realPos;
			if(c.left <= center && c.right >= center)
			{
				//debugger;
				for(te in _this.gdList[i])
				{
					if(_this.gdList[i][te].id == t.id)
						mm = jsUtils.GetRealPos(document.getElementById('d'+_this.gdDrag));
					else
						mm = jsUtils.GetRealPos(_this.gdList[i][te])

					if(center2 < mm.bottom)
					{
						el = _this.gdList[i][te];
						break;
					}
				}

				if(!el)
					  el = 'last';

				break;
			}
		}

		if(el)
		{
			var d = document.getElementById('d'+_this.gdDrag);
			d.parentNode.removeChild(d);
			if(el=='last')
				_this.gdCols[i].appendChild(d);
			else
				el.parentNode.insertBefore(d, el);
		}

		_this.mousePos.x = x;
		_this.mousePos.y = y;
	}

	// Drag'n'drop end
	_this.onMouseUp = function(e)
	{
		if(_this.gdDrag == false)
			return;

		var antiselect = document.getElementById("antiselect");
		if(antiselect)
		{
			antiselect.style.display = 'none';
		}

		var t = document.getElementById('t'+_this.gdDrag);

		t.style.MozOpacity = 1;
		t.style.opacity = 1;
		t.style.filter = '';
		t.style.position = 'static';
		t.style.border = '0px';
		t.style.width = '';
		t.style.height = '';
		t.style.zIndex = _this.zind;

		var d = document.getElementById('d'+_this.gdDrag);
		d.style.display = 'none';

		t.parentNode.removeChild(t);
		d.parentNode.insertBefore(t, d);

		_this.gdDrag = false;

		if(!_this.sendWait)
		{
			_this.sendWait = true;
			setTimeout("getGadgetHolder('"+_this.gadgetHolderID+"').SendUpdatedInfo();", 1000);
		}
	}

	// Create gadgets position string
	_this.GetPosString = function()
	{
		var GDHolder = document.getElementById("GDHolder_"+_this.gadgetHolderID).rows[0].cells;
		var childElements, el, i;
		var result = '', column=-1, row=0;
		for(i=0; i < GDHolder.length; i++)
		{
			if(GDHolder[i].id.substring(0, 1) == 's')
			{
				column++;
				row=0;
				childElements = GDHolder[i].childNodes;
				for(el in childElements)
				{
					if(!childElements[el])
						continue;
					if(childElements[el].tagName && childElements[el].tagName.toUpperCase() == 'TABLE' && childElements[el].id.substring(0, 1) == 't')
					{
						result = result+'&POS['+column+']['+row+']='+encodeURIComponent(childElements[el].id.substring(1)) + (childElements[el].className.indexOf(" gdhided")>0?"*H":"");
						row++;
					}
				}
			}
		}

		return result;
	}


	//////////////
	///
	//////////////
	_this.gdXmlHttpUpdate = new XMLHttpRequest();
	_this.sendWait = false;
	_this.SendUpdatedInfo = function(param)
	{
		param = param || "update_position";

		if (_this.gdXmlHttpUpdate.readyState % 4)
		{
			setTimeout("getGadgetHolder('"+_this.gadgetHolderID+"').SendUpdatedInfo('"+param+"');", 500);
			return;
		}

		_this.sendWait = false;

		_this.gdXmlHttpUpdate.open("POST", updateURL, true);
		_this.gdXmlHttpUpdate.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		_this.gdXmlHttpUpdate.onreadystatechange = function()
		{
			if(_this.gdXmlHttpUpdate.readyState == 4)
			{
				if(_this.gdXmlHttpUpdate.status == 200)
				{
					if(jsUtils.trim(_this.gdXmlHttpUpdate.responseText).length > 0)
					{

					}
					if(param == 'clear_settings')
						window.location = window.location;
				}
				else
				{
					alert(langGDError1);
				}
			}
		}

		_this.gdXmlHttpUpdate.send("gd_ajax="+_this.gadgetHolderID+"&gd_ajax_action=" + param + _this.GetPosString());
	}


	_this.ShowAddGDMenu = function(a)
	{
		_this.menu = new PopupMenu('gadgets_float_menu');
		_this.menu.Create(110);

		if(_this.menu.IsVisible())
			return;

		_this.menu.SetItems(_this.menuItems);
		_this.menu.BuildItems();

		var pos = jsUtils.GetRealPos(a);
		pos["bottom"]+=1;

		_this.menu.PopupShow(pos);
	}

	_this.Add = function(id)
	{
		var frm = document.getElementById("GDHolderForm_" + _this.gadgetHolderID);
		frm["gid"].value = id;
		frm["action"].value = "add";
		frm.submit();
	}

	_this.UpdSettings = function(id)
	{
		var frm = document.getElementById("GDHolderForm_" + _this.gadgetHolderID);
		frm["gid"].value = id;
		frm["action"].value = "update";

		function __AddField(elmName, elmValue)
		{
			var elm = document.createElement("INPUT");
			elm.type = "hidden";
			elm.name = "settings["+elmName+"]";
			elm.value = elmValue;
			frm.appendChild(elm);
		}


		var dSet = document.getElementById("dset"+id);
		var el, res = '';
		for(var i=0; i<dSet._inputs.length; i++)
		{
			el = document.getElementById(id + '_' + dSet._inputs[i]);
			if(el)
			{
				if(el.tagName.toUpperCase()=='INPUT' && el.type.toUpperCase()=='CHECKBOX')
					__AddField(dSet._inputs[i], (el.checked ? 'Y' : 'N'));
				else
					__AddField(dSet._inputs[i] , el.value);
			}
		}

		frm.submit();
	}

	_this.SetForAll = function()
	{
		if(!confirm(langGDConfirm1))
			return;

		_this.SendUpdatedInfo('save_default');
	}

	_this.ClearUserSettings = function()
	{
		_this.SendUpdatedInfo('clear_settings');
	}

	_this.Delete = function(id)
	{
		var t = document.getElementById('t'+id);
		if(t)
			t.parentNode.removeChild(t);

		var d = document.getElementById('d'+id);
		if(d)
			d.parentNode.removeChild(d);

		if(!_this.sendWait)
		{
			_this.sendWait = true;
			setTimeout("getGadgetHolder('"+_this.gadgetHolderID+"').SendUpdatedInfo();", 500);
		}

		return false;
	}

	_this.Hide = function(id, ob)
	{
		var t = document.getElementById('t'+id);
		if(!t)
			return;

		if(t.className.indexOf(" gdhided")>0)
			t.className = 'data-table-gadget';
		else
			t.className = 'data-table-gadget gdhided';

		if(!_this.sendWait)
		{
			_this.sendWait = true;
			setTimeout("getGadgetHolder('"+_this.gadgetHolderID+"').SendUpdatedInfo();", 500);
		}

		return false;
	}

	_this.gdXmlHttpSett = new XMLHttpRequest();
	_this.ShowSettings = function(id, t)
	{
		var dS = document.getElementById("dset"+id);
		if(dS.style.display != 'none')
		{
			dS.style.display = 'none';
			return;
		}

		if(_this.gdXmlHttpSett.readyState % 4)
			return;

		t = t || 'get_settings';

		_this.gdXmlHttpSett.open("POST", updateURL, true);
		_this.gdXmlHttpSett.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
		_this.gdXmlHttpSett.onreadystatechange = function()
		{
			if(_this.gdXmlHttpSett.readyState == 4)
			{
				if(_this.gdXmlHttpSett.status == 200)
				{
					if(jsUtils.trim(_this.gdXmlHttpSett.responseText).length > 0)
					{
						var before = new Date().getTime();
						var dSet = document.getElementById("dset"+id);
						dSet.innerHTML = '';
						dSet._inputs = [];

						try
						{
							eval('var gdObject = '+_this.gdXmlHttpSett.responseText);
						}
						catch (e)
						{
							alert(_this.gdXmlHttpSett.responseText);
							return;
						}

						var param, param_id;
						var oEl;
						for(param_id in gdObject)
						{
							param = gdObject[param_id];
							var str = '';
							var input_id = id + '_' + param_id;

							param["TYPE"] = param["TYPE"] || 'STRING';

							if(!param["VALUE"] && param["DEFAULT"]!='undefined')
								param["VALUE"] = param["DEFAULT"];

				            if(param["TYPE"]=="STRING")
							{
								str = param["NAME"] + ':<br><input type="text" id="' + input_id + '" size="40" value="'+param["VALUE"]+'"><br>';
							}
							else if(param["TYPE"]=="LIST")
							{
								var aR = [];
								for(var vid in param["VALUES"])
								{
									aR.push('<option value="'+vid+'" '+(param["VALUE"]==vid?' selected':'')+'>'+param["VALUES"][vid]+'</option>');
									//str = str + '<option value="'+vid+'" '+(param["VALUE"]==vid?' selected':'')+'>'+param["VALUES"][vid]+'</option>';
								}
								str = param["NAME"] + ':<br><select style="width:100%" id="' + input_id + '" >' + aR.join("") + '</select>';

							}
							else if(param["TYPE"]=="CHECKBOX")
							{
								str = param["NAME"]+': <input type="checkbox" id="' + input_id + '" value="Y" '+(param["VALUE"]=='Y'?' checked':'')+'><br>';
							}


							oEl = document.createElement("DIV");
							oEl.className = "gdsettrow";
							oEl.innerHTML = str;
							dSet.appendChild(oEl);
							dSet._inputs[dSet._inputs.length] = param_id;
						}

						oEl = document.createElement("DIV");
						oEl.className = "gdsettrow";

						oEl.innerHTML = '<input type="button" value="OK" onclick="getGadgetHolder(\''+_this.gadgetHolderID+'\').UpdSettings(\''+id+'\');"> <input type="button" value="'+langGDCancel+'" onclick="getGadgetHolder(\''+_this.gadgetHolderID+'\').CloseSettingsForm(\''+id+'\');">';
						dSet.appendChild(oEl);

						dSet.style.display = 'block';
					}
				}
				else
				{
					alert(langGDError2);
				}
			}
		}

		_this.gdXmlHttpSett.send("gd_ajax="+_this.gadgetHolderID+"&gid="+id+"&gd_ajax_action="+t);

		return false;
	}

	_this.CloseSettingsForm = function(id)
	{
		var dSet = document.getElementById("dset"+id);
		dSet.style.display = 'none';
	}

	jsUtils.addEvent(document.body, "mousemove", _this.onMouseMove);
	jsUtils.addEvent(document.body, "mouseup", _this.onMouseUp);
}
