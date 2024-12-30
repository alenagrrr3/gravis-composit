if (window.WaitOnKeyPress == null)
{
	function WaitOnKeyPress(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			CloseWaitWindow();
	}
}

if (window.ShowWaitWindow == null)
{
	function ShowWaitWindow()
	{
		CloseWaitWindow();
	
		var obWndSize = jsUtils.GetWindowSize();
	
		var div = document.body.appendChild(document.createElement("DIV"));
		div.id = "wait_window_div";
		div.innerHTML = phpVars.messLoading;
		div.className = "waitwindow";
		//div.style.left = obWndSize.scrollLeft + (obWndSize.innerWidth - div.offsetWidth) - (jsUtils.IsIE() ? 5 : 20) + "px";
		div.style.right = (5 - obWndSize.scrollLeft) + 'px';
		div.style.top = obWndSize.scrollTop + 5 + "px";
	
		if(jsUtils.IsIE())
		{
			var frame = document.createElement("IFRAME");
			frame.src = "javascript:''";
			frame.id = "wait_window_frame";
			frame.className = "waitwindow";
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.right = div.style.right;
			frame.style.top = div.style.top;
			document.body.appendChild(frame);
		}
		jsUtils.addEvent(document, "keypress", WaitOnKeyPress);
	}
}

if (window.CloseWaitWindow == null)
{
	function CloseWaitWindow()
	{
		jsUtils.removeEvent(document, "keypress", WaitOnKeyPress);
	
		var frame = document.getElementById("wait_window_frame");
		if(frame)
			frame.parentNode.removeChild(frame);
	
		var div = document.getElementById("wait_window_div");
		if(div)
			div.parentNode.removeChild(div);
	}
}

function pJCFloatDiv() 
{
	var _this = this;
	this.floatDiv = null;
	this.x = this.y = 0;

	this.Show = function(div, left, top, dxShadow, bSubstrate, bIframe)
	{
		var zIndex = parseInt(div.style.zIndex);
		if(zIndex <= 0 || isNaN(zIndex))
			zIndex = 100;
		div.style.zIndex = zIndex;
		div.style.left = left + "px";
		div.style.top = top + "px";

		if(jsUtils.IsIE() && bIframe != "N")
		{
			var frame = document.getElementById(div.id+"_frame");
			if(!frame)
			{
				frame = document.createElement("IFRAME");
				frame.src = "javascript:''";
				frame.id = div.id+"_frame";
				frame.style.position = 'absolute';
				frame.style.zIndex = zIndex-1;
				document.body.appendChild(frame);
			}
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
			frame.style.visibility = 'visible';
		}

		/*shadow*/
		if(isNaN(dxShadow))
			dxShadow = 5;
		if(dxShadow > 0)
		{
			var img = document.getElementById(div.id+'_shadow');
			if(!img)
			{
				if(jsUtils.IsIE())
				{
		 			img = document.createElement("DIV");
		 			img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='"+photoVars.templatePath+"images/shadow.png',sizingMethod='scale')";
				}
				else
				{
		 			img = document.createElement("IMG");
					img.src = photoVars.templatePath + 'images/shadow.png';
				}
				img.id = div.id+'_shadow';
				img.style.position = 'absolute';
				img.style.zIndex = zIndex-2;
				document.body.appendChild(img);
			}
			img.style.width = div.offsetWidth+'px';
			img.style.height = div.offsetHeight+'px';
			img.style.left = parseInt(div.style.left)+dxShadow+'px';
			img.style.top = parseInt(div.style.top)+dxShadow+'px';
			img.style.visibility = 'visible';
		}
		
		if (bSubstrate != "N")
		{
			var substrate = document.getElementById("photo_substrate");
			if(!substrate)
			{
				substrate = document.createElement("DIV");
				substrate.id = 	"photo_substrate";
				substrate.style.zIndex = zIndex-3;
				substrate.style.position = 	'absolute';
				substrate.style.display = 'none';
				substrate.style.visibility = 'hidden';
				substrate.style.background = '#052635';
				substrate.style.opacity = '0.5';
				if (substrate.style.MozOpacity)
					substrate.style.MozOpacity = '0.5';
				else if (substrate.style.KhtmlOpacity)
					substrate.style.KhtmlOpacity = '0.5';
				if (jsUtils.IsIE())
				{
			 		substrate.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=50)";
				}
				document.body.appendChild(substrate);
			}
			substrate.style.display = 'block';
			substrate.style.left = 0;
			substrate.style.top = 0;
			var WindowSize = jsUtils.GetWindowSize();
			substrate.style.width = WindowSize["scrollWidth"] + "px";
			substrate.style.height = WindowSize["scrollHeight"] + "px";
			substrate.style.visibility = 'visible';
		}
	}
		
	this.Close = function(div)
	{
		if(!div)
			return;
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
			sh.style.visibility = 'hidden';

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
			frame.style.visibility = 'hidden';
			
		var substrate = document.getElementById("photo_substrate");
		if(substrate)
		{
			substrate.style.display = 'none';
			substrate.style.visibility = 'hidden';
		}
	}
		
	this.Move = function(div, x, y, dxShadow)
	{
		if(!div)
			return;
			
		var left = parseInt(div.style.left)+x;
		var top = parseInt(div.style.top)+y;
		div.style.left = left+'px';
		div.style.top = top+'px';

		this.AdjustShadow(div, dxShadow);
	}
	
	this.AdjustShadow = function(div, dxShadow)
	{
		var sh = document.getElementById(div.id+"_shadow");
		if(sh)
		{
			if(isNaN(dxShadow))
				dxShadow = 5;

			sh.style.width = div.offsetWidth+'px';
			sh.style.height = div.offsetHeight+'px';
			sh.style.left = parseInt(div.style.left)+dxShadow+'px';
			sh.style.top = parseInt(div.style.top)+dxShadow+'px';
		}

		var frame = document.getElementById(div.id+"_frame");
		if(frame)
		{
			frame.style.width = div.offsetWidth + "px";
			frame.style.height = div.offsetHeight + "px";
			frame.style.left = div.style.left;
			frame.style.top = div.style.top;
		}
	}

	this.StartDrag = function(e, div)
	{
		if(!e)
			e = window.event;
		this.x = e.clientX + document.body.scrollLeft;
		this.y = e.clientY + document.body.scrollTop;
		this.floatDiv = div;

		jsUtils.addEvent(document, "mousemove", this.MoveDrag);
		document.onmouseup = this.StopDrag;
		if(document.body.setCapture)
			document.body.setCapture();
		
		var b = document.body;
	    b.ondrag = jsUtils.False;
	    b.onselectstart = jsUtils.False;
	    b.style.MozUserSelect = _this.floatDiv.style.MozUserSelect = 'none';
	    b.style.cursor = 'move';
    }

	this.StopDrag = function(e)
	{
		if(document.body.releaseCapture)
			document.body.releaseCapture();
		
		jsUtils.removeEvent(document, "mousemove", _this.MoveDrag);
		document.onmouseup = null;
		this.floatDiv = null;

		var b = document.body;
		b.ondrag = null;
		b.onselectstart = null;
		b.style.MozUserSelect = _this.floatDiv.style.MozUserSelect = '';
	    b.style.cursor = '';
	}

	this.MoveDrag = function(e)
	{
		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;
		if(_this.x == x && _this.y == y)
			return;
	
		_this.Move(_this.floatDiv, (x - _this.x), (y - _this.y));
		_this.x = x;
		_this.y = y;
	}
}
var pjsFloatDiv = new pJCFloatDiv();

