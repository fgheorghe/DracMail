var calendarApplication = {}; // Main object

calendarApplication.create = function() {
	this.requestFailure = function() {

	}

	this.requestSucess = function( response ) {
		try {
			this.store.loadData( Ext.util.JSON.decode( response.responseText ) );
		} catch ( ex ) {
			this.requestFailure();
		}
	}

	this.refresh = function() {
		Ext.Ajax.request({
			url: fnXSRF('application=calendar&action=load&id=' + id )
			,success: this.requestSucess.createDelegate( this )
			,failure: this.requestFailure.createDelegate( this )
		});
	}

	this.genericRequestFailure = function() {

	}

	this.genericRequest = function( type, object ) {
		Ext.Ajax.request({
			url: fnXSRF('application=calendar&action=' + type )
			,method: 'POST'
			,params: object
			,failure: this.genericRequestFailure.createDelegate( this )
		});
	}

	this.eventupdate = function( store, record ) { // Also called when moving an event
		var updateData = {
			id: record.data.EventId
			,cid: record.data.CalendarId
			,start: record.data.StartDate
			,end: record.data.EndDate
			,notes: record.data.Notes
			,url: record.data.Url
			,rem: record.data.Reminder
			,title: record.data.Title
			,loc: record.data.Location
			,recur_rule: record.data.RRule
			,ad: record.data.IsAllDay
		}

		this.genericRequest( "update", updateData );
	}

	this.eventdelete = function( store, record ) {
		var deleteData = {
			id: record.data.EventId
			,cid: record.data.CalendarId
		}
		this.genericRequest( "delete", deleteData );
	}

	this.eventadd = function( store, record ) {
		var saveData = {
			start: record.data.StartDate
			,end: record.data.EndDate
			,notes: record.data.Notes
			,url: record.data.Url
			,rem: record.data.Reminder
			,title: record.data.Title
			,loc: record.data.Location
			,recur_rule: record.data.RRule
			,ad: record.data.IsAllDay
		}
		this.genericRequest( "add", saveData );
	}

	this.store = new Ext.ensible.sample.MemoryEventStore();

	this.calendar = new Ext.ensible.cal.CalendarPanel({
		border: false
		,eventStore: this.store
		,listeners: {
			afterrender: this.refresh.createDelegate( this )
			,eventadd: this.eventadd.createDelegate( this )
			,eventdelete: this.eventdelete.createDelegate( this )
			,eventmove: this.eventupdate.createDelegate( this )
			,eventupdate: this.eventupdate.createDelegate( this )
		}
	});

	this.interface = new Ext.Panel({
		title: 'Calendar'
		,layout: 'fit'
		,id: 'calendarApplication'
		,closable: true
		,items: this.calendar
	});

	return this.interface;
}

calendarApplication.menuHandler = function() {
	this.create();
	mainUI.centerTabPanel.add( this.interface );
	mainUI.centerTabPanel.setActiveTab( this.interface.id );
}