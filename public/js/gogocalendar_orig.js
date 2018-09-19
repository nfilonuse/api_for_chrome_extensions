

 (function ( $ ) {
	$.fn.gogoCalendar1 = function( options ) {
		var self = $(this);
		var settings = {};
		var currDate = new Date();
    var defDate = null;
    var todayDate = new Date();
    var weekNames = ["su", "mo", "tu", "we", "th", "fr", "sa"];
    var weekNamesHasAlreadySorted = false;

    Array.prototype.move = function (old_index, new_index) {
        while (old_index < 0) {
            old_index += this.length;
        }
        while (new_index < 0) {
            new_index += this.length;
        }
        if (new_index >= this.length) {
            var k = new_index - this.length;
            while ((k--) + 1) {
                this.push(undefined);
            }
        }
        this.splice(new_index, 0, this.splice(old_index, 1)[0]);
        return this;
    };

    var getDayIndexOfWeek = function() {
          for (var i = 0; i < weekNames.length; i++) {
                if (weekNames[i] == settings.startWeek.toLowerCase()) {
                      return i;
                }
          }
          return 0;
    }

		var weekCount = function(year, month_number) {
      var firstOfMonth = new Date(year, month_number-1, 1);
      var lastOfMonth = new Date(year, month_number, 0);
      var used = firstOfMonth.getDay() + lastOfMonth.getDate();
      return Math.ceil( used / 7);
		}


    var getWeekOfMonth = function(month, year) {
      var   weeks = [],
            firstDate = new Date(year, month, 1),
            lastDate = new Date(year, month+1, 0),
            numDays = lastDate.getDate(),
            startDayWeek = getDayIndexOfWeek();

      var lastDateOfPrevMonth = new Date(year, month, 0);
      var prevDate = lastDateOfPrevMonth.getDate() - firstDate.getDay();

      var firstDateOfNextMonth = new Date(year, month + 1, 1);
      var nextDate = firstDateOfNextMonth.getDate();

      var date = 1;
      while (date <= numDays) {
            var i = 0;

            var tempWeek = [];
            while (i < 7) {


                  if (weeks.length == 0) {

                        if (i >= firstDate.getDay()) {
                              tempWeek.push(date);

                              date++;
                        } else {
                              prevDate++;
                              tempWeek.push(prevDate);
                        }

                  } else {

                        if (date <= numDays) {
                              tempWeek.push(date);

                              date++;
                        } else {
                              tempWeek.push(nextDate++);
                        }

                  }

                  i++;
            }

            weeks.push(tempWeek);
      }

      return weeks;
    }


      var sortingBySelectedDay = function(weeks, month, year) {
      var startDayWeek = getDayIndexOfWeek();
      var lastDateOfPrevMonth = new Date(year, month, 0);
      var prevDate = lastDateOfPrevMonth.getDate();
      var lastDate = new Date(year, month+1, 0).getDate();

      function isset(arr, val) {
            var found = false;
            for (var m = 0; m < arr.length; m++) {
                  if (arr[m] == val) {
                        found = true;
                        break;
                  }
            }

            return found;
      }


      if (startDayWeek > 0) {
            var newArr = [];

            var nWeeks = weeks.length;
            for (var i = 0; i < nWeeks; i++) {
                  var tempArr = [];


                  if (i == 0 && newArr.length == 0) {

                        if (weeks[i][0] == 1) {

                              var startPrevDate = prevDate - (7 - startDayWeek - 1);
                              while (startPrevDate <= prevDate) {
                                    tempArr.push(startPrevDate);

                                    startPrevDate++;
                              }

                              // get next date
                              for (var j = 0; j < weeks[i].length; j++) {
                                    if (tempArr.length < weeks[i].length) {

                                          var val = weeks[i][j];
                                          var isPush = isset(tempArr, val);

                                          if (!isPush) {
                                                tempArr.push(val);
                                          }

                                    }
                              }
                        } else {
                              for (var j = 0; j < weeks[i].length; j++) {
                                    if (tempArr.length < weeks[i].length) {

                                          if (typeof weeks[i][j + startDayWeek] !== 'undefined') {
                                                tempArr.push(weeks[i][j + startDayWeek]);
                                          } else {
                                                if (typeof weeks[i + 1] !== 'undefined') {

                                                      for (var k = 0; k < weeks[i + 1].length; k++) {

                                                            if (tempArr.length < weeks[i].length) {
                                                                  var val = weeks[i + 1][k];
                                                                  var isPush = isset(tempArr, val);

                                                                  if (!isPush) {
                                                                        tempArr.push(val);
                                                                  }
                                                            }

                                                      }

                                                }
                                          }

                                    }
                              }
                        }
                  } else {

                        for (var newI = i; newI >= 0; newI--) {
                              var found = false;
                              for (var j = 0; j < weeks[newI].length; j++) {
                                    var newArrRow = newArr.length - 1;
                                    var newArrCol = newArr[newArrRow].length - 1;

                                    if (newArr[newArrRow][newArrCol] == weeks[newI][j]) {
                                          found = true;
                                    }

                                    if (found == true) {
                                          if (typeof weeks[newI][j + startDayWeek] !== 'undefined') {

                                                if (tempArr.length < weeks[newI].length) {
                                                      var val = weeks[newI][j + startDayWeek];
                                                      var isPush = isset(tempArr, val);

                                                      if (!isPush) {
                                                            tempArr.push(weeks[newI][j + startDayWeek]);

                                                      }
                                                }

                                          } else {
                                                if (typeof weeks[newI + 1] !== 'undefined') {
                                                      for (var k = 0; k < weeks[newI + 1].length; k++) {

                                                            if (tempArr.length < weeks[newI].length) {
                                                                  var val = weeks[newI + 1][k];
                                                                  var isPush = isset(tempArr, val);

                                                                  if (!isPush) {
                                                                        tempArr.push(val);

                                                                  }
                                                            }

                                                      }
                                                }
                                          }
                                    }
                              }
                        }

                  }

                  newArr.push(tempArr);
            }


            var found = false;
            for (var i = 0; i < newArr.length; i++) {
                  for (var j = 0; j < newArr[i].length; j++) {
                        if (newArr[i][j] == lastDate) {

                              found = true;
                              break;

                        }
                  }

                  if (found == true) {
                        break;
                  }
            }

            var newArrRow = newArr.length - 1;

            if (found == false) {
                  var newArrCol = newArr[newArrRow].length - 1;
                  var lastDateInNewArr = newArr[newArrRow][newArrCol];

                  var tempArr = [];
                  while (lastDateInNewArr < lastDate) {
                        lastDateInNewArr++;

                        tempArr.push(lastDateInNewArr);
                  }

                  if (tempArr.length < 7) {
                        var limit = 7 - tempArr.length;
                        var date = 1;

                        while (date <= limit) {
                              tempArr.push(date);

                              date++;
                        }
                  }

                  newArr.push(tempArr);
            } else {
                  var newArrCol = newArr[newArrRow].length - 1;
                  var lastDateInNewArr = newArr[newArrRow][newArrCol];
                  var colLeft = 7 - (newArrCol + 1);

                  if (lastDateInNewArr > 7) {
                    lastDateInNewArr = 1;
                  } else {
                    lastDateInNewArr = lastDateInNewArr + 1;
                  }

                  while (colLeft > 0) {
                    newArr[newArrRow].push(lastDateInNewArr);

                    lastDateInNewArr++;
                    colLeft--;
                  }


            }

            return newArr;
      }

      return weeks;
    }


		var draw = function() {
          var m = currDate.getMonth();
          var d = currDate.getDate();
          var y = currDate.getFullYear();

          var dates = getWeekOfMonth(m, y);
          for (var i = 0; i < dates.length; i++) {
                var string = "";
                for (var j = 0; j < dates[i].length; j++) {
                      string += dates[i][j] + " ";
                }

          }


          dates = sortingBySelectedDay(dates, m, y);
          for (var i = 0; i < dates.length; i++) {
                var string = "";
                for (var j = 0; j < dates[i].length; j++) {
                      string += dates[i][j] + " ";
                }

          }


          var headerMonth = settings.monthNames[m];
          if (m > 1) {
          var headerMonthPrev = settings.monthNames[m-1];
                }else{
          var headerMonthPrev = settings.monthNames[11];
          }
          if (m < 11) {
          var headerMonthNext = settings.monthNames[m+1];
          }else{
           var headerMonthNext = settings.monthNames[0];
          }


          var headerGroup = $("<div class='month-choose'></div>");
          var headerMonthGroup = $("<div class='mc-current-month'></div>");
          headerMonthGroup.append( headerMonth );
          var prevInactive = false;
          var minDate = null;
          if (typeof settings.minDate !== 'undefined') {
          	var minDateArr = settings.minDate.split('-');
          	minDate = new Date(minDateArr[0], minDateArr[1] - 1, minDateArr[2]);

          	if (minDate.getFullYear() >= y) {
          		if (minDate.getMonth() >= m) {
          			prevInactive = true;
          		}
          	}
          }

          var nextInactive = false;
          var maxDate = null;
          if (typeof settings.maxDate !== 'undefined') {
          	var maxDateArr = settings.maxDate.split('-');
          	maxDate = new Date(maxDateArr[0], maxDateArr[1] - 1, maxDateArr[2]);

          	if (maxDate.getFullYear() <= y) {
          		if (maxDate.getMonth() <= m) {
          			nextInactive = true;
          		}
          	}
          }

          var prevLinkGroup = $("<div class='mc-arrow-left mc-prev-month'><a href='javascript:void(0);'></a></div><div class='mc-prev-month prev-month'><a href='javascript:void(0);'>"+headerMonthPrev+"</a></div><div class='mc-free-space'>&nbsp;</div>");
          var nextLinkGroup = $("<div class='mc-free-space'>&nbsp;</div><div class='mc-next-month next-month'><a href='javascript:void(0);'>"+headerMonthNext+"</a></div><div class='mc-arrow-right mc-next-month'><a href='javascript:void(0);'></a></div><div class='clear'></div>");

          if (prevInactive) {
          	prevLinkGroup.addClass("gogocalendar-inactive");
            prevLinkGroup.removeClass("mc-prev-month");
          	prevLinkGroup.removeAttr("id");
          }

          if (nextInactive) {
          	nextLinkGroup.addClass("gogocalendar-inactive");
            nextLinkGroup.removeClass("mc-next-month");
          	nextLinkGroup.removeAttr("id");
          }

          headerGroup.append(prevLinkGroup);
          headerGroup.append(headerMonthGroup);
          headerGroup.append(nextLinkGroup);


          var bodyGroup = $("<div id='gogocalendar-body' class='gogocalendar-body'></div>");
          var tableGroup = $("<div class='day-choose'></div>");

          var weekName = settings.dayNames;
          if (settings.dayUseShortName == true) {
          	weekName = settings.dayNamesShort;
          }


          var dayIndex = getDayIndexOfWeek();
          if (weekNamesHasAlreadySorted == false) {
                var oldIndex = null;
                var newIndex = 0;
                for (var i = 0; i < weekName.length; i++) {
                      if (i >= dayIndex) {
                            if (oldIndex == null) {
                                  oldIndex = i;
                            }

                            weekName.move(oldIndex, newIndex);
                            oldIndex++;
                            newIndex++;
                      }
                }

                weekNamesHasAlreadySorted = true
          }


          var sundayIndex = (dayIndex == 0) ? 0 : 7 - dayIndex;
          var saturdayIndex = 6 - dayIndex;

          var tableHeadGroup = $("<span></span>");
          var numberdayRowGroup = $("<div class='number-day'></div>");
          var weekNameLength = weekName.length;
          for (var i = 0; i < weekNameLength; i++) {
          	numberdayRowGroup.append("<div>"+ weekName[i] +"</div>");
          }


          var bodyGroup = $("<div class='day-choose'></div>");
          var totalWeeks = weekCount(y, m + 1);

          var totalDaysInWeeks = 7;
          var startDate = 1;

          var firstDayOfMonth = new Date(y, m, 1);
      		var lastDayOfMonth = new Date(y, m + 1, 0);

      		var lastDateOfPrevMonth = new Date(y, m, 0);
      		var prevDate = lastDateOfPrevMonth.getDate() - firstDayOfMonth.getDay() + 1;

      		var firstDateOfNextMonth = new Date(y, m + 1, 1);
      		var nextDate = firstDateOfNextMonth.getDate();

      		var limitMinDate = 0;
      		if (minDate != null) {
      			limitMinDate = minDate.getDate();
      		}

      		var limitMaxDate = 0;
      		if (maxDate != null) {
      			limitMaxDate = maxDate.getDate();
      		}

          var todayTitle = 'today';
          var defaultDateTitle = 'default date';
          if (typeof settings.dataTitles !== 'undefined') {
                if (typeof settings.dataTitles.defaultDate !== 'undefined') {
                      defaultDateTitle = settings.dataTitles.defaultDate;
                }

                if (typeof settings.dataTitles.today !== 'undefined') {
                      todayTitle = settings.dataTitles.today;
                }
          }

          var sundayIndex = (dayIndex == 0) ? 0 : 7 - dayIndex;
          var saturdayIndex = (dayIndex == 0) ? 7 - 1 : 7 - (dayIndex + 1);
          var nDates = dates.length;
          for (var i = 0; i < nDates; i++) {
            var tableBodyRowGroup = $("<div></div>");

            var nDate = dates[i].length;
            for (var j = 0; j < nDate; j++) {

              var date = dates[i][j];
              var month = m + 1;
              var year = y;

              var colDateClass = "";
              var colDateDataAttr = "";

              var showCalendarClick = true;


              if (i == 0) {
                if (dates[i][j] > 7) {
                  showCalendarClick = false;
                  month = month - 1;

                  if (month <= 0) {
                    month = 12;
                    year = year - 1;
                  }
                }
              }


              if (i == nDates - 1) {
                if (dates[i][j] <= 7) {
                  showCalendarClick = false;
                  month = month + 1;

                  if (month >= 13) {
                    month = 1;
                    year = year + 1;
                  }
                }
              }


              if (todayDate.getFullYear() == year && (todayDate.getMonth() + 1) == month && todayDate.getDate() == date) {
                colDateClass = ' today-date ';
                colDateDataAttr = "data-title='"+ todayTitle +"'";
              }


              var z_content=0;

              if (defDate != null && defDate.getFullYear() == year && (defDate.getMonth() + 1) == month && defDate.getDate() == date) {
                colDateClass = ' default-date ';
                        z_content=1;
                colDateDataAttr = "data-title='"+ defaultDateTitle +"'";
              }

              if (j == sundayIndex || j == saturdayIndex) {
                colDateClass += ' holiday ';
              }


              var colDate = "<div id='calendarClick1' class='"+ colDateClass +" "+ ((showCalendarClick == true) ? 'calendarClick1' : '') + " " +((date == 1) ? 'calendar-date' : '') + "'  data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type1' "+((date == 1 ||  z_content==1 ) ? 'data-content="'+settings.monthNamesShort12[month]+' '+year+'"' : '')+ "   data-year='"+ year +"' data-month='"+ settings.monthNamesShort12[month] +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";

              if (minDate != null) {
                var myCurrentDate = new Date(year, month - 1, date);
                if (minDate > myCurrentDate) {
                  colDate = "<div class='"+ colDateClass +"' data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type4' data-year='"+ year +"'  data-month='"+ settings.monthNamesShort12[month] +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";
                }
              }

              if (maxDate != null) {
                var myCurrentDate = new Date(year, month - 1, date);
                if (maxDate < myCurrentDate) {
                  colDate = "<div class='"+ colDateClass +"' data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type4' data-year='"+ year +"'  data-month='"+ settings.monthNamesShort12[month]  +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";
                }
              }

              bodyGroup.append(colDate);
            }
          }

    			self.html("");
    			self.append(headerGroup);
    			self.append(bodyGroup);
          self.append('<div class="clear"></div>');
          self.append(numberdayRowGroup);

		}




		var nextMonth = function() {
			var firstDateOfNextMonth = new Date(currDate.getFullYear(), currDate.getMonth() + 1, 1); // get fist day of next month
			var date = firstDateOfNextMonth.getDate();
			var month = firstDateOfNextMonth.getMonth();
			var year = firstDateOfNextMonth.getFullYear();
			currDate = new Date(year, month, date);
			draw();
		}

		var prevMonth = function() {
			var firstDateOfPrevMonth = new Date(currDate.getFullYear(), currDate.getMonth() - 1, 1); // get fist day of previous month
			var date = firstDateOfPrevMonth.getDate();
			var month = firstDateOfPrevMonth.getMonth();
			var year = firstDateOfPrevMonth.getFullYear();
			currDate = new Date(year, month, date);
			draw();
		}

		var triggerAction = function() {
		    $('body').on('click', '#calendarClick1', function(){
  				var selectedDate = $(this).data('date');
  				var selectedMonth = $(this).data('month');
  				var selectedYear = $(this).data('year');
  				settings.dayClick1.call(this, new Date(selectedYear, selectedMonth - 1, selectedDate), self);
       });

		    $('#gogocalendar-pickup').on('click', '.mc-prev-month', function() {
		    	prevMonth();
		    });

		    $('#gogocalendar-pickup').on('click', '.mc-next-month', function() {
		    	nextMonth();
		    });
		}

		return {
			build: function() {
                        settings = $.extend( {}, $.fn.gogoCalendar1.defaults, options );

				if (typeof settings.defaultDate !== 'undefined') {
                              var defaultDateArr = settings.defaultDate.split('-');
                              currDate = new Date(defaultDateArr[0], defaultDateArr[1] - 1, defaultDateArr[2]);
                              defDate = currDate;
                        }
				draw();
				triggerAction();
			},
      update: function(options) {
            settings = $.extend(settings, options);

            // replace with defaultDate when exist
            if (typeof settings.defaultDate !== 'undefined') {
                  var defaultDateArr = settings.defaultDate.split('-');
                  currDate = new Date(defaultDateArr[0], defaultDateArr[1] - 1, defaultDateArr[2]);
                  defDate = currDate;
            }

            draw();
      }
		}
	}

  $.fn.gogoCalendar1.defaults = {
        monthNames: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
        monthNamesShort: [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ],
        monthNamesShort12: [ '','Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ],
        dayNames: [ 'su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'],
        dayNamesShort: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ],
        dayUseShortName: false,
        monthUseShortName: false,
        showNotes: false,
        startWeek: 'sunday',
        dayClick1: function(date, view) {}
  };

} ( jQuery ));