/************************************************/

function PhotoPopupMenu()
{
	var _this = this;
	this.active = null;
	
	this.PopupShow = function(div, pos)
	{
		this.PopupHide();
		if(!div)
			return;
		if (typeof(pos) != "object")
			pos = {};
			
		this.active = div.id;
	    div.ondrag = jsUtils.False;
		
		jsUtils.addEvent(document, "keypress", _this.OnKeyPress);
		
		div.style.width = div.offsetWidth + 'px';
		div.style.visibility = 'visible';
		
		var res = jsUtils.GetWindowSize();
		pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - div.offsetHeight/2);
		pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - div.offsetWidth/2);
		pjsFloatDiv.Show(div, pos["left"], pos["top"]);

/*	    div.onselectstart = jsUtils.False;
	    div.style.MozUserSelect = 'none';
*/	}

	this.PopupHide = function()
	{
		var div = document.getElementById(_this.active);
		if(div)
		{
			pjsFloatDiv.Close(div);
			div.parentNode.removeChild(div);
		}

		this.active = null;
//		jsUtils.removeEvent(document, "click", _this.CheckClick);
		jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
	}

	this.CheckClick = function(e)
	{
		var div = document.getElementById(_this.active);
		
		if(!div)
		{
			return;
		}

		if (div.style.visibility != 'visible')
			return;
			
		if (!jsUtils.IsIE() && e.target.tagName == 'OPTION')
			return false;
			
		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		/*menu region*/
		var posLeft = parseInt(div.style.left);
		var posTop = parseInt(div.style.top);
		var posRight = posLeft + div.offsetWidth;
		var posBottom = posTop + div.offsetHeight;
		if(x >= posLeft && x <= posRight && y >= posTop && y <= posBottom)
			return;

		if(_this.controlDiv)
		{
			var pos = jsUtils.GetRealPos(_this.controlDiv);
			if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
				return;
		}
		_this.PopupHide();
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.PopupHide();
	},

	this.IsVisible = function()
	{
		return (document.getElementById(this.active).style.visibility != 'hidden');
	}
}

PhotoMenu = new PhotoPopupMenu();