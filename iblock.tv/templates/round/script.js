if(!jsPublicTVCollector)
	var jsPublicTVCollector = {tv: [], list: [], add: []};

function jsPublicTV()
{
	this.DescriptionBlockID = null;
	this.DescriptionValues = 
	{
		title: true,
		description: true,
		duration: true,
		file: true
	};
	
	this.Sections = [];
	
	this.Prefix = '';
	
	this.LanguagePhrases = {};
	
	this.TreeBlockID = null;
	this.TreeNodes = null;	

	this.TreeStyleCodes = {};
	this.TreeRoll = 
	{
			id:'-roll', 
			styles:['bitrix-tv-section-closed','bitrix-tv-section-title']
	};
	
	this.ShowPreviewImage = true;
	this.ShowPreviewImageSize = [64, 48];

	this.Player = {wmv: null, flv:null};
	this.PlayerConfig = {};
	this.PlayOrder = false;
	
	this.CurrentItem = false;
	
	this.ArListeners = [];
	
	//effect variables
	this.EffectOverlay = null;
	this.EffectTvBlock = null;
	this.EffectTvBlockPadding = [15,15];
	
	//init sign
	this.Inited = null;
	
	//delay functions
	this.MaxWaitTime = 1000;
}

/* Work With Player */
jsPublicTV.prototype.Init = function(arValues, TBID, DBID, arStart)
{	
	this.PlayerListeners('BEFORE_INIT'); //handler to set own element in Init
	
	this.TreeStyleCodes = 
	{
		description_start:'<div class="player_description_text">',
		description_end:'</div>',
		tree_image_start:'<div class="bitrix-tv-small-image">',
		tree_image_end:'</div>',
		tree_description_start:'<div class="bitrix-tv-tree-item-description">',
		tree_description_end:'</div>',
		description_file_start:'<a target="_blank" href="',
		description_file_end:'">' + this.LanguagePhrases.download + '</a>'
	};
	
	this.Sections = arValues;
	this.TreeBlockID = document.getElementById(TBID);
	this.DescriptionBlockID = document.getElementById(DBID);
	
	//prepare player params
	if(this.CurrentItem===false)
	{
		var nextItem = this.GetNextItem();
		if(nextItem)
			this.CurrentItem = {Section:nextItem.Section, Item:nextItem.Item};
	}
	
	if(this.CurrentItem!==false)
	{	
		this.PlayerConfig.file = this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].File;
		this.PlayerConfig.image = this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].BigImage;
		this.PlayerConfig.link = this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].File;
		this.PlayerConfig.autostart = 'false';
		this.PlayerConfig.width = (arStart.width>0)?arStart.width:640; 
		this.PlayerConfig.height = (arStart.height>0)?arStart.height:480;
		this.PlayerConfig.block_id = {wmv:arStart.block_id.wmv, flv:arStart.block_id.flv};
		this.PlayerConfig.logo = arStart.logo;
		
		//create player
		this.GeneratePlayer();
		
		//set description
		this.SetDescription(this.CurrentItem.Section, this.CurrentItem.Item);
		
		//set inited
		this.Inited = true;
	}
}

jsPublicTV.prototype.GeneratePlayer = function()
{
	if(this.PlayerConfig.file && this.PlayerConfig.width && this.PlayerConfig.height && this.PlayerConfig.block_id[this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].Type])
	{
		if(this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].Type == 'wmv') //WMV
		{
			this.PlayerConfig.height -=4; //height fix
			this.Player.wmv = new jeroenwijering.Player(document.getElementById(this.PlayerConfig.block_id.wmv), '/bitrix/components/bitrix/player/wmvplayer/wmvplayer.xaml',  this.PlayerConfig);
			this.PlayerConfig.height +=4; //height fix
			
			var _this = this;
			//state listener
			this.RunDelayFunction(function(){_this.SetListener('STATE', function(oldstate, newstate){_this.StateListener(oldstate, newstate);}, 'wmv')}, 50, 0);
		}
		else if(this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].Type == 'flv') //FLW
		{
			var block = document.getElementById(this.PlayerConfig.block_id.flv);
			if(block && block.tagName!='OBJECT') //inner object
			{
				if(!jsUtils.IsIE() && !jsUtils.IsSafari()) //for FF, Opera
					var obj = block.getElementsByTagName('EMBED');
				else
					var obj = block.getElementsByTagName('OBJECT');
					
				if(obj.length>0)
					this.Player.flv = obj[0];
			}
			else if(block && block.tagName=='OBJECT') //this object
			{
				if(!jsUtils.IsIE() && !jsUtils.IsOpera() && !jsUtils.IsSafari()) //for FF
				{
					var obj = block.getElementsByTagName('EMBED');
					if(obj.length>0)
						this.Player.flv = obj[0];
				}
				else
					this.Player.flv = block;
			}
			
			if(this.Player.flv)
			{
				//set listeners
				var _this = this;
				//state listener
				
				var SWFSL= "SWFStatListener"+this.Prefix;
				this.RunDelayFunction(function(){_this.SetListener('STATE', SWFSL, 'flv');}, 50, 0);
				
				if(jsUtils.IsIE())//load file for IE
					this.RunDelayFunction(function(){_this.Player.flv.sendEvent("LOAD", _this.PlayerConfig);}, 50, 0);
			}//SWFStatListener important function for SWF STATE listener
		}
	}
}

