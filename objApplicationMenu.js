function objApplicationMenu() {
	var configuration = {};
	configuration.hidden = true;
	configuration.menuItems = [];

	for ( key in applications ) { // Generate menu items, for each loaded application
		if ( typeof applications[key].menu !== "undefined" ) {
			configuration.menuItems.push( applications[key].menu );
		}
	}

	if ( configuration.menuItems.length !== 0 ) {
		configuration.hidden = false;
	}

	this.interface = new Ext.tree.TreePanel({
		region: 'south'
		,height: 450
		,collapsible: true
		,split: true
		,title: 'Applications'
		,hidden: configuration.hidden
		,border: false
		,loader: new Ext.tree.TreeLoader()
		,root: new Ext.tree.AsyncTreeNode({
			expanded: true
			,children: configuration.menuItems
		})
		,rootVisible: false
	});

	return this;
}