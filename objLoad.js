/**
 * Loads the user interface, checks for an existing login and 'glues' items toghether.
*/
function objLoad() {
	this.loginWindow = objLoginWindow();

	this.loadMask = new Ext.LoadMask( Ext.getBody(), {
		msg: 'Loading...'
	})

	/**
	 * Handle login check answer.
	*/
	this.checkLogin = function( response ) {
		if ( response.success == true ) {
			mailAddress = Ext.util.Cookies.get("mailAddress");
			this.loadMask.hide();
			// 'Ignite' the UI
			ignite();
		} else {
			this.loadMask.hide();
			this.loginWindow.interface.show();
		}
	}

	/**
	 * Initialize the load project.
	*/
	this.init = function() {
		this.loadMask.show();
		fnAjax({
			url: 'action=checkLogin'
			,success: this.checkLogin.createDelegate( this )
			,failure: this.checkLogin.createDelegate( this )
			,method: 'get'
		});
	}

	return this;
}