jsPublicTV.prototype.RunDelayFunction = function(func, repeatTime, CurrentTime, delay)
{
	if(CurrentTime>=this.MaxWaitTime)
		return;
		
	if(0<delay) //delay func
	{
		var _this = this;
		setTimeout(function(){_this.RunDelayFunction(func, repeatTime, CurrentTime);}, delay);
		return;		
	}
		
	try
	{	
		func();
	}
	catch(e)
	{
		var _this = this;
		setTimeout(function(){_this.RunDelayFunction(func, repeatTime, (CurrentTime+repeatTime));}, repeatTime);
	}
}

/* Work With Item List */
jsPublicTV.prototype.BuildTree = function(kill)
{
	if(kill)
		this.KillTree();
	//build
	for(i=0; i<this.Sections.length; i++)
		this.BuildBlock(i);
}

jsPublicTV.prototype.KillTree = function()
{
	this.TreeNodes = null;
	this.CurrentItem = false;
	for(i=0, n=this.TreeBlockID.childNodes.length; i<n; i++)
		this.TreeBlockID.removeChild(this.TreeBlockID.childNodes[0]);
}

jsPublicTV.prototype.BuildBlock = function(i)
{
	var CurLevel = this['Sections'][i]['Depth'] ?this['Sections'][i]['Depth'] :0;
	var _this = this;
	
	//get parent
	if(CurLevel==1 || CurLevel==0)
		var ParentBlock = this.TreeBlockID;
	else if(this['Sections'][i]['Depth'] == this['Sections'][i-1]['Depth'])
		var ParentBlock = document.getElementById(this.Prefix + 'bx-tv-section-' + (i-1)).parentNode;
	else
		var ParentBlock = document.getElementById(this.Prefix + 'bx-tv-section-' + (i-1));

	//build block
	if(CurLevel>0)
	{
		//title
		var block = ParentBlock.appendChild(document.createElement('DIV'));
		block.innerHTML = '<div style="clear:both"></div>' + this['Sections'][i]['Name'];
		block.onclick = function(){_this.TreeExpand(i)};
		block.className = 'bitrix-tv-section-title';
		//block
		var block = ParentBlock.appendChild(document.createElement('DIV'));
		block.id = this.Prefix + 'bx-tv-section-' + i;
		//set style
		block.className = 'bitrix-tv-section-closed';
	}

	//build items
	if(this['Sections'][i]['Items'].length >0)
	{
		for(j=0;j<this['Sections'][i]['Items'].length;j++)
				this.BuildItem(i,j);
	}
}

