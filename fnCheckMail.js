/**
 * Function for checking new email.
*/
var synchLock = false;
var checkMail;
function fnCheckMail( interval, forceReload ) {
	// Cancel any previous request, so we don't have multiple requests running in the same time.
	if ( typeof checkMail !== "undefined" ) {
		checkMail.cancel();
	}
	if ( synchLock == true ) {
		return; // A synch in progress. Cancel.
	}
	checkMail = new Ext.util.DelayedTask( function() {
		synchLock = true;
		mainUI.statusLabel.showBusy( "Checking mail..." );
		/* Request a Synch */
		checkMail.cancel();
		Ext.Ajax.request({
			url: fnXSRF('action=checkMail')
			,timeout: 600000
			,success: function( response ) {
				var response = Ext.util.JSON.decode( response.responseText );
				var reloadData = false;
				// Silently update tree node
				for ( var i = 0; i < response.length; i++ ) {
					var node = mailFolders.interface.root.findChild( "folder_id", response[i].folder_id );
					if ( response[i].text != node.text ) { // Check if we have any new data
						node.setText( response[i].text );
						reloadData = true;
					}
				}
				if ( forceReload == false ) { // Skip, if requested
					reloadData = false;
				}
				mainUI.statusLabel.clearStatus({
					useDefaults:true
				});
				synchLock = false;
				fnCheckMail( 12000 );
				if ( reloadData == true || forceReload == true ) { // If new data, reload the grid
					// Reload the active mail panel ( if any )
					var activeTab = mainUI.centerTabPanel.getActiveTab();
					if ( typeof activeTab !== "undefined" && activeTab !== null ) {
						if ( activeTab.registerAsMail == true ) {
							activeTab.mailGrid.getStore().reload();
						}
					}
				}
			}
		});
	});

	if ( typeof interval == "undefined" ) {
		interval = 1000;
	}

	checkMail.delay( interval ); // Check mail every 10 minutes ( default )
}