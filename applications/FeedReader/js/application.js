applications['Feed Reader'] = {
	title: 'Feed Reader' // Application title
	,author: 'Grosan Flaviu Gheorghe' // Application author name
	,version: '0.1' // Application version
	,menu:  { // Optional: Application menu entry
		text: 'News Feeds'
		,leaf: true
		,icon: 'applications/FeedReader/ico/internet-news-reader.png'
		,id: 'feedReaderMenuEntry'
		,listeners: {
			click: feedReader.menuHandler.createDelegate( feedReader )
		}
	}
};