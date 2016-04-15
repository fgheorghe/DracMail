var notepadApplication = {}; // Main object

notepadApplication.create = function( id ) {
	this.activeFile = "";

	/**
	 * Close the application
	*/
	this.close = function() {
		mainUI.centerTabPanel.remove( this.interface );
	}

	/**
	 * Refresh ( new document )
	*/
	this.refresh = function() {
		this.editor.setValue("");
		this.interface.setTitle( "Notepad - New file" );
		this.interface.doLayout( false, true );
		this.activeFile = null;
	}

	this.setTitle = function() {
		this.interface.setTitle( "Notepad - " + this.activeFile );
		this.interface.doLayout( false, true );
	}

	this.openFileSuccess = function( response ) {
		var content = response.responseText;
		this.editor.setValue( content );
		this.setTitle();
	}

	this.openFileFailure = function() {
		this.activeFile = null;
	}

	this.renameFileSuccess = function( filename, tree ) {
		this.setTitle();
		tree.reload();
	}

	this.errorDialog = function( text ) {
		Ext.Msg.show({
			title: 'Error'
			,msg: text
			,buttons: Ext.Msg.OK
			,icon: Ext.MessageBox.ERROR
		});
	}

	this.renameFileFailure = function() {
		this.errorDialog( "Cannot rename file. Please try again later." );
	}

	this.renameFile = function( id, filename, tree ) {
		Ext.Ajax.request({
			url: fnXSRF('application=notepad&action=rename&id=' + id + '&filename=' + filename )
			,success: this.renameFileSuccess.createDelegate( this, [ filename, tree ] )
			,failure: this.renameFileFailure.createDelegate( this )
		});
	}

	/**
	 * Open a file
	*/
	this.openFile = function( id, filename ) {
		this.activeFile = filename;
		Ext.Ajax.request({
			url: fnXSRF('application=notepad&action=open&id=' + id )
			,success: this.openFileSuccess.createDelegate( this )
			,failure: this.openFileFailure.createDelegate( this )
		});
	}

	this.saveFileSuccess = function() {
		Ext.Msg.alert( 'Status', 'File successfully saved.' );
	}

	this.saveFileFailure = function() {
		this.errorDialog( "Cannot save file. Please try again later." );
	}

	this.deleteFileSuccess = function( tree ) {
		tree.reload();
	}

	this.deleteFileFailure = function() {
		this.errorDialog( "Cannot delete file. Please try again later." );
	}

	this.deleteFile = function( id, tree ) {
		Ext.Ajax.request({
			url: fnXSRF('application=notepad&action=delete&id=' + id )
			,success: this.deleteFileSuccess.createDelegate( this, [ tree ] )
			,failure: this.deleteFileFailure.createDelegate( this )
		});
	}

	/**
	 * Save file
	*/
	this.saveFile = function( filename ) {
		this.activeFile = filename;
		this.interface.setTitle( "Notepad - " + Ext.util.Format.htmlEncode( filename ) );
		Ext.Ajax.request({
			url: fnXSRF('application=notepad&action=save&filename=' + filename)
			,method: 'POST'
			,params: {
				content: this.editor.getValue()
			}
			,success: this.saveFileSuccess.createDelegate( this )
			,failure: this.saveFileFailure.createDelegate( this )
		});
	}

	this.browse = function( type ) {
		if ( type === 1 && this.activeFile != "" && this.activeFile != null ) {
			this.saveFile( this.activeFile );
			return;
		}

		this.window = new objNotepadBrowse({
			type: type
			,parent: this
		});
	}

	/**
	 * Top menu
	*/
	this.tbar = new Ext.Toolbar({
		items: [{
			text: 'File'
			,menu: [{
				text: 'New'
				,handler: this.refresh.createDelegate( this )
			},'-', {
				text: 'Open'
				,handler: this.browse.createDelegate( this, [ 0 ] )
			},'-',{
				text: 'Save'
				,handler: this.browse.createDelegate( this, [ 1 ] )
			},{
				text: 'Save As...'
				,handler: this.browse.createDelegate( this, [ 2 ] )
			},'-',{
				text: 'Close'
				,handler: this.close.createDelegate( this )
			}]
		}]
	});

	/**
	 * Editor
	*/
	this.editor = new Ext.ux.TinyMCE({
		region: 'center'
		,tabIndex: 5
		,tinymceSettings: {
			theme : "advanced",
			plugins: "safari,pagebreak,style,layer,table,advhr,iespell,insertdatetime,preview,print,nonbreaking,xhtmlxtras",
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsizeselect",
			theme_advanced_buttons2 : "bullist,numlist,|,outdent,indent,blockquote,|,insertdate,inserttime,preview,|,forecolor,backcolor",
			theme_advanced_buttons3 : "sub,sup,|,print,|,nonbreaking,pagebreak",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : false,
			extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]"
		}
	});

	/**
	 * Main container
	*/
	this.interface = new Ext.Panel({
		title: 'Notepad - New file'
		,id: 'notepadApplication_' + id
		,closable: true
		,tbar: this.tbar
		,layout: 'fit'
		,items: this.editor
	});

	return this;
}

notepadApplication.menuHandler = function() {
	var notepad = new this.create( Ext.id() );

	mainUI.centerTabPanel.add( notepad.interface );
	mainUI.centerTabPanel.setActiveTab( notepad.interface.id );
}