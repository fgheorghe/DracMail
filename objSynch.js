/**
 * Object for synchronizing email with the imap server
*/
function objSynch( configuration ) {
	if ( typeof configuration == "undefined" ) {
		configuration = {};
	}

	/* Handle requests for every step */
	var stepRequest = function( step, mailFolders ) {
		var folder = '';
		if ( step == 1 ) {
			var folder = mailFolders.pop();
		}
		Ext.Ajax.request({
			url: fnXSRF('action=doSynch&step=' + step + '&folder=' + folder)
			,timeout: 600000
			,success: function( response ) {
				var result = Ext.util.JSON.decode( response.responseText );
				switch ( step ) {
					case 0:
						objSynch({ step: ++step, mailFolders: result.mailFolders }); // Go to next step...
						break;
					case 1:
						if ( mailFolders.length != 0 ) {
							objSynch({ step: 1, mailFolders: mailFolders }); // End.
						} else {
							objSynch({ step: 2 }); // End.
						}
						break;
				}
			}
		});
	}

	configuration.step = ( typeof configuration.step == "undefined" ) ? 0 : configuration.step;

	// Step 0, display progess and import folders
	switch ( configuration.step ) {
		case 0:
			Ext.Msg.progress(
				'Please wait...'
				,'Synchronizing'
			);

			Ext.Msg.updateProgress( 0, "Listing Mail Folders...", 'Synchronizing' );
			stepRequest( 0 );
			break;
		case 1:
			Ext.Msg.updateProgress( 0.5, "Importing Folder: " + configuration.mailFolders[configuration.mailFolders.length - 1] + "...", 'Synchronizing' );
			stepRequest( configuration.step, configuration.mailFolders );
			break;
		case 2:
			Ext.Msg.hide();
			// Load the UI.
			ignite();
			break;
	}
}