jsPublicTV.prototype.BuildItem = function(i,j)
{
	var _this = this;
	var ParentBlock = (this['Sections'][i]['Depth']==0) ? this.TreeBlockID : document.getElementById(this.Prefix + 'bx-tv-section-' + i);

	if(ParentBlock)
	{
		var item = ParentBlock.appendChild(document.createElement('DIV'));
		item.id = this.Prefix + 'bx-tv-s' + i + 'i' + j;
		item.className = 'bitrix-tv-tree-item';
		var txt = 
			this.TreeStyleCodes.tree_description_start
			+ '<a>' + this['Sections'][i]['Items'][j]['Name'] + '</a><br>'
			+ (this.LanguagePhrases['duration'] ? this.LanguagePhrases['duration'] : '') + this['Sections'][i]['Items'][j]['Duration']
			+ this.TreeStyleCodes.tree_description_end;
			
		if(this['Sections'][i]['Items'][j]['SmallImage']!='' && this.ShowPreviewImage)
			txt = this.TreeStyleCodes.tree_image_start + '<img width="' + this.ShowPreviewImageSize[0] + 'px" height="' + this.ShowPreviewImageSize[1] + 'px" src="' + this['Sections'][i]['Items'][j]['SmallImage'] + '">' + this.TreeStyleCodes.tree_image_end + txt;
		
		var listen = this.PlayerListeners('BUILD_ITEM', [txt, i, j]);
		if(typeof listen != "undefined" && listen!='')
		{
			item.innerHTML = listen;
			return;
		}
			
		item.innerHTML = txt;
		item.onclick = function(){_this.SetDescription(i,j); _this.PlayFile(i,j,true);};
	}
}

jsPublicTV.prototype.TreeExpand = function(i)
{
	var block = document.getElementById(this.Prefix + 'bx-tv-section-' + (i));
	if(block)
	{
		if(block.style.display == 'block')
			block.style.display = 'none';
		else
			block.style.display = 'block';
	}
}

jsPublicTV.prototype.TreeExpandUp = function(i)
{
		var block = document.getElementById(this.Prefix + 'bx-tv-section-' + (i));
		if(block)
		{
			var i=0; //max_depth 25
			while(this.TreeBlockID.id != block.id)
			{
				block.style.display = 'block';
				block = block.parentNode;
				if(i++>25)
					break;
			}
		}
}

/* Play File From List */
jsPublicTV.prototype.PlayFile = function(i, j, autoplay, handle)
{
	if("undefined" == typeof(this['Sections'][i]['Items'][j]))
		return;
		
	//prepare params
	var params = 
	{
		file: this['Sections'][i]['Items'][j]['File'],
		image: this['Sections'][i]['Items'][j]['BigImage'],
		link: this['Sections'][i]['Items'][j]['File'],
		width: this['Sections'][i]['Items'][j]['Width'],
		height: this['Sections'][i]['Items'][j]['Height'],
		type: this['Sections'][i]['Items'][j]['Type']
	};
	
	//old values
	var old_i = this.CurrentItem.Section;
	var old_j = this.CurrentItem.Item;
	
	//set current item
	this.CurrentItem = {Section:i, Item:j};
	
	if(old_i==i && old_j==j && handle!==true) // dublicate call
		return;
	
	//event
	var listen = this.PlayerListeners('BEFORE_PLAY_FILE', [i, j, old_i, old_j]);
	if(typeof listen != "undefined" && listen!='')
		return;
		
	//expand menu
	this.TreeExpandUp(i);
	
	var wmv = document.getElementById(this.PlayerConfig.block_id.wmv);
	var flv = document.getElementById(this.PlayerConfig.block_id.flv);
	
	if(params.type == 'wmv')
	{
		if(this.Player.flv && flv.style.display!='none') //if current not wmv, stop flv
		{
			this.Player.flv.sendEvent("STOP");
		}
		
		if(!this.Player.wmv) //generate WMV if not exists
			this.GeneratePlayer(); //Opera want to regenerate? O.o

		if(this.Player.wmv)
		{
			var _player  = this.Player.wmv;
			var _this = this;
			_player.configuration.image = params.image; // \\only once???<<<
			_player.configuration.link = params.link; //modify link
			
			if(wmv.style.display == 'none') //wmv hidden
			{	
				if(flv)
					flv.style.display = 'none';
				if(wmv)
					wmv.style.display = 'block';	
			}

			if(autoplay == true)
				this.RunDelayFunction(function(){_player.sendEvent('LOAD', params.file); _this.RunDelayFunction(function(){_player.sendEvent('PLAY');}, 50, 0, 50);}, 50, 0, jsUtils.IsSafari() ? 100 : 0); //Safari +100
			else
				this.RunDelayFunction(function(){_player.sendEvent('LOAD', params.file);}, 50, 0);
		}
	}
	else if (params.type == 'flv')
	{
		if(this.Player.wmv && wmv.style.display!='none') //if current not flv, stop wmv
		{
			var _thiswmv = this.Player.wmv;
			this.RunDelayFunction(function(){_thiswmv.sendEvent("STOP");}, 50, 0);
		}
		
		if(!this.Player.flv) //generate FLV if not exists
			this.GeneratePlayer();

		if(this.Player.flv && getFlashVersion()>=9) //flash 9+ version
		{
			var _this = this;
			var _player  = this.Player.flv;
			
			if(flv.style.display == 'none') //flv hidden
			{
				if(wmv)
					wmv.style.display = 'none';
				if(flv)
					flv.style.display = 'block';

				var SWFSL= "SWFStatListener"+this.Prefix;
				//state listener, repeat for regeneration
				this.RunDelayFunction(function(){_this.SetListener('STATE', SWFSL, 'flv');}, 50, 0);
			}
			
			var flvparams = {file:params.file, link: params.file}
			
			if(autoplay == true)
				this.RunDelayFunction(function(){_player.sendEvent("LOAD", flvparams); _this.RunDelayFunction(function(){_player.sendEvent("PLAY");}, 50, 0, 50);}, 50, 0, jsUtils.IsSafari() ? 100 : 0); //Safari +100
			else
				this.RunDelayFunction(function(){_player.sendEvent("LOAD", flvparams);}, 50, 0);
		}
	}
}

