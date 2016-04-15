/**
 * Provides composer functionality.
*/
function objComposer( configuration ) {
	if ( typeof configuration === "undefined" ) {
		configuration = {};
	}
	/**
	 * Detect various configuration options.
	*/
	var to = "";
	var cc = "";
	var bcc = "";
	var subject = "";
	var content = "";
	/* Recipient */
	if ( typeof configuration.to !== "undefined" && configuration.to !== "" ) {
		to = configuration.to;
	}
	if ( typeof configuration.cc !== "undefined" && configuration.cc !== "" ) {
		cc = configuration.cc;
	}
	if ( typeof configuration.bcc !== "undefined" && configuration.bcc !== "" ) {
		bcc = configuration.bcc;
	}
	if ( typeof configuration.subject !== "undefined" && configuration.subject !== "" ) {
		subject = configuration.subject;
	}
	if ( typeof configuration.content !== "undefined" && configuration.content !== "" ) {
		content = configuration.content;
	}
	this.editor = new Ext.ux.TinyMCE({
		id: "richText"
		,region: 'center'
		,tabIndex: 5
		,value: content
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

	this.toField = new Ext.form.TextField({
		fieldLabel: 'To'
		,width: 380
		,tabIndex: 1
		,value: to
		,name: 'to'
		,allowBlank: false
	});

	this.ccField = new Ext.form.TextField({
		fieldLabel: 'Cc'
		,tabIndex: 2
		,width: 380
		,name: 'cc'
		,value: cc
	});

	this.bccField = new Ext.form.TextField({
		fieldLabel: 'Bcc'
		,tabIndex: 3
		,width: 380
		,name: 'bcc'
		,value: bcc
	});

	this.subjectField = new Ext.form.TextField({
		fieldLabel: 'Subject'
		,width: 380
		,tabIndex: 4
		,name: 'subject'
		,allowBlank: false
		,value: subject
	});

	this.attachmentField_1 = new Ext.ux.form.FileUploadField({
		fieldLabel: 'Attachment 1'
		,width: 380
		,tabIndex: 5
		,name: 'attachment_1'
	});

	this.attachmentField_2 = new Ext.ux.form.FileUploadField({
		fieldLabel: 'Attachment 2'
		,width: 380
		,tabIndex: 6
		,name: 'attachment_2'
	});

	this.attachmentField_3 = new Ext.ux.form.FileUploadField({
		fieldLabel: 'Attachment 3'
		,width: 380
		,tabIndex: 7
		,name: 'attachment_3'
	});

	this.contentField = new Ext.form.TextField({
		name: 'content'
		,hidden: true
	});

	this.draftField = new Ext.form.TextField({
		name: 'draft'
		,hidden: true
	});

	this.sendForm = new Ext.form.FormPanel({
		region: 'north'
		,fileUpload: true
		,labelAlign: 'right'
		,height: 200
		,frame: true
		,items: [
			this.toField
			,this.ccField
			,this.bccField
			,this.subjectField
			,this.attachmentField_1
			,this.attachmentField_2
			,this.attachmentField_3
			,this.contentField
			,this.draftField
		]
	});

	this.failureHandler = function( form, action ) {
		if ( typeof this.loadMask !== "undefined" ) {
			this.loadMask.hide(); 
		}
		handleFormFailure( form, action );
	}
	
	this.sendHandler = function( draft ) {
		if ( typeof draft === "undefined" || draft == false ) {
			draft = "1"; // Message is not a draft
		} else if ( draft === true ) {
			draft = "0"; // Message is a draft
		}
		this.loadMask = new Ext.LoadMask( this.interface.id, { msg: ( draft === "0" ? "Saving draft" : "Sending mail" ) + "..."} );
		this.loadMask.show();
		this.contentField.setValue( this.editor.getValue() );
		this.draftField.setValue( draft );
		this.sendForm.getForm().submit({
			url: fnXSRF('action=sendMail')
			,method: 'POST'
			,clientValidation: ( draft === "0" ) ? false : true
			,success: this.close.createDelegate( this )
			,failure: this.failureHandler.createDelegate( this )
		});
	}

	this.close = function() {
		mainUI.centerTabPanel.remove( this.interface, true );
	}

	this.toolbar = new Ext.Toolbar({
		items: [{
				text: 'Send'
				,iconCls: 'sendMail'
				,handler: this.sendHandler.createDelegate( this, [ false ] )
		},'-',{
			text: 'Save as Draft'
			,iconCls: 'saveDraft'
			,handler: this.sendHandler.createDelegate( this, [ true ] )
		},'-', {
				text: 'Cancel'
				,iconCls: 'cancelMail'
				,handler: this.close.createDelegate( this )
	        }, ' ']
	});

	this.startPosition = function() {
		this.editor.focus();
	}

	this.interface = new Ext.Panel({
		title: 'New mail'
		,layout: 'border'
		,closable: true
		,items: [ this.editor, this.sendForm ]
		,tbar: this.toolbar
		,listeners: {
			afterlayout: this.startPosition.createDelegate( this )
		}
	});

	return this;
}