function objUIMenu() {
	this.toolbar = new Ext.Toolbar({
		height: 25
		,items: [{
				text: 'Compose'
				,iconCls: 'composeMail'
				,handler: function() {
					var composer = new objComposer().interface;
					mainUI.centerTabPanel.add( composer );
					mainUI.centerTabPanel.setActiveTab( composer );
				}
			},'-',{
				text: 'Synchronize'
				,iconCls: 'synchronizeMail'
				,handler: function() {
					fnCheckMail( 1 ); // Check mail now.
				}
			}
			, '->', {
			text: 'Logout'
			,iconCls: 'logout'
			,handler: function() {
				Ext.Ajax.request({
					url: fnXSRF('action=logout')
					,success: function( response ) {
						document.location.reload(); // Refresh page 
					}
				});
			}
	        }, ' ']
	});

	return this;
}