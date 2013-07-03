var CalendarManager = {
	year : 0,
	month : 0,

	initialize : function( id, date ) {
		var year = date.substring(0,4);
		var month = parseInt( date.substring(5,7), 10);
		$('#' + id + ' [name=monthSelect]' ).val( month );
		$('#' + id + ' [name=yearSelect]' ).val( year );
	},

	setMonth : function( id, month ) {
		var year = $('#' + id + ' [name=yearSelect]' ).val();
		this.setDate( id, month, year );
	},
	
	setYear : function( id, year ) {
		var month = $('#' + id + ' [name=monthSelect]' ).val();
		this.setDate( id, month, year );	
	},
	
	incrementMonth : function( id ) {
		var month = $('#' + id + ' [name=monthSelect]' ).val();
		var year = $('#' + id + ' [name=yearSelect]' ).val();
		
		month++;
		if ( month > 12 ) {
			month = 1;
			year++;
		}
		this.setDate( id, month, year );	
	},
	
	decrementMonth : function( id ) {
		var month = $('#' + id + ' [name=monthSelect]' ).val();
		var year = $('#' + id + ' [name=yearSelect]' ).val();
		
		month--;
		if ( month == 0 ) {
			month = 12;
			year11;
		}
		this.setDate( id, month, year );	
	},
	
	incrementYear : function( id ) {
		var month = $('#' + id + ' [name=monthSelect]' ).val();
		var year = $('#' + id + ' [name=yearSelect]' ).val();
		
		year++;
		this.setDate( id, month, year );	
	},
	
	decrementYear : function( id ) {
		var month = $('#' + id + ' [name=monthSelect]' ).val();
		var year = $('#' + id + ' [name=yearSelect]' ).val();
		
		year--;
		this.setDate( id, month, year );	
	},
	
	setDate : function( id, month, year ) {
		month = (month < 10) ? '0' + month : month;
		this.createCookie( 'calendar-' + id, year + '-' + month + '-01', 1 ); 
		window.location.reload();
	},
	
	createCookie : function(name,value,days) {
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	},
	
	readCookie : function(name) {
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	},
	
	eraseCookie : function(name) {
		createCookie(name,"",-1);
	}

	
	
	




};
