/**
 * Sample application code
*/
var sampleApplication = {}; // Main object

sampleApplication.create = function() {
	this.interface = new Ext.Panel({
		title: 'Sample Application'
		,cls: 'sampleApplicationStyle'
		,html: 'This is a Sample Application.'
		,id: 'sampleApplication'
		,closable: true
	});

	return this.interface;
}

sampleApplication.handleOkButton = function() {
	mainUI.centerTabPanel.add( this.interface );
	mainUI.centerTabPanel.setActiveTab( this.interface.id );
}

sampleApplication.menuHandler = function() { // Optional application menu handler
	this.create();

	Ext.Msg.alert( 'Hello', 'Hello world: this is a sample application!', this.handleOkButton.createDelegate( this ) );
}