/* Play Order */
jsPublicTV.prototype.SetPlayOrder = function(order)
{
	this.PlayOrder = order;
}

/* Set Description */
jsPublicTV.prototype.SetDescription = function(i,j)
{
	if(this.DescriptionBlockID)
	{
		var txt = this.TreeStyleCodes.description_start;
		
		if(this.DescriptionValues.title===true)
			txt += (this.LanguagePhrases['title'] ? this.LanguagePhrases['title'] : '') + this['Sections'][i]['Items'][j]['Name'] + "<br>\n";
		if(this.DescriptionValues.description===true)	
			txt += (this.LanguagePhrases['description'] ? this.LanguagePhrases['description'] : '') + this['Sections'][i]['Items'][j]['Description'] + "<br>\n";
		if(this.DescriptionValues.duration===true)		
			txt += (this.LanguagePhrases['duration'] ? this.LanguagePhrases['duration'] : '') + this['Sections'][i]['Items'][j]['Duration'] + "<br>\n";
		if(this.DescriptionValues.file===true)	
			txt += (this.LanguagePhrases['file'] ? this.LanguagePhrases['file'] : '') + this.TreeStyleCodes.description_file_start + this['Sections'][i]['Items'][j]['File'] + this.TreeStyleCodes.description_file_end + "<br>\n";
			
		txt += this.TreeStyleCodes.description_end;
		
		var listen = this.PlayerListeners('SET_DESCRIPTION', [txt, i, j]);
		if(typeof listen != "undefined" && listen!='')
			txt = listen;
	
		this.DescriptionBlockID.innerHTML = txt;
	}
}

/* Roll List */
jsPublicTV.prototype.Roll = function()
{
	if(!this.TreeNodes)
		this.TreeNodes = this.TreeBlockID.getElementsByTagName('DIV');
		
	for(i=0; i<this.TreeNodes.length; i++)
	{
		for(j=0; j<this.TreeRoll.styles.length; j++)
		{
			if (this.TreeNodes[i].className.indexOf(this.TreeRoll.styles[j] + this.TreeRoll.id)!=-1)
			{
				this.TreeNodes[i].className = this.TreeNodes[i].className.replace(this.TreeRoll.styles[j] + this.TreeRoll.id, this.TreeRoll.styles[j]);
				break;
			}
			else if (this.TreeNodes[i].className.indexOf(this.TreeRoll.styles[j])!=-1)
			{
				this.TreeNodes[i].className = this.TreeNodes[i].className.replace(this.TreeRoll.styles[j], this.TreeRoll.styles[j] + this.TreeRoll.id);
				break;
			}
		}
	}
}

/* Walk By Items */
jsPublicTV.prototype.GetNextItem = function(in_section)
{
	if(!this.CurrentItem) //GetFirst
	{
		for(i=0; i<this.Sections.length; i++)
		{ 
			if(this['Sections'][i]['Items'].length > 0)
				return {Section:i, Item:0};
		}
	}
	else //GetNext
	{
		if(this['Sections'][this.CurrentItem.Section]['Items'].length > this.CurrentItem.Item + 1) //inside section
			return {Section:this.CurrentItem.Section, Item:this.CurrentItem.Item+1};
		else if(true!=in_section) //at all
		{
			for(i = this.CurrentItem.Section + 1; i < this.Sections.length; i++)
			{
				if(this['Sections'][i]['Items'].length>0)
					return {Section:i, Item:0};			
			}
		}
	}
	
	this.PlayerListeners('END_PLAY_LIST');
	return false;
}

