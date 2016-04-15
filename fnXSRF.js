/**
 * Construct a valid url, including the XSRF random number
*/
function fnXSRF( url ) {
	return "api/index.php?" + url + "&randomNumber=" + Ext.util.Cookies.get("randomNumber");
}