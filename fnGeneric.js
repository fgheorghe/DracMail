/**
 * Provides generic functions.
*/

/**
 * Generic form failure handler
*/
function handleFormFailure( form, action ) {
	switch ( action.failureType ) {
		case Ext.form.Action.CLIENT_INVALID:
			Ext.Msg.alert('Failure', 'Form fields may not be submitted with invalid values');
			break;
		case Ext.form.Action.CONNECT_FAILURE:
			Ext.Msg.alert('Failure', 'Ajax communication failed');
			break;
		case Ext.form.Action.SERVER_INVALID:
			Ext.Msg.alert('Failure', action.result.msg);
			break;
	}
}

/**
 * Generic function for loading the UI
*/
function ignite() {
	/**
	 * Prepare UI Menu
	*/
	UIMenu = new objUIMenu();
	applicationMenu = new objApplicationMenu();
	/**
		* Prepare the mail folders.
	*/
	mailFolders = new objMailFolders({
		dataUrl: fnXSRF('action=listFolders')
	});
	/**
		* Build the main UI.
	*/
	mainUI = new objMainUI({
		westRegion: {
			items: [
				mailFolders.interface
				,applicationMenu.interface
			]
		}
		,UIMenu: UIMenu
	});

	fnCheckMail();
}

/**
 * Generic function for importing email "stuff"
*/
function importFolders() {
	objSynch();
}

/**
 * Generic rendering functions.
*/
function dateRender( value ) {
	return Ext.util.Format.date( new Date(value * 1000), 'F j, Y, g:i a' );
}

function sizeRender( value ) {
	if ( value >= 1073741824 ) { // Gb
		return ( Ext.util.Format.number( value / 1073741824, '?0,000.00?' ) ) + " Gb";
	} else if ( value >= 1048576 ) { // Mb
		return ( Ext.util.Format.number( value / 1048576, '?0,000.00?' ) ) + " Mb";
	} else if ( value >= 1024 ) { // Kb
		return ( Ext.util.Format.number( value / 1024, '?0,000.00?' ) ) + " Kb";
	} else { // Bytes
		return ( Ext.util.Format.number( value, '?0,000.00?' ) ) + " bytes";
	}
}

/* Format reply content */
function replyContentRender( date, sender, content ) {
	var startText = "<br/><br/><p>On " + date +", " + sender + " wrote: </p>";
	return startText + "<blockquote style=\"border-left: 1px solid gray; padding-left: 4px;\">" + content + "</blockquote>";
}

function getRecipients( address ) {
	var recipients = address.split("; ");
	var result = "";
	var j = 0;
	for ( var i = 0; i < recipients.length; i++ ) {
		if ( recipients[i].toLowerCase() == mailAddress.toLowerCase() ) {
			continue;
		}
		if ( j != 0 ) {
			result += "; ";
		}
		result += recipients[i];
		j++;
	}
	return result;
}

function formatAttachments( attachments, message_id, folder_id ) {
	var attachmentsList = attachments.split(",");
	attachments = "";
	for ( var i = 0; i < attachmentsList.length; i++ ) {
		if ( i != 0 ) {
			attachments += ", ";
		}
		attachments += "<a href='" + fnXSRF('action=fetchAttachment&folder_id=' + folder_id + '&message_id=' + message_id + '&filename=' + encodeURI( encodeURI( Ext.util.Format.htmlDecode( attachmentsList[i] ) ) ).replace(/=/g,"%3D").replace(/&/g,"%26") ) + "' target='_blank'>" + attachmentsList[i] + "</a>";
	}
	return attachments;
}