jsPublicTV.prototype.PlayNextItem = function()
{
	var nextItem = this.GetNextItem();
	if(nextItem===false)
		return;

	if(this.CurrentItem!==false)
	{
		this.PlayFile(nextItem.Section, nextItem.Item, true);
		this.SetDescription(nextItem.Section, nextItem.Item);
	}
}

jsPublicTV.prototype.SeekByRealParams = function(section_id, element_id)
{
	if(typeof(section_id) != 'undefined' && typeof(element_id) != 'undefined' && false != section_id) // look by section&element id
	{
		for(i=0, n=this.Sections.length; i<n; i++)
		{
			if(section_id == this.Sections[i].Id)
			{
				for (ii=0, nn=this.Sections[i]['Items'].length; ii<nn; ii++)
				{
					if(element_id == this.Sections[i]['Items'][ii].Id)
						return {section:i, element:ii};
				}
			}
		}
	}
	else if(typeof(section_id) != 'undefined' && false != section_id) //look by section -> return only section
	{
		for(i=0, n=this.Sections.length; i<n; i++)
		{
			if(section_id == this.Sections[i].Id)
				return {section:i, element:false};
		}
	}
	else if(typeof(element_id) != 'undefined') //look by elementID in blind
	{
		for(i=0, n=this.Sections.length; i<n; i++)
		{
			for (ii=0, nn=this.Sections[i]['Items'].length; ii<nn; ii++)
			{
				if(element_id == this.Sections[i]['Items'][ii].Id)
					return {section:i, element:ii};
			}
		}		
	}
	
	return {section:false, element:false};
}

jsPublicTV.prototype.TreeMerge = function(arrToMerge)
{
	if(arrToMerge.length<=0)
		return false;
}

/* Listeners */
jsPublicTV.prototype.SetListener = function(type, func, playertype)
{
	if('wmv' == playertype)
	{
		if(this.Player[playertype])
			this.Player[playertype].addListener(type, func);
	}
	else if('flv' == playertype)
	{
		if(this.Player[playertype])
			this.Player[playertype].addModelListener(type, func);
	}
}

jsPublicTV.prototype.StateListener = function(oldstate, newstate)
{
	if('Completed'==newstate)
	{
		if(this.PlayOrder!==false)
		{
			if(this.PlayOrder == 'section' || this.PlayOrder=='all')
				this.PlayNextItem();
		}
	}
}

/* Own Listenerss */
//END_PLAY_LIST
//BEFORE_INIT
//SET_DESCRIPTION, in\out text of description
//BUILD_ITEM
//BEFORE_PLAY_FILE
jsPublicTV.prototype.PlayerListeners = function(type, args)
{
	for(var i=0; i<this.ArListeners.length; i++) 
	{
		if(this.ArListeners[i]['type'].toUpperCase() == type) 
			return this.ArListeners[i]['func'].apply(null, args);
	}
}

jsPublicTV.prototype.AddPlayerListener = function(type, func)
{
	if(typeof type != "undefined" && typeof func != "undefined")
		this.ArListeners.push({type:type,func:func});
}

