/**
 * Provides mail folders, presented by a Tree.
*/

/**
 * @param configuration
 *	Mixed various folder object configuration options: dataUrl - data provider url
*/
function objMailFolders( configuration ) {
	/**
	 * Object handlers
	*/
	this.nodeClick = function( node, event ) {
		if ( typeof node.attributes.type !== "undefined" && node.attributes.type === "clickable" ) {
			// Load the new mail folder
			mainUI.centerTabPanel.add(
				new objFolderContent({
					title: node.attributes.name
					,id: 'folder_' + node.attributes.folder_id // Set the panel ID similar to the node ID
					,url: fnXSRF('action=listMessages&folder_id=' + node.attributes.folder_id)
					,folder: node.attributes.name
					,folder_id: node.attributes.folder_id
					,folder_type: node.attributes.folder_type
					,nodePath: node.getPath()
				}).interface
			);
			mainUI.centerTabPanel.setActiveTab( 'folder_' + node.attributes.folder_id ); // Activate the added tab
		}
	}

	this.rootLoad = function( node ) {
		try {
			if ( objGeneric.isReady === false ) {
				objGeneric.isReady = true; // Mark the UI as ready.
				for ( var i = 0; i < node.childNodes.length; i++ ) {
					if ( node.childNodes[i].attributes.folder_type === objGeneric.folderTypes.FOLDER_TYPE_INBOX ) {
						this.nodeClick( node.childNodes[i] );
					}
				}
			}
		} catch ( ex ) {
			// Do nothing.
		}
	}

	/**
	 * Expand tree
	*/
	this.expandAll = function() {
		this.interface.expandAll();
	}
	/**
	 * UI Objects.
	*/
	this.interface = new Ext.tree.TreePanel({
		useArrows: true
		,autoScroll: true
		,region: 'center'
		,split: true
		,animate: true
		,minHeight: 150
		,border: false
		,dataUrl: configuration.dataUrl
        	,root: {
			nodeType: 'async'
			,text: 'Mail Folders'
			,draggable: false
			,id: 'source'
			,clearOnLoad: false
			,listeners: {
				load: this.rootLoad.createDelegate( this )
			}
        	}
        	,listeners: {
			click: this.nodeClick.createDelegate( this )
			,afterrender: this.expandAll.createDelegate( this )
        	}
	});

	return this;
}