applications['Change Password - vpopmysql'] = {
	title: 'Change Password 3' // Application title
	,author: 'Grosan Flaviu Gheorghe' // Application author name
	,version: '0.1' // Application version
	,menu:  { // Optional: Application menu entry
		text: 'Change Password'
		,leaf: true
		,icon: 'applications/ChangePassword-vpopmysql/ico/system-users.png'
		,id: 'changePasswordApplicationMenuEntry'
		,listeners: {
			click: changePassword.menuHandler.createDelegate( changePassword )
		}
	}
};