/*Effects*/
jsPublicTV.prototype.EffectCreateOverlay = function()
{
	var windowSize = this.EffectGetWindowScrollSize();
	var posSize = this.EffectGetWindowScrollPos();
	
	if(this.EffectTvBlock)
	{
		this.EffectTvBlock.style.display = "block";
		this.EffectTvBlock.style.top = this.EffectTvBlockPadding[1] + posSize.scrollTop + 'px';
		this.EffectTvBlock.style.left = this.EffectTvBlockPadding[0] + 'px';
	}
		
	if (!this.EffectOverlay && this.EffectGetOpacityProperty())
	{
		var doc = this.EffectTvBlock ? this.EffectTvBlock.parentNode : document.body;
		this.EffectOverlay = doc.appendChild(document.createElement("DIV"));
		this.EffectOverlay.className = "bitrix-tv-overlay";
		this.EffectOverlay.id = 'bitrix-tv-overlay';
				
		this.EffectOverlay.style.width = windowSize.scrollWidth + "px";
		this.EffectOverlay.style.height = windowSize.scrollHeight + "px";
		this.EffectOverlay.style.display = "block";
	}
	
	if(this.Inited==true && !jsUtils.IsIE() && this['Sections'][this.CurrentItem.Section]['Items'][this.CurrentItem.Item].Type == 'flv') //for FF,Safari
	{
		var _this = this;
		this.RunDelayFunction(function(){_this.Player.flv.sendEvent("LOAD", _this['Sections'][_this.CurrentItem.Section]['Items'][_this.CurrentItem.Item].File);}, 50, 0, 50 + (jsUtils.IsSafari() ? 100 : 0)); //FF +50, Safari +150
	}
	
	//prepare close esc
	var _this = this;	
	jsUtils.addEvent(document, 'keydown', function(e){_this.escEffectRemoveOverlay(e);});
}

jsPublicTV.prototype.escEffectRemoveOverlay = function(e)
{
	if (!e) e = window.event
	if (!e) return;
	if (e.keyCode == 27)
	{
		this.EffectRemoveOverlay();
		var _this = this;
		jsUtils.removeEvent(document, 'keydown', _this.escEffectRemoveOverlay);
	}	
}

jsPublicTV.prototype.EffectRemoveOverlay = function()
{
	if(this.Player.wmv && document.getElementById(this.PlayerConfig.block_id.wmv).style.display!='none')
		this.Player.wmv.sendEvent('STOP');
	
	if(this.EffectTvBlock)
		this.EffectTvBlock.style.display = "none";

	if(this.EffectOverlay)
	{
		this.EffectOverlay.parentNode.removeChild(this.EffectOverlay);
		this.EffectOverlay = null;
	}
}

jsPublicTV.prototype.EffectGetWindowScrollSize = function(pDoc)
{
	var width, height;
	if (!pDoc)
		pDoc = document;
	
	if ( (pDoc.compatMode && pDoc.compatMode == "CSS1Compat"))
	{
		width = pDoc.documentElement.scrollWidth;
		height = pDoc.documentElement.scrollHeight;
	}
	else
	{
		if (pDoc.body.scrollHeight > pDoc.body.offsetHeight)
			height = pDoc.body.scrollHeight;
		else
			height = pDoc.body.offsetHeight;
	
		if (pDoc.body.scrollWidth > pDoc.body.offsetWidth || 
			(pDoc.compatMode && pDoc.compatMode == "BackCompat") ||
			(pDoc.documentElement && !pDoc.documentElement.clientWidth)
		)
			width = pDoc.body.scrollWidth;
		else
			width = pDoc.body.offsetWidth;
	}
	return {scrollWidth : width, scrollHeight : height};
}

jsPublicTV.prototype.EffectGetWindowScrollPos = function(pDoc)
{
	var left, top;
	if (!pDoc)
		pDoc = document;
			
	if (self.pageYOffset)
	{
		left = self.pageXOffset;
		top = self.pageYOffset;
	}
	else if (pDoc.documentElement && pDoc.documentElement.scrollTop)
	{
		left = document.documentElement.scrollLeft;
		top = document.documentElement.scrollTop;
	}
	else if (pDoc.body)
	{
		left = pDoc.body.scrollLeft;
		top = pDoc.body.scrollTop;
	}
	return {scrollLeft : left, scrollTop : top};
}

jsPublicTV.prototype.EffectGetOpacityProperty = function()
{
	if (typeof document.body.style.opacity == 'string')
		return 'opacity';
	else if (typeof document.body.style.MozOpacity == 'string')
		return 'MozOpacity';
	else if (typeof document.body.style.KhtmlOpacity == 'string')
		return 'KhtmlOpacity';
	else if (document.body.filters && navigator.appVersion.match(/MSIE ([\d.]+);/)[1]>=5.5)
		return 'filter';
	
	return false;
}

