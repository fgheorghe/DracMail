/**
 * Handles UI themes.
*/
function objThemes() {
	this.changeTheme = function( combo ) {
		var theme = combo.getValue();
		themeTitle = theme;
		setActiveStyleSheet( theme );
	}

	this.dropDown = new Ext.form.ComboBox({
		typeAhead: true
		,triggerAction: 'all'
		,allowBlank: false
		,forceSelection: true
		,lazyRender: true
		,width: 90
		,mode: 'local'
		,store: new Ext.data.ArrayStore({
			id: 0
			,fields: [
				'type'
				,'displayText'
			],
			data: [
				[ "honey", 'Honey']
				,[ "blue", 'Blue']
				,[ "snake", 'Snake']
				,[ "grey2", 'Light Grey']
				,[ "grey3", 'Dark Grey']
			]
		})
		,valueField: 'type'
		,displayField: 'displayText'
		,fieldLabel: 'Select Theme'
		,listeners: {
			collapse: this.changeTheme.createDelegate( this )
		}
	});

	this.dropDown.setValue( themeTitle );

	return this;
}