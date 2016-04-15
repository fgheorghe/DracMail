var changePassword = {};

changePassword.create = function() {
	this.closeWindow = function() {
		this.interface.hide();
	}

	this.saveSuccess = function() {
		this.mask.hide();
		this.interface.close();
	}

	this.saveFailure = function( form, action ) {
		this.mask.hide();
		switch ( action.failureType ) { // From ExtJS documentation
			case Ext.form.Action.CLIENT_INVALID:
				Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
				break;
			case Ext.form.Action.CONNECT_FAILURE:
				Ext.Msg.alert('Failure', 'Ajax communication failed');
				break;
			case Ext.form.Action.SERVER_INVALID:
				Ext.Msg.alert('Failure', action.result.msg);
		}
	}

	this.save = function() {
		this.mask = new Ext.LoadMask( this.interface.getId(), { msg: 'Updating Password...' });
		this.mask.show();
		this.form.getForm().submit({
			clientValidation: true
			,method: 'POST'
			,url: fnXSRF('application=changePassword&action=change')
			,success: this.saveSuccess.createDelegate( this )
			,failure: this.saveFailure.createDelegate( this )
		});
	}

	this.bbar = {
		items: ['->',{
			text: 'Save'
			,handler: this.save.createDelegate( this )
		},'-', {
			text: 'Cancel'
			,handler: this.closeWindow.createDelegate( this )
		}]
	}

	this.passwordField = new Ext.form.TextField({
		inputType: 'password'
		,name: 'password'
		,allowBlank: false
		,fieldLabel: 'Current password'
	});

	this.newPasswordField = new Ext.form.TextField({
		inputType: 'password'
		,name: 'newpassword'
		,allowBlank: false
		,fieldLabel: 'New password'
	});

	this.confirmPasswordField = new Ext.form.TextField({
		inputType: 'password'
		,name: 'confirmpassword'
		,allowBlank: false
		,fieldLabel: 'Confirm password'
	});

	this.form = new Ext.form.FormPanel({
		labelAlign: 'right'
		,padding: '5px'
		,frame: false
		,labelWidth: 120
		,border: false
		,items: [
			this.passwordField
			,this.newPasswordField
			,this.confirmPasswordField
		]
	});

	this.interface = new Ext.Window({
		modal: true
		,title: 'Change Password'
		,minimizable: false
		,maximizable: false
		,width: 330
		,resizable: false
		,height: 150
		,items: this.form
		,bbar: this.bbar
	});
}

changePassword.menuHandler = function() {
	this.create();
	this.interface.show();
	return false; // This is a window, prevent the menu from being selected
}