//
//-----------------------------------------------------------------------------------------------------------------------------------------
 (function ( $ ) {
  $.fn.gogoCalendar2 = function( options ) {
    var self = $(this);
    var settings = {};
    var currDate = new Date();
    var defDate = null;
    var todayDate = new Date();
    var weekNames = ["su", "mo", "tu", "we", "th", "fr", "sa"];
    var weekNamesHasAlreadySorted = false;

    Array.prototype.move = function (old_index, new_index) {
        while (old_index < 0) {
            old_index += this.length;
        }
        while (new_index < 0) {
            new_index += this.length;
        }
        if (new_index >= this.length) {
            var k = new_index - this.length;
            while ((k--) + 1) {
                this.push(undefined);
            }
        }
        this.splice(new_index, 0, this.splice(old_index, 1)[0]);
        return this;
    };

    var getDayIndexOfWeek = function() {
          for (var i = 0; i < weekNames.length; i++) {
                if (weekNames[i] == settings.startWeek.toLowerCase()) {
                      return i;
                }
          }
          return 0;
    }

    var weekCount = function(year, month_number) {
      var firstOfMonth = new Date(year, month_number-1, 1);
      var lastOfMonth = new Date(year, month_number, 0);
      var used = firstOfMonth.getDay() + lastOfMonth.getDate();
      return Math.ceil( used / 7);
    }


    var getWeekOfMonth = function(month, year) {
      var   weeks = [],
            firstDate = new Date(year, month, 1),
            lastDate = new Date(year, month+1, 0),
            numDays = lastDate.getDate(),
            startDayWeek = getDayIndexOfWeek();

      var lastDateOfPrevMonth = new Date(year, month, 0);
      var prevDate = lastDateOfPrevMonth.getDate() - firstDate.getDay();

      var firstDateOfNextMonth = new Date(year, month + 1, 1);
      var nextDate = firstDateOfNextMonth.getDate();

      var date = 1;
      while (date <= numDays) {
            var i = 0;

            var tempWeek = [];
            while (i < 7) {


                  if (weeks.length == 0) {

                        if (i >= firstDate.getDay()) {
                              tempWeek.push(date);

                              date++;
                        } else {
                              prevDate++;
                              tempWeek.push(prevDate);
                        }

                  } else {

                        if (date <= numDays) {
                              tempWeek.push(date);

                              date++;
                        } else {
                              tempWeek.push(nextDate++);
                        }

                  }

                  i++;
            }

            weeks.push(tempWeek);
      }

      return weeks;
    }


      var sortingBySelectedDay = function(weeks, month, year) {
      var startDayWeek = getDayIndexOfWeek();
      var lastDateOfPrevMonth = new Date(year, month, 0);
      var prevDate = lastDateOfPrevMonth.getDate();
      var lastDate = new Date(year, month+1, 0).getDate();

      function isset(arr, val) {
            var found = false;
            for (var m = 0; m < arr.length; m++) {
                  if (arr[m] == val) {
                        found = true;
                        break;
                  }
            }

            return found;
      }


      if (startDayWeek > 0) {
            var newArr = [];

            var nWeeks = weeks.length;
            for (var i = 0; i < nWeeks; i++) {
                  var tempArr = [];


                  if (i == 0 && newArr.length == 0) {
                        // check first day is sunday
                        if (weeks[i][0] == 1) {
                              // calculate previous date
                              var startPrevDate = prevDate - (7 - startDayWeek - 1);
                              while (startPrevDate <= prevDate) {
                                    tempArr.push(startPrevDate);

                                    startPrevDate++;
                              }

                              // get next date
                              for (var j = 0; j < weeks[i].length; j++) {
                                    if (tempArr.length < weeks[i].length) {

                                          var val = weeks[i][j];
                                          var isPush = isset(tempArr, val);

                                          if (!isPush) {
                                                tempArr.push(val);
                                          }

                                    }
                              }
                        } else {
                              for (var j = 0; j < weeks[i].length; j++) {
                                    if (tempArr.length < weeks[i].length) {

                                          if (typeof weeks[i][j + startDayWeek] !== 'undefined') {
                                                tempArr.push(weeks[i][j + startDayWeek]);
                                          } else {
                                                if (typeof weeks[i + 1] !== 'undefined') {

                                                      for (var k = 0; k < weeks[i + 1].length; k++) {

                                                            if (tempArr.length < weeks[i].length) {
                                                                  var val = weeks[i + 1][k];
                                                                  var isPush = isset(tempArr, val);

                                                                  if (!isPush) {
                                                                        tempArr.push(val);
                                                                  }
                                                            }

                                                      }

                                                }
                                          }

                                    }
                              }
                        }
                  } else {

                        for (var newI = i; newI >= 0; newI--) {
                              var found = false;
                              for (var j = 0; j < weeks[newI].length; j++) {
                                    var newArrRow = newArr.length - 1;
                                    var newArrCol = newArr[newArrRow].length - 1;

                                    if (newArr[newArrRow][newArrCol] == weeks[newI][j]) {
                                          found = true;
                                    }

                                    if (found == true) {
                                          if (typeof weeks[newI][j + startDayWeek] !== 'undefined') {

                                                if (tempArr.length < weeks[newI].length) {
                                                      var val = weeks[newI][j + startDayWeek];
                                                      var isPush = isset(tempArr, val);

                                                      if (!isPush) {
                                                            tempArr.push(weeks[newI][j + startDayWeek]);

                                                      }
                                                }

                                          } else {
                                                if (typeof weeks[newI + 1] !== 'undefined') {
                                                      for (var k = 0; k < weeks[newI + 1].length; k++) {

                                                            if (tempArr.length < weeks[newI].length) {
                                                                  var val = weeks[newI + 1][k];
                                                                  var isPush = isset(tempArr, val);

                                                                  if (!isPush) {
                                                                        tempArr.push(val);

                                                                  }
                                                            }

                                                      }
                                                }
                                          }
                                    }
                              }
                        }

                  }

                  newArr.push(tempArr);
            }


            var found = false;
            for (var i = 0; i < newArr.length; i++) {
                  for (var j = 0; j < newArr[i].length; j++) {
                        if (newArr[i][j] == lastDate) {

                              found = true;
                              break;

                        }
                  }

                  if (found == true) {
                        break;
                  }
            }

            var newArrRow = newArr.length - 1;

            if (found == false) {
                  var newArrCol = newArr[newArrRow].length - 1;
                  var lastDateInNewArr = newArr[newArrRow][newArrCol];

                  var tempArr = [];
                  while (lastDateInNewArr < lastDate) {
                        lastDateInNewArr++;

                        tempArr.push(lastDateInNewArr);
                  }

                  if (tempArr.length < 7) {
                        var limit = 7 - tempArr.length;
                        var date = 1;

                        while (date <= limit) {
                              tempArr.push(date);

                              date++;
                        }
                  }

                  newArr.push(tempArr);
            } else {
                  var newArrCol = newArr[newArrRow].length - 1;
                  var lastDateInNewArr = newArr[newArrRow][newArrCol];
                  var colLeft = 7 - (newArrCol + 1);

                  if (lastDateInNewArr > 7) {
                    lastDateInNewArr = 1;
                  } else {
                    lastDateInNewArr = lastDateInNewArr + 1;
                  }

                  while (colLeft > 0) {
                    newArr[newArrRow].push(lastDateInNewArr);

                    lastDateInNewArr++;
                    colLeft--;
                  }


            }

            return newArr;
      }

      return weeks;
    }


    var draw = function() {
          var m = currDate.getMonth();
          var d = currDate.getDate();
          var y = currDate.getFullYear();

          var dates = getWeekOfMonth(m, y);
          for (var i = 0; i < dates.length; i++) {
                var string = "";
                for (var j = 0; j < dates[i].length; j++) {
                      string += dates[i][j] + " ";
                }

          }


          dates = sortingBySelectedDay(dates, m, y);
          for (var i = 0; i < dates.length; i++) {
                var string = "";
                for (var j = 0; j < dates[i].length; j++) {
                      string += dates[i][j] + " ";
                }

          }


          var headerMonth = settings.monthNames[m];
          if (m > 1) {
          var headerMonthPrev = settings.monthNames[m-1];
                }else{
          var headerMonthPrev = settings.monthNames[11];
          }
          if (m < 11) {
          var headerMonthNext = settings.monthNames[m+1];
          }else{
           var headerMonthNext = settings.monthNames[0];
          }


          var headerGroup = $("<div class='month-choose'></div>");
          var headerMonthGroup = $("<div class='mc-current-month'></div>");
          headerMonthGroup.append( headerMonth );
          var prevInactive = false;
          var minDate = null;
          if (typeof settings.minDate !== 'undefined') {
            var minDateArr = settings.minDate.split('-');
            minDate = new Date(minDateArr[0], minDateArr[1] - 1, minDateArr[2]);

            if (minDate.getFullYear() >= y) {
              if (minDate.getMonth() >= m) {
                prevInactive = true;
              }
            }
          }

          var nextInactive = false;
          var maxDate = null;
          if (typeof settings.maxDate !== 'undefined') {
            var maxDateArr = settings.maxDate.split('-');
            maxDate = new Date(maxDateArr[0], maxDateArr[1] - 1, maxDateArr[2]);

            if (maxDate.getFullYear() <= y) {
              if (maxDate.getMonth() <= m) {
                nextInactive = true;
              }
            }
          }

          var prevLinkGroup = $("<div class='mc-arrow-left mc-prev-month'><a href='javascript:void(0);'></a></div><div class='mc-prev-month prev-month'><a href='javascript:void(0);'>"+headerMonthPrev+"</a></div><div class='mc-free-space'>&nbsp;</div>");
          var nextLinkGroup = $("<div class='mc-free-space'>&nbsp;</div><div class='mc-next-month next-month'><a href='javascript:void(0);'>"+headerMonthNext+"</a></div><div class='mc-arrow-right mc-next-month'><a href='javascript:void(0);'></a></div><div class='clear'></div>");

          if (prevInactive) {
            prevLinkGroup.addClass("gogocalendar-inactive");
            prevLinkGroup.removeClass("mc-prev-month");
            prevLinkGroup.removeAttr("id");
          }

          if (nextInactive) {
            nextLinkGroup.addClass("gogocalendar-inactive");
            nextLinkGroup.removeClass("mc-next-month");
            nextLinkGroup.removeAttr("id");
          }

          headerGroup.append(prevLinkGroup);
          headerGroup.append(headerMonthGroup);
          headerGroup.append(nextLinkGroup);


          var bodyGroup = $("<div id='gogocalendar-body' class='gogocalendar-body'></div>");
          var tableGroup = $("<div class='day-choose'></div>");

          var weekName = settings.dayNames;
          if (settings.dayUseShortName == true) {
            weekName = settings.dayNamesShort;
          }


          var dayIndex = getDayIndexOfWeek();
          if (weekNamesHasAlreadySorted == false) {
                var oldIndex = null;
                var newIndex = 0;
                for (var i = 0; i < weekName.length; i++) {
                      if (i >= dayIndex) {
                            if (oldIndex == null) {
                                  oldIndex = i;
                            }

                            weekName.move(oldIndex, newIndex);
                            oldIndex++;
                            newIndex++;
                      }
                }

                weekNamesHasAlreadySorted = true
          }


          var sundayIndex = (dayIndex == 0) ? 0 : 7 - dayIndex;
          var saturdayIndex = 6 - dayIndex;

          var tableHeadGroup = $("<span></span>");
          var numberdayRowGroup = $("<div class='number-day'></div>");
          var weekNameLength = weekName.length;
          for (var i = 0; i < weekNameLength; i++) {
            numberdayRowGroup.append("<div>"+ weekName[i] +"</div>");
          }


          var bodyGroup = $("<div class='day-choose'></div>");
          var totalWeeks = weekCount(y, m + 1);

          var totalDaysInWeeks = 7;
          var startDate = 1;

          var firstDayOfMonth = new Date(y, m, 1);
          var lastDayOfMonth = new Date(y, m + 1, 0);

          var lastDateOfPrevMonth = new Date(y, m, 0);
          var prevDate = lastDateOfPrevMonth.getDate() - firstDayOfMonth.getDay() + 1;

          var firstDateOfNextMonth = new Date(y, m + 1, 1);
          var nextDate = firstDateOfNextMonth.getDate();

          var limitMinDate = 0;
          if (minDate != null) {
            limitMinDate = minDate.getDate();
          }

          var limitMaxDate = 0;
          if (maxDate != null) {
            limitMaxDate = maxDate.getDate();
          }

          var todayTitle = 'today';
          var defaultDateTitle = 'default date';
          if (typeof settings.dataTitles !== 'undefined') {
                if (typeof settings.dataTitles.defaultDate !== 'undefined') {
                      defaultDateTitle = settings.dataTitles.defaultDate;
                }

                if (typeof settings.dataTitles.today !== 'undefined') {
                      todayTitle = settings.dataTitles.today;
                }
          }

          var sundayIndex = (dayIndex == 0) ? 0 : 7 - dayIndex;
          var saturdayIndex = (dayIndex == 0) ? 7 - 1 : 7 - (dayIndex + 1);
          var nDates = dates.length;
          for (var i = 0; i < nDates; i++) {
            var tableBodyRowGroup = $("<div></div>");

            var nDate = dates[i].length;
            for (var j = 0; j < nDate; j++) {

              var date = dates[i][j];
              var month = m + 1;
              var year = y;

              var colDateClass = "";
              var colDateDataAttr = "";

              var showCalendarClick = true;


              if (i == 0) {
                if (dates[i][j] > 7) {
                  showCalendarClick = false;
                  month = month - 1;

                  if (month <= 0) {
                    month = 12;
                    year = year - 1;
                  }
                }
              }


              if (i == nDates - 1) {
                if (dates[i][j] <= 7) {
                  showCalendarClick = false;
                  month = month + 1;

                  if (month >= 13) {
                    month = 1;
                    year = year + 1;
                  }
                }
              }


              if (todayDate.getFullYear() == year && (todayDate.getMonth() + 1) == month && todayDate.getDate() == date) {
                colDateClass = ' today-date ';
                colDateDataAttr = "data-title='"+ todayTitle +"'";
              }

              var z_content=0;

              if (defDate != null && defDate.getFullYear() == year && (defDate.getMonth() + 1) == month && defDate.getDate() == date) {
                colDateClass = ' default-date ';
                z_content  = 1;
                colDateDataAttr = "data-title='"+ defaultDateTitle +"'";
              }

              if (j == sundayIndex || j == saturdayIndex) {
                colDateClass += ' holiday ';
              }


              var colDate = "<div id='calendarClick2' class='"+ colDateClass +" "+ ((showCalendarClick == true) ? 'calendarClick2' : '') + " " +((date == 1) ? 'calendar-date' : '') + "'  data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type1' "+((date == 1 || z_content == 1) ? 'data-content="'+settings.monthNamesShort12[month]+' '+year+'"' : '')+ "   data-year='"+ year +"' data-month='"+ settings.monthNamesShort12[month] +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";

              if (minDate != null) {
                var myCurrentDate = new Date(year, month - 1, date);
                if (minDate > myCurrentDate) {
                  colDate = "<div class='"+ colDateClass +"' data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type4' data-year='"+ year +"'  data-month='"+ settings.monthNamesShort12[month] +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";
                }
              }

              if (maxDate != null) {
                var myCurrentDate = new Date(year, month - 1, date);
                if (maxDate < myCurrentDate) {
                  colDate = "<div class='"+ colDateClass +"' data-date='"+ date +"' data-month='"+ month +"' data-year='"+ year +"'><a class='type4' data-year='"+ year +"'  data-month='"+ settings.monthNamesShort12[month]  +"' "+ colDateDataAttr +">"+ ((date < 10) ? '0' + date : date)  +"</a></div>";
                }
              }

              bodyGroup.append(colDate);
            }
          }

          self.html("");
          self.append(headerGroup);
          self.append(bodyGroup);
          self.append('<div class="clear"></div>');
          self.append(numberdayRowGroup);

    }




    var nextMonth = function() {
      var firstDateOfNextMonth = new Date(currDate.getFullYear(), currDate.getMonth() + 1, 1);
      var date = firstDateOfNextMonth.getDate();
      var month = firstDateOfNextMonth.getMonth();
      var year = firstDateOfNextMonth.getFullYear();
      currDate = new Date(year, month, date);
      draw();
    }

    var prevMonth = function() {
      var firstDateOfPrevMonth = new Date(currDate.getFullYear(), currDate.getMonth() - 1, 1);
      var date = firstDateOfPrevMonth.getDate();
      var month = firstDateOfPrevMonth.getMonth();
      var year = firstDateOfPrevMonth.getFullYear();
      currDate = new Date(year, month, date);
      draw();
    }

    var triggerAction = function() {
        $('body').on('click', '#calendarClick2', function(){
          var selectedDate = $(this).data('date');
          var selectedMonth = $(this).data('month');
          var selectedYear = $(this).data('year');
          settings.dayClick2.call(this, new Date(selectedYear, selectedMonth - 1, selectedDate), self);
       });

        $('#gogocalendar-dropoff').on('click', '.mc-prev-month', function() {
          prevMonth();
        });

        $('#gogocalendar-dropoff').on('click', '.mc-next-month', function() {
          nextMonth();
        });
    }

    return {
      build: function() {
                        settings = $.extend( {}, $.fn.gogoCalendar2.defaults, options );

        if (typeof settings.defaultDate !== 'undefined') {
                              var defaultDateArr = settings.defaultDate.split('-');
                              currDate = new Date(defaultDateArr[0], defaultDateArr[1] - 1, defaultDateArr[2]);
                              defDate = currDate;
                        }
        draw();
        triggerAction();
      },
      update: function(options) {
            settings = $.extend(settings, options);

            // replace with defaultDate when exist
            if (typeof settings.defaultDate !== 'undefined') {
                  var defaultDateArr = settings.defaultDate.split('-');
                  currDate = new Date(defaultDateArr[0], defaultDateArr[1] - 1, defaultDateArr[2]);
                  defDate = currDate;
            }

            draw();
      }
    }
  }

  $.fn.gogoCalendar2.defaults = {
        monthNames: [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ],
        monthNamesShort: [ 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ],
        monthNamesShort12: [ '','Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' ],
        dayNames: [ 'su', 'mo', 'tu', 'we', 'th', 'fr', 'sa'],
        dayNamesShort: [ 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat' ],
        dayUseShortName: false,
        monthUseShortName: false,
        showNotes: false,
        startWeek: 'sunday',
        dayClick2: function(date, view) {}
  };

} ( jQuery ));





















$(document).ready(function() {

  var d = new Date();
  var now = d.getFullYear() + '-' + (d.getMonth() + 1) + '-' + d.getDate();

  var today_year = d.getFullYear();
  var today_month = d.getMonth()+1;
  var today_day = d.getDate();

  var future = (d.getFullYear()+1) + '-'+(d.getMonth())+'-'+d.getDate();
  var DatePickupDay = $('#date-pickup-day');
  var DateDropoffDay = $('#date-dropoff-day');

  var pDate = now;
  if ($('#collector').length && $('#collector').attr('data-pickup') != '') {
      pDate = $('#collector').attr('data-pickup').substring(0, $('#collector').attr('data-pickup').indexOf('T'));
  }
  var d = new Date(pDate);
  var lastDayOfMonth = new Date(d.getFullYear(), (d.getMonth() + 1), 0).getDate();
  var output = [];
  for (i = 1; i <= lastDayOfMonth; i++) {
      if (i < d.getDate()) { disabled = "disabled='disabled'"; } else { disabled = ""; }
      output.push('<option value="' + i + '" ' + disabled + ' >' + i + '</option>');
  };
  DatePickupDay.html(output.join(''));
  $("#date-pickup-day [value='" + d.getDate() + "']").prop("selected", true).trigger('refresh');
  $("#date-pickup-month [value='" + (d.getMonth() + 1) + "']").prop("selected", true).trigger('refresh');


  var dDate = now;
  if ($('#collector').length && $('#collector').attr('data-dropoff') != '') {
      dDate = $('#collector').attr('data-dropoff').substring(0, $('#collector').attr('data-dropoff').indexOf('T'));
  }
  var d = new Date(dDate);
  var lastDayOfMonth = new Date(d.getFullYear(), (d.getMonth() + 1), 0).getDate();
  var output = [];
  for (i = 1; i <= lastDayOfMonth; i++) {
      if (i < d.getDate()) { disabled = "disabled='disabled'"; } else { disabled = ""; }
      output.push('<option value="' + i + '" ' + disabled + ' >' + i + '</option>');
  };
  DateDropoffDay.html(output.join(''));
  $("#date-dropoff-day [value='" + d.getDate() + "']").prop("selected", true).trigger('refresh');
  $("#date-dropoff-month [value='" + (d.getMonth() + 1) + "']").prop("selected", true).trigger('refresh');



                  var calendar_pickup = $("#gogocalendar-pickup").gogoCalendar1({
                      minDate: now,
                      maxDate: future,
                      //        defaultDate: now,
                      defaultDate: pDate,
                dayClick1: function(date, view) {
                  var defaultDate= date.getFullYear() + '-' +  (date.getMonth() + 1) + '-' + date.getDate();
                  var defaultMonth = date.getMonth() + 1;
                  var defaultYear = date.getFullYear();
                  var defaultDay = date.getDate();
                  var lastDayOfMonth = new Date(defaultYear, defaultMonth, 0).getDate();

                  DatePickupDay.empty();

                  var output = [];

                  if (today_month == defaultMonth &&  today_year== defaultYear){current_m = 1;}else{current_m = 0;}

                  for (i = 1; i <= lastDayOfMonth; i++) {

                     if ( defaultYear == today_year && current_m == 1 ){disabled= "disabled='disabled'";}else{disabled="";}

                    output.push('<option value="'+ i +'">'+ i +'</option>');
                  };

                  DatePickupDay.html(output.join(''));

                  $("#date-pickup-day [value='"+defaultDay+"']").prop("selected", true).trigger('refresh');
                  $("#date-pickup-month:selected").prop("selected", false)
                  $("#date-pickup-month [value='"+defaultMonth+"']").prop("selected", true).trigger('refresh');


                  calendar_pickup.update({
                      minDate: now,
                     defaultDate: defaultDate
                   });


                  var DateDropoff = $('#calendarClick2.default-date').data('year') +'-'+ $('#calendarClick2.default-date').data('month') +'-'+  $('#calendarClick2.default-date').data('date') +'-'+ $('#date-dropoff-time').val() + '-' + $('#date-dropoff-minute').val();



                  if ((new Date(DateDropoff) - new Date(defaultDate)) < 0 ){
                        calendar_dropoff.update({
                          minDate: defaultDate,
                           defaultDate: defaultDate
                           });


                  $("#date-dropoff-day [value='"+defaultDay+"']").prop("selected", true).trigger('refresh');
                  $("#date-dropoff-month:selected").prop("selected", false)
                  $("#date-dropoff-month [value='"+defaultMonth+"']").prop("selected", true).trigger('refresh');


                  }else{

                calendar_dropoff.update({
                          minDate: defaultDate

                           });


                  }

                  collector();
                }
      });

     calendar_pickup.build();


      var calendar_dropoff = $("#gogocalendar-dropoff").gogoCalendar2({
        minDate: now,
        maxDate: future,
        defaultDate: dDate,
                dayClick2: function(date, view) {
                  var defaultDate= date.getFullYear() + '-' +  (date.getMonth() + 1) + '-' + date.getDate();
                  var defaultMonth = date.getMonth() + 1;
                  var defaultYear = date.getFullYear();
                  var defaultDay = date.getDate();
                  var lastDayOfMonth = new Date(defaultYear, defaultMonth, 0).getDate();

                  DateDropoffDay.empty();

                  var output = [];
                  for (i = 1; i <= lastDayOfMonth; i++) {


                    output.push('<option value="'+ i +'">'+ i +'</option>');
                  };
                  DateDropoffDay.html(output.join(''));
                  $("#date-dropoff-day [value='"+defaultDay+"']").prop("selected", true).trigger('refresh');
                  $("#date-dropoff-month:selected").prop("selected", false)
                  $("#date-dropoff-month [value='"+defaultMonth+"']").prop("selected", true).trigger('refresh');



          var DatePickup = $('#calendarClick1.default-date').data('year') +'-'+ $('#calendarClick1.default-date').data('month') +'-'+  $('#calendarClick1.default-date').data('date') +'-'+ $('#date-pickup-time').val() + '-' + $('#date-pickup-minute').val();

        if ((new Date(DatePickup) - new Date(now)) < 0 ){

                  calendar_dropoff.update({
                      minDate: now,
                     defaultDate: defaultDate
                   });

        }else{

                  calendar_dropoff.update({
                     defaultDate: defaultDate
                   });

        }



                  collector();
                }
      });

      calendar_dropoff.build();
  collector();





$('#date-pickup-month').change(function() {
      var defaultMonth = $(this).val();

        if ( defaultMonth >=  d.getMonth()+1 ) {var defaultYear = d.getFullYear(); }else{ var defaultYear = d.getFullYear() + 1; }
        if (today_month == defaultMonth) { var defaultDate= defaultYear + '-'+ defaultMonth + '-' + today_day;  defaultDay =  today_day;  }else  {  var defaultDate= defaultYear + '-'+ defaultMonth +'-01'; defaultDay = 1;}


        var lastDayOfMonth = new Date(defaultYear, defaultMonth, 0).getDate();
        DatePickupDay.empty();

                if (today_month == defaultMonth &&  today_year== defaultYear){current_m = 1;}else{current_m = 0;}
                  var output = [];
                  for (i = 1; i <= lastDayOfMonth; i++) {
                  if ( defaultYear == today_year && current_m == 1 ){disabled= "disabled='disabled'";}else{disabled="";}
                    output.push('<option value="'+ i +'" '+disabled+'>'+ i +'</option>');
                  };
                  DatePickupDay.html(output.join('')).trigger('refresh');
                   if (today_month == defaultMonth) {
                      $("#date-pickup-day [value='"+today_day+"']").prop("selected", true).trigger('refresh');
                  }
      calendar_pickup.update({
          minDate: now,
          defaultDate: defaultDate
       });


    var DateDropoff = $('#calendarClick2.default-date').data('year') +'-'+ $('#calendarClick2.default-date').data('month') +'-'+  $('#calendarClick2.default-date').data('date');

           if ((new Date(DateDropoff) - new Date(defaultDate)) < 0 ){
                  calendar_dropoff.update({
                      minDate: defaultDate,
                      defaultDate: defaultDate
                   });
          }else{

                  calendar_dropoff.update({
                      minDate: defaultDate,

                   });

          }


                  $("#date-dropoff-day [value='"+defaultDay+"']").prop("selected", true).trigger('refresh');
                  $("#date-dropoff-month:selected").prop("selected", false)
                  $("#date-dropoff-month [value='"+defaultMonth+"']").prop("selected", true).trigger('refresh');



      collector();
});




$('#date-dropoff-month').change(function() {

      var old_month = $('#calendarClick2.default-date').data('month');
      var defaultMonth = $(this).val();

        if ( defaultMonth >=  d.getMonth()+1 ) {var defaultYear = d.getFullYear(); }else{ var defaultYear = d.getFullYear() + 1; }
        var defaultDate= defaultYear + '-'+ defaultMonth +'-01';




      var DatePickup = $('#calendarClick1.default-date').data('year') +'-'+ $('#calendarClick1.default-date').data('month') +'-'+  $('#calendarClick1.default-date').data('date');




           if ((new Date(DatePickup) - new Date(defaultDate)) > 0 ){

                $("#date-dropoff-month [value='"+old_month+"']").prop("selected", true).trigger('refresh');
           }else{


        var lastDayOfMonth = new Date(defaultYear, defaultMonth, 0).getDate();
        DateDropoffDay.empty();

                  var output = [];
                  for (i = 1; i <= lastDayOfMonth; i++) {
                    output.push('<option value="'+ i +'">'+ i +'</option>');
                  };
                  DateDropoffDay.html(output.join('')).trigger('refresh');
}



       if ((new Date(DatePickup) - new Date(defaultDate)) < 0 ){

      calendar_dropoff.update({
          minDate: now,
          defaultDate: defaultDate
       });

        }


      collector();
});





$('#date-pickup-day').change(function() {
    var defaultMonth = $("#date-pickup-month").val();
    if ( defaultMonth >=  d.getMonth()+1 ) {var defaultYear = d.getFullYear(); }else{ var defaultYear = d.getFullYear() + 1; }
    var defaultDate= defaultYear + '-'+ defaultMonth +'-' + $(this).val();

    defaultDay = $(this).val();

     calendar_pickup.update({
          minDate: now,
          defaultDate: defaultDate
       });

var DateDropoff = $('#calendarClick2.default-date').data('year') +'-'+ $('#calendarClick2.default-date').data('month') +'-'+  $('#calendarClick2.default-date').data('date') +'-'+ $('#date-dropoff-time').val() + '-' + $('#date-dropoff-minute').val();

 if ((new Date(DateDropoff) - new Date(defaultDate)) < 0 ){
     calendar_dropoff.update({
          minDate: defaultDate,
          defaultDate: defaultDate
       });

             $("#date-dropoff-day [value='"+defaultDay+"']").prop("selected", true).trigger('refresh');
        $("#date-dropoff-month:selected").prop("selected", false)
        $("#date-dropoff-month [value='"+defaultMonth+"']").prop("selected", true).trigger('refresh');

}else{
     calendar_dropoff.update({
          minDate: defaultDate,

       });



}


      collector();
});


$('#date-dropoff-day').change(function() {
    var defaultMonth = $("#date-dropoff-month").val();

    var old_day = $('#calendarClick2.default-date').data('date');

    if ( defaultMonth >=  d.getMonth()+1 ) {var defaultYear = d.getFullYear(); }else{ var defaultYear = d.getFullYear() + 1; }
    var defaultDate= defaultYear + '-'+ defaultMonth +'-' + $(this).val();


   var DatePickup = $('#calendarClick1.default-date').data('year') +'-'+ $('#calendarClick1.default-date').data('month') +'-'+  $('#calendarClick1.default-date').data('date');



          if ((new Date(DatePickup) - new Date(defaultDate)) <= 0 ){
             calendar_dropoff.update({
                  minDate: DatePickup ,
                  defaultDate: defaultDate
               });

        }else{

        $("#date-dropoff-day [value='"+old_day+"']").prop("selected", true).trigger('refresh');

        }



      collector();
});



$('#date-pickup-time').change(function() {
   collector();
});

$('#date-pickup-minute').change(function() {
   collector();
});

$('#date-dropoff-time').change(function() {
   collector();
});

$('#date-dropoff-minute').change(function() {
   collector();
});




   $(document).on({
      mouseover: function () {
        var selectedMonth = $(this).data('month');
        var selectedYear = $(this).data('year');
        $(this).addClass('change').attr('data-content', selectedMonth + " " + selectedYear );
      }
    }, ".day-choose div a");




});
function amc_addnul(num)
{
	if (num<10)
	return '0'+num;
	else
	return num;
}
function amc_convert_date(y,m,d,h,mn)
{
	var x = new Date();
	var currentTimeZoneOffsetInHours = x.getTimezoneOffset() / 60;
	h=h.split(' ');
	var ap=h[1];
	var hour=parseInt(h[0].split(':')[0]);
	if (ap=='PM') hour=hour+12-currentTimeZoneOffsetInHours;
    mn = mn.replace(':','');
//    return new Date(y, m, d, hour, mn,0,0);
	var dt = new Date(y, m, d, hour, mn,0,0);
    return dt.getFullYear()+'-'+amc_addnul(dt.getMonth()+1)+'-'+amc_addnul(dt.getDate())+'T'+amc_addnul(dt.getHours())+':'+amc_addnul(dt.getMinutes())+':00';
}
/*
function collector() {
	if ($('#calendarClick1.default-date').length)
	{
		var DatePickup=amc_convert_date(
			$('#calendarClick1.default-date').data('year'),
			$('#date-pickup-month').val()-1,
			$('#date-pickup-day').val(),
			$('#date-pickup-time').val(),
			$('#date-pickup-minute').val().replace(':',''),
		);
		$('#collector').data('pickup', DatePickup.toISOString() ).attr('data-pickup', DatePickup.toISOString());
		$('#date-pickup-value').val(DatePickup.toISOString() );
	}

	if ($('#calendarClick2.default-date').length)
	{
		var DateDropoff=amc_convert_date(
			$('#calendarClick2.default-date').data('year'),
			$('#date-dropoff-month').val()-1,
			$('#date-dropoff-day').val(),
			$('#date-dropoff-time').val(),
			$('#date-dropoff-minute').val().replace(':',''),
		);
		$('#collector').data('dropoff', DateDropoff.toISOString() ).attr('data-dropoff', DateDropoff.toISOString());
        $('#date-dropoff-value').val(DateDropoff.toISOString() );
	}
}
/*
function amc_convert_date(y,m,d,h,mn)
{
	h=h.split(' ');
	var ap=h[1];
	var hour=parseInt(h[0].split(':')[0]);
	if (ap=='PM') hour=hour+12;
    mn = mn.replace(':','');
    var dt=new Date(y, m, d, hour, mn,0,0);
    return dt.getFullYear()+'-'+dt.getMonth()+'-'+dt.getDate()+'T'+dt.getHours()+':'+dt.getMinutes()+':00';
}
*/
function collector() {
	if ($('#calendarClick1.default-date').length)
	{
		var DatePickup=amc_convert_date(
			$('#calendarClick1.default-date').data('year'),
			$('#date-pickup-month').val()-1,
			$('#date-pickup-day').val(),
			$('#date-pickup-time').val(),
			$('#date-pickup-minute').val().replace(':',''),
		);
		$('#collector').data('pickup', DatePickup ).attr('data-pickup', DatePickup);
		$('#date-pickup-value').val(DatePickup );
	}

	if ($('#calendarClick2.default-date').length)
	{
		var DateDropoff=amc_convert_date(
			$('#calendarClick2.default-date').data('year'),
			$('#date-dropoff-month').val()-1,
			$('#date-dropoff-day').val(),
			$('#date-dropoff-time').val(),
			$('#date-dropoff-minute').val().replace(':',''),
		);
		$('#collector').data('dropoff', DateDropoff ).attr('data-dropoff', DateDropoff);
        $('#date-dropoff-value').val(DateDropoff );
	}
}

