/**
 * Object used for 'browsing' files.
 * Serves the Save, Save As... and Open menu items.
*/

function objNotepadBrowse( configuration ) {
	this.selectedFile = "";

	switch ( configuration.type ) {
		case 0:
			this.title = "Open file";
			this.button = "Open";
			break;
		case 1:
			this.title = "Save file";
			this.button = "Save";
			break;
		case 2:
			this.title = "Save file as...";
			this.button = "Save";
			break;
	}

	if ( configuration.type === 1 || configuration.type === 2 ) {
		this.textValueChange = function() {
			this.selectedFile = this.fileName.getValue();

			if ( this.selectedFile === "" ) {
				this.actionButton.setDisabled( true );
			} else {
				this.actionButton.setDisabled( false );
			}
		}

		this.fileName = new Ext.form.TextField({
			enableKeyEvents: true
			,listeners: {
				keyup: this.textValueChange.createDelegate( this )
			}
		});
	}

	this.openFile = function() {
		configuration.parent.openFile( this.selectedFileId, this.selectedFile );
		this.cancel();
	}

	this.doSave = function() {
		configuration.parent.saveFile( this.selectedFile );
		this.cancel();
	}

	this.processAnswer = function( button ) {
		if ( button == 'yes' ) {
			this.doSave();
		}
	}

	this.checkExistingFile = function( filename ) {
		var children = this.fileTree.getRootNode().childNodes;
		if ( children.length != 0 ) {
			for ( var i = 0; i < children.length; i++ ) {
				if ( children[i].attributes.text === filename ) {
					return true;
				}
			}
		}
		return false;
	}

	this.saveFile = function() {
		if ( this.checkExistingFile( this.fileName.getValue() ) === true ) {
			Ext.Msg.show({
				title: this.title
				,msg: 'Overwrite existing file?'
				,buttons: Ext.Msg.YESNO
				,fn: this.processAnswer.createDelegate( this )
				,animEl: 'elId'
				,icon: Ext.MessageBox.QUESTION
			});
			return;
		}

		this.doSave();
	}

	this.cancel = function() {
		this.interface.hide();
	}

	this.click = function( node ) {
		if ( typeof node.attributes.type === "undefined" || node.attributes.type !== "file") {
			if ( configuration.type === 0 ) {
				this.actionButton.setDisabled( true );
			}
		} else {
			if ( configuration.type === 0 ) {
				this.selectedFile = node.attributes.text;
				this.selectedFileId = node.attributes.file_id;
				this.actionButton.setDisabled( false );
			} else {
				this.fileName.setValue( node.attributes.text );
				this.textValueChange();
			}
		}
	}

	this.dblclick = function( node ) {
		if ( typeof node.attributes.type !== "undefined" && node.attributes.type === "file") {
			this.click( node );
			if ( configuration.type === 0 ) {
				this.openFile();
			} else {
				this.saveFile();
			}
		}
	}

	this.actionButton = new Ext.Button({
		text: this.button
		,disabled: true
		,listeners: {
			click: ( configuration.type === 0 ? this.openFile.createDelegate( this ) : this.saveFile.createDelegate( this ) )
		}
	});

	this.items = [];

	this.tbar = null;
	if ( this.fileName ) {
		this.items.push( 'Filename:', this.fileName );
		this.tbar = {
			items: this.items
		}
	}

	this.contextmenu = function( node, event ) {
		if ( typeof node.attributes.type !== "undefined" && node.attributes.type === "file") {
			var menu = new Ext.menu.Menu({
				items: [{
					text: 'Rename'
					,handler: this.renameFile.createDelegate( this, [ node ] )
				},'-', {
					text: 'Delete'
					,handler: this.deleteFile.createDelegate( this, [ node ] )
				}]
			});
			menu.showAt( event.getXY() );
		}
	}

	this.doDelete = function( node ) {
		configuration.parent.deleteFile( node.attributes.file_id, this.fileTree.root );
	}

	this.doRename = function( node, filename ) {
		configuration.parent.renameFile( node.attributes.file_id, filename, this.fileTree.root );
	}

	this.processDeleteAnswer = function( button ) {
		if ( button == 'yes' ) {
			this.doDelete( this.node );
		}
	}

	this.deleteFile = function( node ) {
		this.node = node;
		Ext.Msg.show({
			title: 'Delete'
			,msg: 'Delete file?'
			,buttons: Ext.Msg.YESNO
			,fn: this.processDeleteAnswer.createDelegate( this )
			,animEl: 'elId'
			,icon: Ext.MessageBox.QUESTION
		});
		return;
	}

	this.processRenameOverwriteAnswer = function( button ) {
		if ( button === 'yes' ) {
			this.doRename( this.node, this.newFilename );
		}
	}

	this.processRenameAnswer = function( button, text ) {
		if ( button == 'ok' && text != "" ) {
			if ( this.checkExistingFile( text ) ) {
				this.newFilename = text;
				Ext.Msg.show({
					title: 'Overwrite'
					,msg: 'Overwrite existing file?'
					,buttons: Ext.Msg.YESNO
					,fn: this.processRenameOverwriteAnswer.createDelegate( this )
					,animEl: 'elId'
					,icon: Ext.MessageBox.QUESTION
				});
				return;
			}
			this.doRename( this.node, text )
		}
	}

	this.renameFile = function( node ) {
		this.node = node;
		Ext.Msg.prompt( 'Rename file', 'Filename:', this.processRenameAnswer.createDelegate( this ) );
	}

	/**
	 * Expand tree
	*/
	this.expandAll = function() {
		this.fileTree.expandAll();
	}

	this.fileTree = new Ext.tree.TreePanel({
		border: false
		,autoScroll: true
		,title: ( configuration.type !== 0 ) ? 'Existing files' : null
		,dataUrl: fnXSRF('application=notepad&action=files')
		,root: new Ext.tree.AsyncTreeNode({
			expanded: true
			,text: 'My Files'
		})
		,rootVisible: true
		,listeners: {
			click: this.click.createDelegate( this )
			,contextmenu: this.contextmenu.createDelegate( this )
			,dblclick: this.dblclick.createDelegate( this )
			,afterrender: this.expandAll.createDelegate( this )
		}
	});

	this.interface = new Ext.Window({
		title: this.title
		,height: 300
		,width: 300
		,modal: true
		,layout: 'fit'
		,items: new Ext.Panel({
			layout: 'fit'
			,border: false
			,tbar: this.tbar
			,items: this.fileTree
		})
		,bbar: {
			items: ['->', this.actionButton, {
				text: 'Cancel'
				,handler: this.cancel.createDelegate( this )
			}]
		}
	});

	this.interface.show();

	return this;
}