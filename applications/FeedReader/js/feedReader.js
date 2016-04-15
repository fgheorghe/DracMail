var feedReader = {}; // Main object

feedReader.create = function() {
	this.makeMask = function( message ) {
		this.mask = new Ext.LoadMask( Ext.getBody(), { msg: message } );
	}

	this.keyup = function( field, event ) {
		if ( event.getCharCode() !== 13 ) {
			return;
		}
		this.refresh( true );
	}

	this.refresh = function( gridOnly ) {
		if ( typeof gridOnly === "undefined" ) {
			gridOnly = false;
		}
		this.articleGrid.getStore().load({
			params: {
				itemId: this.itemId
				,search: this.search.getValue()
				,itemType: this.itemType
			}
		});
		if ( gridOnly !== true ) {
			this.feedTree.root.reload();
		}
	}

	this.errorDialog = function( text ) {
		Ext.Msg.show({
			title: 'Error'
			,msg: text
			,buttons: Ext.Msg.OK
			,icon: Ext.MessageBox.ERROR
		});
	}

	this.search = new Ext.form.TextField({
		width: 350
		,emptyText: 'Search for article'
		,enableKeyEvents: true
		,listeners: {
			keyup: this.keyup.createDelegate( this )
		}
	});

	this.newCategorySuccess = function() {
		this.feedTree.root.reload();
	}

	this.newCategoryFailure = function() {
		this.errorDialog( "Cannot create Category. Please try again later." );
	}

	this.doNewCategory = function( name ) {
		Ext.Ajax.request({
			url: fnXSRF('application=feedReader&action=addCategory&name=' + name )
			,success: this.newCategorySuccess.createDelegate( this )
			,failure: this.newCategoryFailure.createDelegate( this )
		});
	}

	this.processNewCategoryAnswer = function( button, text ) {
		if ( button === 'ok' && text !== "" ) {
			this.doNewCategory( text );
		} else if ( button === 'ok' && text === "" ) {
			this.errorDialog( "Please input Category name." );
		}
	}

	this.renameItemFailure = function( response ) {
		this.errorDialog( "Cannot rename " + ( this.type === 'category' ? 'Category' : 'Feed' )  + ". Please try again later." );
	}

	this.renameItemSuccess = function( response ) {
		try {
			var response = Ext.util.JSON.decode( response.responseText );
			var error = false;
			var msg = "";
			switch ( response.reason ) {
				case 1:
					error = true;
					msg = "Category name already in use.";
					break;
				case 2:
					error = true;
					msg = "Please input " + ( this.type === 'category' ? 'Category' : 'Feed' )  + " name.";
					break;
			}
			if ( error === true ) {
				this.errorDialog( msg );
				return;
			}
		} catch ( ex ) {
			// Ignore
		}
		this.refresh();
	}

	this.doRenameItem = function( name ) {
		Ext.Ajax.request({
			url: fnXSRF('application=feedReader&action=' + ( this.type === 'category' ? 'renameCategory' : 'renameFeed' ) + '&id=' + this.id + '&name=' + name )
			,success: this.renameItemSuccess.createDelegate( this )
			,failure: this.renameItemFailure.createDelegate( this )
		});
	}

	this.processRenameItemAnswer = function( button, text ) {
		if ( button === 'ok' ) {
			this.doRenameItem( text );
		}
	}

	this.newFeedFailure = function() {
		this.mask.hide();
		this.errorDialog( "Cannot add Feed. Please try again later." );
	}

	this.newFeedSuccess = function() {
		this.mask.hide();
		this.refresh();
	}

	this.doNewFeed = function( url ) {
		Ext.Ajax.request({
			url: fnXSRF('application=feedReader&action=addFeed&url=' + url + '&id=' + this.id )
			,success: this.newFeedSuccess.createDelegate( this )
			,failure: this.newFeedFailure.createDelegate( this )
		});
	}

	this.processNewFeedAnswer = function( button, text ) {
		if ( button === 'ok' && text !== '' ) {
			this.makeMask( 'Adding Feed...' );
			this.mask.show();
			this.doNewFeed( text );
		} else if ( button === 'ok' && text === '' ) {
			this.errorDialog( "Please input Feed URL." );
		}
	}

	this.renameItem = function( type, id ) {
		var title = "";
		var text = "";
		switch ( type ) {
			case "feed":
				title = 'Rename Feed';
				text = 'New Feed name:';
				break;
			case "category":
				title = 'Rename Category'
				text = 'New Category name:';
				break;
		}

		this.type = type; // Save for later use
		this.id = id;

		Ext.Msg.prompt( title, text, this.processRenameItemAnswer.createDelegate( this ) );
	}

	this.newCategory = function() {
		Ext.Msg.prompt( 'New category', 'Category name:', this.processNewCategoryAnswer.createDelegate( this ) );
	}

	this.newFeed = function( category_id ) {
		this.id = category_id;
		Ext.Msg.prompt( 'New feed', 'RSS Feed URL ( without leading http:// ):', this.processNewFeedAnswer.createDelegate( this ) );
	}

	this.deleteItemSuccess = function() {
		this.refresh();
	}

	this.deleteItemFailure = function() {
		this.errorDialog( "Cannot delete " + ( this.type === 'category' ? 'Category' : 'Feed' )  + ". Please try again later." );
	}

	this.doDeleteItem = function() {
		Ext.Ajax.request({
			url: fnXSRF('application=feedReader&action=' + ( this.type === 'category' ? 'deleteCategory' : 'deleteFeed' ) + '&id=' + this.id )
			,success: this.deleteItemSuccess.createDelegate( this )
			,failure: this.deleteItemFailure.createDelegate( this )
		});
	}

	this.processDeleteAnswer = function( button ) {
		if ( button === 'yes' ) {
			this.doDeleteItem();
		}
	}

	this.deleteItem = function( type, id ) {
		var text = "";
		switch ( type ) {
			case "feed":
				text = 'Delete selected Feed?';
				break;
			case "category":
				text = 'Delete selected Category and all subitems?'
				break;
		}

		this.id = id; // Save for later use
		this.type = type;

		Ext.Msg.show({
			title: this.title
			,msg: text
			,buttons: Ext.Msg.YESNO
			,fn: this.processDeleteAnswer.createDelegate( this )
			,animEl: 'elId'
			,icon: Ext.MessageBox.QUESTION
		});
	}

	this.updateFeed = function( feed_url, category_id ) {
		this.id = category_id;
		this.makeMask( 'Updating Feed...' );
		this.mask.show();
		this.doNewFeed( feed_url );
	}

	this.contextmenu = function( node, event ) {
		if ( typeof node.attributes.type === "undefined" ) {
			return;
		}
		var menuItems = [];

		if ( node.attributes.type === "category" ) {
			menuItems.push({
				text: 'New Feed'
				,handler: this.newFeed.createDelegate( this, [ node.attributes.id ] )
			},'-');
		}

		if ( node.attributes.type === "category" || node.attributes.type === "feed" ) {
			menuItems.push({
					text: 'Rename'
					,handler: this.renameItem.createDelegate( this, [ node.attributes.type, ( node.attributes.type === "category" ) ? node.attributes.id : node.attributes.feed_id ] )
				},'-', {
					text: 'Delete'
					,handler: this.deleteItem.createDelegate( this, [ node.attributes.type, ( node.attributes.type === "category" ) ? node.attributes.id : node.attributes.feed_id ] )
			});
		}

		if ( node.attributes.type === "feed" ) {
			menuItems.push( '-', {
				text: 'Update'
				,handler: this.updateFeed.createDelegate( this, [ node.attributes.feed_url, node.parentNode.attributes.id ] )
			});
		}

		if ( node.attributes.type === "root" ) {
			menuItems.push({
				text: 'New Category'
				,handler: this.newCategory.createDelegate( this )
			});
		}

		var menu = new Ext.menu.Menu({
			items: menuItems
		});

		menu.showAt( event.getXY() );
	}

	this.nodeDblClick = function( node, event ) {
		this.itemType = "";
		this.itemId = "";
		if ( node.attributes.type === "category" ) {
			this.itemType = 1;
			this.itemId = node.attributes.id;
		} else if ( node.attributes.type === "feed" ) {
			this.itemType = 2;
			this.itemId = node.attributes.feed_id;
		}
		this.refresh( true );
	}

	this.nodeClick = function( node ) {
		node.expand();
	}

	this.feedTree = new Ext.tree.TreePanel({
		region: 'west'
		,split: true
		,width: 250
		,border: false
		,title: 'Feeds'
		,autoScroll: true
		,dataUrl: fnXSRF('application=feedReader&action=loadMenu')
		,root: new Ext.tree.AsyncTreeNode({
			expanded: true
			,text: 'My Feeds'
			,type: 'root'
		})
		,rootVisible: true
		,listeners: {
			contextmenu: this.contextmenu.createDelegate( this )
			,dblclick: this.nodeDblClick.createDelegate( this )
			,click: this.nodeClick.createDelegate( this )
		}
	});

	this.searchButton = new Ext.Button({
		text: 'Search'
		,handler: this.refresh.createDelegate( this, [ true ] )
	});

	this.articleStore = new Ext.data.JsonStore({
		autoDestroy: false
		,url: fnXSRF('application=feedReader&action=loadArticles')
		,root: 'articles'
		,autoLoad: true
		,remoteSort: false
		,fields: [
			{ name: 'id' }
			,{ name: 'category_id' }
			,{ name: 'title' }
			,{ name: 'date' }
			,{ name: 'feed' }
			,{ name: 'link' }
			,{ name: 'description' }
			,{ name: 'image' }
		]
		,sortInfo: {
			field: "date"
			,direction: "DESC"
		}
	});

	this.readNews = function( link ) {
		var url = window.open( link, '_blank' );
	}

	this.rowdblclick = function( grid, rowIndex ) {
		var data = grid.getStore().getAt( rowIndex ).data;
		this.readNews( data.link );
	}

	this.contextmenu = function( grid, rowIndex, event ) {
		var data = grid.getStore().getAt( rowIndex ).data;
		var menu = new Ext.menu.Menu({
			items: [{
				text: 'Read in Browser'
				,handler: this.readNews.createDelegate( this, [ data.link ] )
			}]
		});
		menu.showAt( event.getXY() );
	}

	this.renderer = function( value ) {
		return '<b>' + value + '</b>';
	}

	this.articleGrid = new Ext.grid.GridPanel({
		height: 250
		,border: false
		,tbar: {
			items: [
				this.search
				,' '
				,this.searchButton
			]
		}
		,store: this.articleStore
		,autoExpandColumn: 'title'
		,colModel: new Ext.grid.ColumnModel({
			columns: [
				{ id: 'title', header: 'Title', dataIndex: 'title', sortable: true, renderer: this.renderer.createDelegate( this ) }
				,{ id: 'date', header: 'Date', dataIndex: 'date', width: 200, sortable: true, renderer: this.renderer.createDelegate( this ) }
				,{ id: 'feed', header: 'Feed', dataIndex: 'feed', width: 200, sortable: true, renderer: this.renderer.createDelegate( this ) }
			]
		})
		,loadMask: {
			msg: 'Fetching Articles...'
		}
		,viewConfig: {
			enableRowBody: true
			,getRowClass: function( record, rowIndex, rowParams, store ) {
				var image = "";
				var description = "";
				var columns = 1;
				if ( record.data.image !== "" ) {
					image = '<td>\
							<img src=\'' + record.data.image + '\'>\
						</td>';
					columns++;
				}
				if ( record.data.description !== "" ) {
					description = '<td style="width: 350px;">\
							' + record.data.description + '\
						</td>';
					columns++;
				}
				rowParams.body = '<table style="padding-left: 2px;">\
					<tr valign="top">\
						' + image + '\
						' + description + '\
					</tr>\
					<tr>\
						<td colspan="'+ columns + '" style="padding-bottom: 5px;">\
							<a href="' + record.data.link + '" target="_blank" title="Click to read article">' + record.data.link + '</a>\
						</td>\
					</tr>\
				</table>';
				return 'x-grid3-row-expanded';
			}
		}
		,listeners: {
			rowcontextmenu: this.contextmenu.createDelegate( this )
			,rowdblclick: this.rowdblclick.createDelegate( this )
		}
	});

	this.feedContent = new Ext.Panel({
		border: false
		,title: 'Articles'
		,region: 'center'
		,layout: 'fit'
		,items: [
			this.articleGrid
		]
	});

	/**
	 * Main container
	*/
	this.interface = new Ext.Panel({
		title: 'News Feeds'
		,id: 'feedReader'
		,closable: true
		,tbar: this.tbar
		,layout: 'border'
		,items: [
			this.feedTree
			,this.feedContent
		]
	});

	return this.interface;
}

feedReader.menuHandler = function() {
	this.create();

	mainUI.centerTabPanel.add( this.interface );
	mainUI.centerTabPanel.setActiveTab( this.interface.id );
}