applications['Theme Changer'] = {
	title: 'Theme Changer' // Application title
	,author: 'Grosan Flaviu Gheorghe' // Application author name
	,version: '0.1' // Application version
	,description: 'Theme Changer application.' // Optional: Application description
	,menu:  { // Optional: Application menu entry
		text: 'Change Theme'
		,leaf: true
		,id: 'themeChanger'
		,icon: 'applications/ThemeChanger/ico/preferences-desktop-theme.png'
		,listeners: {
			click: themeChanger.menuHandler.createDelegate( themeChanger )
		}
	}
};