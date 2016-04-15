applications['Notepad'] = {
	title: 'Notepad' // Application title
	,author: 'Grosan Flaviu Gheorghe' // Application author name
	,version: '0.1' // Application version
	,menu:  { // Optional: Application menu entry
		text: 'Notepad'
		,leaf: true
		,id: 'notepadApplicationMenuEntry'
		,icon: 'applications/Notepad/ico/accessories-text-editor.png'
		,listeners: {
			click: notepadApplication.menuHandler.createDelegate( notepadApplication )
		}
	}
};