/*float class*/
//add if not exists
if(!window.JCFloatDiv)
{
	JCFloatDiv = function ()
	{
		var _this = this;
		this.floatDiv = null;
		this.x = this.y = 0;
	
		this.Show = function(div, left, top, dxShadow, restrictDrag)
		{
			var zIndex = parseInt(div.style.zIndex);
			if(zIndex <= 0 || isNaN(zIndex))
				zIndex = 100;
	
			div.style.zIndex = zIndex;
	
			if (left < 0)
				left = 0;
	
			if (top < 0)
				top = 0;
	
			div.style.left = left + "px";
			div.style.top = top + "px";
	
			if(jsUtils.IsIE())
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
	
			/*Restrict drag*/
			div.restrictDrag = restrictDrag || false;
	
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
			 			img.style.filter = "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='/bitrix/themes/"+phpVars.ADMIN_THEME_ID+"/images/shadow.png',sizingMethod='scale')";
					}
					else
					{
			 			img = document.createElement("IMG");
						img.src = '/bitrix/themes/'+phpVars.ADMIN_THEME_ID+'/images/shadow.png';
					}
					img.id = div.id+'_shadow';
					img.style.position = 'absolute';
					img.style.zIndex = zIndex-2;
					img.style.left = '-1000px';
					img.style.top = '-1000px';
					img.style.lineHeight = 'normal';
					document.body.appendChild(img);
				}
				img.style.width = div.offsetWidth+'px';
				img.style.height = div.offsetHeight+'px';
				img.style.left = parseInt(div.style.left)+dxShadow+'px';
				img.style.top = parseInt(div.style.top)+dxShadow+'px';
				img.style.visibility = 'visible';
			}
			div.dxShadow = dxShadow;
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
		}
	
		this.Move = function(div, x, y)
		{
			if(!div)
				return;
	
			var dxShadow = div.dxShadow;
			var left = parseInt(div.style.left)+x;
			var top = parseInt(div.style.top)+y;
	
			if (div.restrictDrag)
			{
				//Left side
				if (left < 0)
					left = 0;
	
				//Right side
				if ( (document.compatMode && document.compatMode == "CSS1Compat"))
					windowWidth = document.documentElement.scrollWidth;
				else
				{
					if (document.body.scrollWidth > document.body.offsetWidth ||
						(document.compatMode && document.compatMode == "BackCompat") ||
						(document.documentElement && !document.documentElement.clientWidth)
					)
						windowWidth = document.body.scrollWidth;
					else
						windowWidth = document.body.offsetWidth;
				}
	
				var floatWidth = div.offsetWidth;
				if (left > (windowWidth - floatWidth - dxShadow))
					left = windowWidth - floatWidth - dxShadow;
	
				//Top side
				if (top < 0)
					top = 0;
			}
	
			div.style.left = left+'px';
			div.style.top = top+'px';
	
			this.AdjustShadow(div);
		}
	
		this.HideShadow = function(div)
		{
			var sh = document.getElementById(div.id + "_shadow");
			sh.style.visibility = 'hidden';
		}
	
		this.UnhideShadow = function(div)
		{
			var sh = document.getElementById(div.id + "_shadow");
			sh.style.visibility = 'visible';
		}
	
		this.AdjustShadow = function(div)
		{
			var sh = document.getElementById(div.id + "_shadow");
			if(sh && sh.style.visibility != 'hidden')
			{
				var dxShadow = div.dxShadow;
	
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
}
var jsPlayerFloatDiv = new JCFloatDiv();


/*utils class*/
//add if not exists
if(!window.jsUtils)
{
	var jsUtils =
	{
		arEvents: Array(),
	
		addEvent: function(el, evname, func, capture)
		{
			if(el.attachEvent)
				el.attachEvent("on" + evname, func);
			else if(el.addEventListener)
				el.addEventListener(evname, func, false);
			else
				el["on" + evname] = func;
			this.arEvents[this.arEvents.length] = {'element': el, 'event': evname, 'fn': func};
		},
	
		removeEvent: function(el, evname, func)
		{
			if(el.detachEvent)
				el.detachEvent("on" + evname, func);
			else if(el.removeEventListener)
				el.removeEventListener(evname, func, false);
			else
				el["on" + evname] = null;
		},
		
		IsIE: function()
		{
			return (document.attachEvent && !this.IsOpera());
		},

		IsOpera: function()
		{
			return (navigator.userAgent.toLowerCase().indexOf('opera') != -1);
		},

		IsSafari: function()
		{
			var userAgent = navigator.userAgent.toLowerCase();
			return (/webkit/.test(userAgent));
		}
	}
}