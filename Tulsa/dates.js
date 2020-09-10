/*
	Ravil Mukhamedov
	17 October 2014

   Function List:
   showDateTime(time)
      Returns the date in a text string formatted as:
      mm/dd/yyyy at hh:mm:ss am

   changeYear(today, holiday)
      Changes the year value of the holiday object to point to the
      next year if it has already occurred in the present year

   countdown(stop, start)
      Displays the time between the stop and start date objects in the
      text format:
      dd days, hh hrs, mm mins, ss secs
*/

function showDateTime(time) {
   var date = time.getDate();
   var month = time.getMonth()+1;
   var year = time.getFullYear();

   var second = time.getSeconds();
   var minute = time.getMinutes();
   var hour = time.getHours();

   var ampm = (hour < 12) ? " a.m." : " p.m.";
   hour = (hour > 12) ? hour - 12 : hour;
   hour = (hour == 0) ? 12 : hour;

   minute = minute < 10 ? "0"+minute : minute;
   second = second < 10 ? "0"+second : second;

   return month+"/"+date +"/"+year+" at "+hour+":"+minute+":"+second+ampm;
}

function changeYear(today, holiday){
	var year = today.getFullYear();
	holiday.setFullYear(year);
	if(holiday < today){
		year++;}
	holiday.setFullYear(year);
}

function countdown(start, stop){
	var time = stop - start;
	var days = Math.floor(time/86400000);
	var hrs = Math.floor((time%86400000)/3600000);
	var mins = Math.floor(((time%86400000)%3600000)/60000);
	var secs = Math.round((((time%86400000)%3600000)%60000)/1000);
	
	return days+" days, "+hrs+" hrs, "+mins+" mins, "+secs+" secs";
}




