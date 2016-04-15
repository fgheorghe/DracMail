/**
 * Generic ajax function.
*/
/**
 * @param configuration
 *	Object having the folowing properties:
 *		url: url to request
 *		success: success handler
 *		failure: failure handler
 *		params: request parameters
 *		method: request method type, default to post
*/
function fnAjax( configuration ) {
	Ext.Ajax.request({
		url: fnXSRF( configuration.url )
		,params: ( typeof configuration.params !== "undefined" ? configuration.params : null )
		,method: ( typeof configuration.method !== "undefined" ? configuration.method : 'post' )
		,success: function( result ) {
			var answer = {};
			try {
				answer = Ext.util.JSON.decode( result.responseText )
			} catch ( ex ) {
				Ext.MessageBox.alert('Failed', "The returned Ajax response is not a valid JSON string."); 
			}
			// Call handler, and pass the JavaScript object
			configuration.success( answer );
		}
		,failure: function( result ) {
			var answer = {};
			try {
				answer = Ext.util.JSON.decode( result.responseText )
			} catch ( ex ) {
				Ext.MessageBox.alert('Failed', "The returned Ajax response is not a valid JSON string."); 
			}
			// Call handler, and pass the JavaScript object
			configuration.failure( answer );
		}
	});
}