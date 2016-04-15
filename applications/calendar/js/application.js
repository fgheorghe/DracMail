applications['Calendar Application'] = {
	title: 'Calendar Application' // Application title
	,author: 'Grosan Flaviu Gheorghe' // Application author name
	,version: '0.1' // Application version
	,menu:  {
		text: 'Calendar'
		,leaf: true
		,icon: 'applications/calendar/ico/office-calendar.png'
		,id: 'calendarApplicationMenuEntry'
		,listeners: {
			click: calendarApplication.menuHandler.createDelegate( calendarApplication )
		}
	}
};