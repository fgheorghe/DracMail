/**
 * Provides login functionality.
*/
function objLoginWindow() {
	this.successHandler = function( form, action ) {
		this.wait.hide();
		var result = Ext.util.JSON.decode( action.response.responseText );
		if ( result.success == true && result.firstTime == true ) {
			mailAddress = this.usernameInput.getValue();
			Ext.util.Cookies.set("mailAddress", mailAddress);
			// Hide the login window
			this.interface.hide();
			// Begin importing
			importFolders();
		} else if ( result.success == true ) {
			mailAddress = this.usernameInput.getValue();
			Ext.util.Cookies.set("mailAddress", mailAddress);
			// 'Ignite' the UI
			ignite();
			// Hide the login window
			this.interface.hide();
		}
	}

	this.failureHandler = function( form, action ) {
		this.wait.hide();
		handleFormFailure( form, action );
	}

	/**
	 * UI handlers.
	*/
	this.doLogin = function() {
		this.wait = Ext.Msg.wait( // Let the user know a login is taking place
			'Logging in...'
			,'Please wait...'
		);
		this.form.getForm().submit({
			url: 'api/index.php?action=doLogin'
			,params: {
				username: this.usernameInput.getValue()
				,password: this.passwordInput.getValue()
			}
			,success: this.successHandler.createDelegate( this )
			,failure: this.failureHandler.createDelegate( this )
		});
	}

	this.formValidation = function( form, valid ) {
		if ( valid === false ) {
			this.loginButton.setDisabled( true );
		} else {
			this.loginButton.setDisabled( false );
		}
	}

	this.returnKeyHandler = function( textfield, event ) { // Submit data on RETURN key press
		if ( event.getCharCode() === 13 ) {
			this.doLogin();
		}
	}

	/**
	 * UI objects.
	*/
	this.usernameInput = new Ext.form.TextField({
		fieldLabel: 'Username'
		,name: 'username'
		,allowBlank: false
		,width: 180
		,tabIndex: 1
		,enableKeyEvents: true
		,listeners: {
			keyup: this.returnKeyHandler.createDelegate( this )
		}
	});

	this.passwordInput = new Ext.form.TextField({
		fieldLabel: 'Password'
		,width: 180
		,inputType: 'password'
		,name: 'password'
		,allowBlank: false
		,tabIndex: 2
		,enableKeyEvents: true
		,listeners: {
			keyup: this.returnKeyHandler.createDelegate( this )
		}
	});

	this.loginButton = new Ext.Button({
		text: 'Login'
		,tabIndex: 3
		,handler: this.doLogin.createDelegate( this )
	});

	this.form = new Ext.form.FormPanel({
		border: false
		,frame: true
		,padding: '5px 5px 5px 5px'
		,labelAlign: 'right'
		,monitorValid: true
		,labelWidth: 70
		,items: [
			this.usernameInput
			,this.passwordInput
		]
		,buttons: [
			this.loginButton
		]
		,listeners: {
			clientvalidation: this.formValidation.createDelegate( this )
		}
	});

	this.interface = new Ext.Window({
		title: 'dracMail Webmail login'
		,height: 130
		,width: 300
		,modal: true
		,resizable: false
		,closable: false
		,layout: 'fit'
		,items: this.form
	});

	return this;
}
