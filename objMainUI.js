/**
 * Provides main UI container.
*/

/**
 * @param configuration
 *	Mixed Main User Interface configuration object. Parameters: centerRegion - item for the center region. westRegion - item for the west region, centerTabPanel - items for the main tab panel
*/
function objMainUI( configuration ) {
	/**
	 * UI Objects.
	*/
	this.centerTabPanel = new Ext.TabPanel({
		enableTabScroll: true
		,border: false
	});

	this.centerRegion = new Ext.Panel({
		region: 'center'
		,split: true
		,layout: 'fit'
		,border: false
		,title: 'dracMail Webmail v0.1'
		,tbar: configuration.UIMenu.toolbar
		,items: [
			this.centerTabPanel
		]
	});

	this.statusLabel =  new Ext.ux.StatusBar({
		defaultText: 'Ready.'
	});

	this.southRegion = new Ext.Panel({
		frame: false
		,height: 'auto'
		,border: false
		,region: 'south'
		,items: [
			this.statusLabel
		]
	});

	this.westRegion = new Ext.Panel({
		region: 'west'
		,minWidth: 150
		,width: 200
		,collapsible: true
		,border: false
		,split: true
		,layout: 'border'
		,title: 'Menu'
		,items: configuration.westRegion.items
	});

	this.interface = new Ext.Viewport({
		layout: 'border'
		,items: [
			this.centerRegion
			,this.westRegion
			,this.southRegion
		]
	});

	return this;
}