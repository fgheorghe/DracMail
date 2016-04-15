var themeChanger = {};

themeChanger.create = function() {
	this.closeWindow = function() {
		this.interface.hide();
	}
	
	this.bbar = {
		items: ['->', {
			text: 'Close'
			,handler: this.closeWindow.createDelegate( this )
		}]
	}

	this.interface = new Ext.Window({
		modal: true
		,title: 'Change Theme'
		,minimizable: false
		,maximizable: false
		,width: 230
		,resizable: false
		,height: 90
		,items: [ new Ext.form.FormPanel({
			items: new objThemes().dropDown
			,padding: '5px'
			,frame: false
			,border: false
		})]
		,bbar: this.bbar
	});
}

themeChanger.menuHandler = function() {
	this.create();
	this.interface.show();
	return false; // This is a window, prevent the menu from being selected
}