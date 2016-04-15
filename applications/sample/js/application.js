applications['Sample Application'] = {
	title: 'Sample Application' // Application title
	,author: 'Your Name' // Application author name
	,version: '0.1' // Application version
	,description: 'Sample dracMail Application' // Optional: Application description
	,url: 'http://your.link.tld/' // Optional: Application URL
	,contact: 'your@email.tld' // Optional: Author contact email
	,menu:  { // Optional: Application menu entry
		text: 'Sample Application'
		,leaf: true
		,icon: 'applications/sample/ico/preferences-system-windows.png' // Optional menu entry icon
		,id: 'sampleApplicationMenuEntry'
		,listeners: {
			click: sampleApplication.menuHandler.createDelegate( sampleApplication )
		}
	}
};