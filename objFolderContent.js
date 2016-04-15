/**
 * Provides Folder Content Panel, consisting of:
 * 1) Top grid - listing mail messages
 * 2) Bottom panel - display mail message
*/
/**
 * @param configuration
 *	Mixed various configuration options: ID - panel id
*/
function objFolderContent( configuration ) {
	this.reload = function() {
		this.itemsStore.reload();
		mailFolders.interface.selectPath( configuration.nodePath ); // Select associated node
		
	}

	this.itemsStore = new Ext.data.JsonStore({
		autoDestroy: false
		,url: configuration.url
		,root: 'mailList'
		,idProperty: 'id'
		,autoLoad: true
		,remoteSort: true
		,totalProperty: 'total'
		,fields: [
			{ name: 'id' }
			,{ name: 'seen' }
			,{ name: 'answered' }
			,{ name: 'subject' }
			,{ name: 'sender' }
			,{ name: 'ccaddress' }
			,{ name: 'bccaddress' }
			,{ name: 'reply_toaddress' }
			,{ name: 'recipient' }
			,{ name: 'date' }
			,{ name: 'size' }
			,{ name: 'attachments' }
		]
	});

	this.getSelected = function() {
		var selectionCount = this.itemsGrid.getSelectionModel().getCount();
		if ( selectionCount == 0 ) {
			return; // Nothing selected.
		}
		var selections = this.itemsGrid.getSelectionModel().getSelections();
		var selectedIds = "";
		for ( var i = 0; i < selectionCount; i++ ) {
			if ( i != 0 ) {
				selectedIds += ",";
			}
			selectedIds += selections[i].data.id;
		}
		return selectedIds;
	}

	/* Mark message(s) as read or unread. Type 1 - read, 2 - unread */
	this.handlerMark = function( type ) {
		var me = this;
		var selectedIds = this.getSelected();
		if ( selectedIds === "" ) {
			return;
		}
		/* Do an ajax request */
		Ext.Ajax.request({
			url: fnXSRF('action=mark&as=' + type + '&ids=' + selectedIds + '&folder_id=' + configuration.folder_id)
			,success: function( response ) {
				fnCheckMail( 1, true );
			}
		});
	}

	this.doSearch = function() {
		var value = this.searchField.getValue();
		var type = this.searchIn.getValue();
		var show = this.filterMail.getValue();
		this.itemsStore.setBaseParam( "type", type );
		this.itemsStore.setBaseParam( "value", value );
		this.itemsStore.setBaseParam( "show", show );
		this.itemsStore.load();
	}

	this.keyEventHandler = function( combo, event ) {
		if ( event.getCharCode() === 13 ) {
			this.doSearch();
		}
	}

	this.searchField = new Ext.form.TextField({
		width: 200
		,tabIndex: 3
		,enableKeyEvents: true
		,listeners: {
			keyup: this.keyEventHandler.createDelegate( this )
		}
	});

	this.filterMail = new Ext.form.ComboBox({
		typeAhead: true
		,triggerAction: 'all'
		,allowBlank: false
		,forceSelection: true
		,value: 1
		,lazyRender: true
		,width: 90
		,mode: 'local'
		,tabIndex: 1
		,store: new Ext.data.ArrayStore({
			id: 0
			,fields: [
				'type'
				,'displayText'
			],
			data: [ [1, 'Show All'], [2, 'Show Unread'], [3, 'Show Read'] ]
		})
		,valueField: 'type'
		,displayField: 'displayText'
		,listeners: {
			collapse: this.doSearch.createDelegate( this )
		}
	});

	this.searchIn = new Ext.form.ComboBox({
		typeAhead: true
		,triggerAction: 'all'
		,allowBlank: false
		,forceSelection: true
		,value: 1
		,lazyRender: true
		,tabIndex: 2
		,width: 70
		,mode: 'local'
		,store: new Ext.data.ArrayStore({
			id: 0
			,fields: [
				'type'
				,'displayText'
			],
			data: [ [1, 'Find All'], [2, 'Subject'], [3, 'Sender'], [4, 'Body'] ]
		})
		,valueField: 'type'
		,displayField: 'displayText'
		,listeners: {
			collapse: this.doSearch.createDelegate( this )
		}
	});

	this.deleteMessage = function( selectedId ) {
		if ( typeof selectedId !== "undefined" && selectedId === true ) {
			var selectedIds = this.currentMessage;
		} else {
			var selectedIds = this.getSelected();
		}
		if ( selectedIds === "" ) {
			return;
		}
		Ext.Ajax.request({
			url: fnXSRF('action=delete&ids=' + selectedIds + '&folder_id=' + configuration.folder_id)
			,success: function( response ) {
				fnCheckMail( 1, true );
			}
		});
	}

	this.handleMessageBoxButton = function( button, text, object, selectedId ) {
		if ( button === 'yes' ) { // If the user agreed, delete the message.
			this.deleteMessage( selectedId );
		} // Otherwise ignore the request.
	}

	this.handleDeleteMessage = function( selectedId ) {
		if ( configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_TRASH ) { // If deleting from the TRASH folder, ask for user permission.
			Ext.Msg.show({
				title:'Delete message?'
				,msg: 'Deleting a message from the trash folder cannot be undone. Continue?'
				,buttons: Ext.Msg.YESNO
				,fn: this.handleMessageBoxButton.createDelegate( this, [ selectedId ], true )
				,animEl: 'elId'
				,icon: Ext.MessageBox.QUESTION
			});
		} else { // Otherwise just delete it (move to trash)
			this.deleteMessage( selectedId );
		}
	}

	this.handleMoveMessage = function( folder_id ) {
		var me = this;
		var selectedIds = this.getSelected();
		if ( selectedIds === "" ) {
			return;
		}
		Ext.Ajax.request({
			url: fnXSRF('action=move&ids=' + selectedIds + '&folder_id=' + configuration.folder_id + '&to_folder_id=' + folder_id )
			,success: function( response ) {
				fnCheckMail( 1, true );
			}
		});
	}

	/**
	 * Folder items grid ( mail list ).
	*/
	this.rowcontextmenu = function( grid, rowIndex, event ) {
		var menuItems = []; // Prepare the menu items array
		var selectionCount = grid.getSelectionModel().getCount();
		if ( selectionCount === 0 ) {
			return false; // Nothing to display
		} else if ( selectionCount !== 1 ) {
			menuItems = [ new Ext.menu.Item({
					text: 'Delete selected'
					,handler: this.handleDeleteMessage.createDelegate( this )
				})
			];
		} else {
			menuItems = [ new Ext.menu.Item({
					text: 'Read'
					,handler: this.rowdblclick.createDelegate( this, [ grid, rowIndex, event ] )
				})
				,new Ext.menu.Separator()
				,new Ext.menu.Item({
					text: 'Delete'
					,handler: this.handleDeleteMessage.createDelegate( this )
			})]
		}

		var moveToMenu = [];
		for ( var i = 0; i < mailFolders.interface.root.childNodes.length; i++ ) {
			if ( mailFolders.interface.root.childNodes[i].attributes.folder_id != configuration.folder_id ) {
				moveToMenu.push(
					new Ext.menu.Item({
						text: mailFolders.interface.root.childNodes[i].attributes.name
						,handler: this.handleMoveMessage.createDelegate( this, [ mailFolders.interface.root.childNodes[i].attributes.folder_id ] )
					})
				);
			}
		}

		menuItems.push(
			'-'
			,new Ext.menu.Item({
				text: 'Mark as'
				,menu: [ new Ext.menu.Item({
					text: 'Read'
					,handler: this.handlerMark.createDelegate( this, [1] )
				}), new Ext.menu.Item({
					text: 'Unread'
					,handler: this.handlerMark.createDelegate( this, [2] )
				})]
			})
			,new Ext.menu.Item({
				text: 'Move to'
				,menu: moveToMenu
			})
		);

		menu = new Ext.menu.Menu({
			items: menuItems
		});

		menu.showAt( event.getXY() );
	}

	this.rowdblclick = function( grid, rowIndex, event ) {
		// Load email content
		var mailId = grid.getStore().getAt( rowIndex ).data.id;
		var date = grid.getStore().getAt( rowIndex ).data.date;
		var mailSubject = grid.getStore().getAt( rowIndex ).data.subject;
		var sender = grid.getStore().getAt( rowIndex ).data.sender;
		var cc = grid.getStore().getAt( rowIndex ).data.ccaddress;
		var bcc = grid.getStore().getAt( rowIndex ).data.bccaddress;
		var replyTo = grid.getStore().getAt( rowIndex ).data.reply_toaddress;
		var recipient = grid.getStore().getAt( rowIndex ).data.recipient;
		var attachments = grid.getStore().getAt( rowIndex ).data.attachments;
		Ext.get( this.itemsGrid.getView().getRow( rowIndex ) ).removeClass('unread-row');
		if ( this.contentPanel.collapsed == true ) {
			this.contentPanel.expand( true );
		}
		this.currentMessage = mailId;
		this.openMail({ url: fnXSRF('action=viewMessage&id=' + mailId + '&folder_id=' + configuration.folder_id), title: mailSubject, subject: mailSubject, sender: sender, recipient: recipient, cc: cc, bcc: bcc, replyTo: replyTo, date: date, attachments: attachments, message_id: mailId, folder_id: configuration.folder_id, folder_type: configuration.folder_type });
	}

	this.formatRow = function( record, index, rowParams ) {
		if ( record.data.seen == 0 ) { // Mark as unread ( set to bold )
			return 'unread-row';
		}
	}

	this.selectionModel = new Ext.grid.CheckboxSelectionModel();
	this.itemsGrid = new Ext.grid.GridPanel({
		region: 'center'
		,split: true
		,border: false
		,tbar: {
			items: [
				this.filterMail
				,' '
				,this.searchIn
				,' '
				,this.searchField
				,' '
				,{
					text: 'Find'
					,handler: this.doSearch.createDelegate( this )
				}
			]
		}
		,store: this.itemsStore
		,autoExpandColumn: 'subject'
		,loadMask: {
			msg: 'Loading...'
		}
		,autoExpandMax: 4000
		,columns: [
			this.selectionModel
			,{ id: 'subject', header: 'Subject', dataIndex: 'subject', hideable: true, sortable: true }
			,{ id: 'sender', header: 'Sender', dataIndex: 'sender', hideable: true, sortable: true, width: 250, hidden: configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_SENT || configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ? true : false }
			,{ id: 'recipient', header: 'Recipient', dataIndex: 'recipient', hideable: true, sortable: true, width: 250, hidden: configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_SENT || configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS || configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_TRASH ? false : true }
			,{ id: 'ccaddress', header: 'Cc', dataIndex: 'ccaddress', hideable: true, sortable: true, width: 150, hidden: true }
			,{ id: 'bccaddress', header: 'Bcc', dataIndex: 'bccaddress', hideable: true, sortable: true, width: 150, hidden: true }
			,{ id: 'reply_toaddress', header: 'Reply to', dataIndex: 'reply_toaddress', hideable: true, sortable: true, width: 150, hidden: true }
			,{ id: 'date', header: 'Date', dataIndex: 'date', hideable: true, sortable: true, renderer: dateRender.createDelegate( this ), width: 160 }
			,{ id: 'size', header: 'Size', dataIndex: 'size', hideable: true, sortable: true, renderer: sizeRender.createDelegate( this ), width: 120 }
			,{ id: 'attachments', header: 'Attachments', dataIndex: 'attachments', hideable: true, sortable: true, width: 220 }
		]
		,sm: this.selectionModel
		,bbar: new Ext.PagingToolbar({
			pageSize: 50
			,store: this.itemsStore
			,displayInfo: true
			,displayMsg: 'Displaying messages {0} - {1} of {2}'
			,emptyMsg: "No messages to display."
		})
		,viewConfig: {
			getRowClass: this.formatRow.createDelegate( this )
			,emptyText: 'No messages to display.'
		}
		,listeners: {
			rowcontextmenu: this.rowcontextmenu.createDelegate( this )
			,rowdblclick: this.rowdblclick.createDelegate( this )
		}
	});

	this.openMail = function( configuration ) {
		this.headerContent.setVisible( true );
		this.replyButton.setDisabled( false );
		this.replyAllButton.setDisabled( false );
		this.forwardButton.setDisabled( false );
		this.deleteButton.setDisabled( false );
		this.mailContent.removeAll();
		this.contentPanel.setTitle( 'Subject: ' + configuration.title );
		this.fromField.setText( 'From: ' + configuration.sender, false );
		if ( configuration.recipient == "" ) {
			this.toField.setVisible( false );
		} else {
			this.toField.setText( 'To: ' + configuration.recipient, false );
			this.toField.setVisible( true );
		}
		if ( typeof configuration.cc != "undefined" && configuration.cc != "" ) {
			this.ccField.setText( 'Cc: ' + configuration.cc );
			this.ccField.setVisible( true );
		} else {
			this.ccField.setVisible( false );
		}
		if ( typeof configuration.bcc != "undefined" && configuration.bcc != "" ) {
			this.bccField.setText( 'Bcc: ' + configuration.bcc );
			this.bccField.setVisible( true );
		} else {
			this.bccField.setVisible( false );
		}
		if ( typeof configuration.replyTo != "undefined" && configuration.replyTo != "" && configuration.replyTo != configuration.sender ) {
			this.replyToField.setText( 'Reply to: ' + configuration.replyTo );
			this.replyToField.setVisible( true );
		} else {
			this.replyToField.setVisible( false );
		}
		if ( typeof configuration.attachments != "undefined" && configuration.attachments != "" ) {
			this.attachmentsField.setVisible( true );
			this.attachmentsField.setText( 'Attachments: ' + formatAttachments( configuration.attachments, configuration.message_id, configuration.folder_id ), false );
		} else {
			this.attachmentsField.setVisible( false );
		}
		this.iframeId = Ext.id();
		this.mailContent.add( new Ext.BoxComponent({
			cls: 'contentIframe'
			,autoEl: {
				tag: 'iframe'
				,id: this.iframeId
				,src: configuration.url
			}
			,listeners: {
				afterrender: function() {
					fnCheckMail( 1, false ); // Update tree menu
				}
			}
		}));
		this.configuration = configuration;
		this.headerPanel.setHeight( this.headerContent.getHeight() );
		this.contentPanel.doLayout( false, true );
	}

	this.fromField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.toField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.ccField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.bccField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.replyToField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.attachmentsField = new Ext.form.Label({
		hideLabel: true
		,fieldLabel: ' '
	});

	this.headerContent = new Ext.form.FormPanel({
		labelAlign: 'right'
		,frame: true
		,autoHeight: true
		,hidden: true
		,items: [
			this.fromField
			,this.toField
			,this.ccField
			,this.bccField
			,this.replyToField
			,this.attachmentsField
		]
	});

	this.headerPanel = new Ext.Panel({
		region: 'north'
		,autoHeight: true
		,border: false
		,layout: 'fit'
		,items: this.headerContent
	});

	this.mailContent = new Ext.Panel({
		region: 'center'
		,layout: 'fit'
		,html: '<div align="center">Please select a message from the mail list.</div>'
		,border: false
	});

	this.replyHandler = function( type ) {
		if ( typeof type === "undefined" ) {
			type = 0; // Default to reply to sender.
		}
		var configuration = this.configuration;
		var composerConfiguration = {};
		switch ( type ) {
			case 0: // Reply sender
				var content = "";
				var subject = "";
				// If FOLDER_TYPE_DRAFTS append the content as it is, otherwise format for reply
				if ( configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ) {
					subject = configuration.subject;
					content = document.getElementById(this.iframeId).contentWindow.document.body.innerHTML;
				} else {
					subject = "Re: " + configuration.subject;
					content = replyContentRender( dateRender( configuration.date ), ( typeof configuration.replyTo !== "undefined" && configuration.replyTo !== "" ) ? configuration.replyTo : configuration.sender, document.getElementById(this.iframeId).contentWindow.document.body.innerHTML );
				}
				composerConfiguration = {
					to: ( typeof configuration.replyTo !== "undefined" && configuration.replyTo !== "" ) ? configuration.replyTo : configuration.sender
					,subject: subject
					,content: content
					
				};
				break;
			case 1: // Reply all
				composerConfiguration = {
					to: ( typeof configuration.replyTo !== "undefined" && configuration.replyTo !== "" ) ? configuration.replyTo : configuration.sender
					,subject: "Re: " + configuration.subject
					,cc: getRecipients( configuration.recipient + ( typeof configuration.cc !== "undefined" && configuration.cc !== "" ? "; " + configuration.cc : "" ) )
					,bcc: configuration.bcc
					,content: replyContentRender( dateRender( configuration.date ), (typeof configuration.replyTo !== "undefined" && configuration.replyTo !== "" ) ? configuration.replyTo : configuration.sender, document.getElementById(this.iframeId).contentWindow.document.body.innerHTML )
					
				};
				break;
			case 2: // forward
				composerConfiguration = {
					subject: "Fwd: " + configuration.subject
					,content: replyContentRender( dateRender( configuration.date ), (typeof configuration.replyTo !== "undefined" && configuration.replyTo !== "" ) ? configuration.replyTo : configuration.sender, document.getElementById(this.iframeId).contentWindow.document.body.innerHTML )
					
				};
				break;
		}
		var composer = new objComposer( composerConfiguration ).interface;
		mainUI.centerTabPanel.add( composer );
		mainUI.centerTabPanel.setActiveTab( composer );
	}

	this.replyButton = new Ext.Button({
		text: configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ? 'Edit' : 'Reply'
		,iconCls: 'replySender'
		,disabled: true
		,handler: this.replyHandler.createDelegate( this, [0] )
	});

	this.replyAllButton = new Ext.Button({
		text: 'Reply all'
		,iconCls: 'replyAll'
		,disabled: true
		,hidden: configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ? true : false
		,handler: this.replyHandler.createDelegate( this, [1] )
	});
	
	this.forwardButton = new Ext.Button({
		text: 'Forward'
		,iconCls: 'forwardMail'
		,disabled: true
		,hidden: configuration.folder_type === objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ? true : false
		,handler: this.replyHandler.createDelegate( this, [2] )
	});
	
	this.deleteButton = new Ext.Button({
		text: 'Delete'
		,iconCls: 'deleteMail'
		,disabled: true
		,handler: this.handleDeleteMessage.createDelegate( this, [ true ] )
	});

	this.buttons = [];

	// Build buttons according to folder type
	this.buttons.push( this.replyButton );
	if ( configuration.folder_type !== objGeneric.folderTypes.FOLDER_TYPE_DRAFTS ) {
		this.buttons.push( '-', this.replyAllButton,'-', this.forwardButton );
	}
	this.buttons.push( '-', this.deleteButton );

	this.contentPanel = new Ext.Panel({
		region: 'south'
		,split: true
		,height: 300
		,collapsed: true
		,collapsible: true
		,border: true
		,layout: 'border'
		,tbar: {
			items: this.buttons
	        }
		,items: [
			this.headerPanel
			,this.mailContent
		]
	});

	this.interface = new Ext.Panel({
		title: configuration.title
		,layout: 'border'
		,id: configuration.id
		,registerAsMail: true
		,mailGrid: this.itemsGrid
		,closable: true
		,items: [
			this.itemsGrid
			,this.contentPanel
		]
		,listeners: {
			show: this.reload.createDelegate( this )
		}
	});

	return this;
}