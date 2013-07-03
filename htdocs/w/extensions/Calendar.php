<?php
/* Description:
 *   This extension implements a calendar in PHP, designed to integrate into 
 *   the MediaWiki wiki.  This calendar supports custom themes 
 *   (via a template), generation of wiki articles for events, listing the 
 *   events for a day including a "summary" of the events, and allows you to
 *   name calendars to make them unique or to share them within multiple
 *   articles.
 * 
 * Use:
 *   To use this calendar, simply place a calendar tag into an article:
 *   <calendar></calendar>
 * 
 *   If you wish to name your calendar, you can add a name attribute.
 *   <calendar name="IT Calendar"></calendar>
 *   By giving a calendar a name, the events won't show up on other calendars
 *   with different names or no name.
 * 
 *   Note, the names have to be simple and valid for article titles.  Some
 *   characters will cause the attribute to be ignored completely, like an
 *   apostrophe.
 * 
 *   You can also set two other attributes in the calendar tag:
 *   startyear - The first year to show on the calendar, defaults to this year.
 *   yearsahead - The number of years to include ahead of the current year,
 *                defaults to 3.
 * 
 *   For example, you could have the tag:
 *   <calendar name="Band Events" startyear="2007" yearsahead="2" /> 
 * 
 * Templating:
 *   You are required to use a template file for the calendar.  The template
 *   file is named "calendar_template.html" and should be placed in the
 *   extensions folder of MediaWiki.  You should have gotten a basic template
 *   with this code.  You can use this template as a guide to making your own
 *   calendars.  Note, all the sections are important and the comment tags such
 *   as "<!-- Monday Start -->" are very important and must remain in the
 *   template.  They define the boundaries of the HTML to pull when generating
 *   the calendar code.  More information can be found in the included template
 *   file.
 * 
 * Article Summaries:
 *   When you add events to the calendar, it is possible to have the calendar
 *   pull "summaries" of the articles for display on the calendar.  This is
 *   done by pulling the first section heading and the text within that
 *   section.  The heading is displayed on the calendar and the text within
 *   that section is displayed with a mouse-over, via the title attribute of
 *   the anchor tag.  The lengths are truncated if too long, 25 characters for
 *   the event title and 100 characters for the mouse-over text.
 * 
 *   So when you create an article, you can do something like this:
 *   == Event Title ==
 *   Some informative text for the event to display on mouse-over.
 *   blah blah blah...
 * 
 * Notes:
 *   Although this calendar works well, it has not been tested much.  There may
 *   be some input that does break it or that does not work well.  If you find
 *   anything that breaks the calendar, let me know.  Something I do not have
 *   much control over if its handled by MediaWiki.  This has been tested
 *   against version 1.7.1 and 1.8.2.  If you have problems running it on newer
 *   versions, let me know and I will try to get it updated.
 * 
 *   Since all the links for the events are not contained within an article,
 *   they will all be included in your orphaned page list.  There's no good
 *   way around this.
 * 
 *   For abuse protection, the code limits a max of 20 events per day.  You
 *   shouldn't need more than this, but if you do, you will need to modify the
 *   code in the getArticlesForDay() function, and the code at the end of the
 *   CalendarAdjust.php file.  Really, you may want to categorize your
 *   calendars using names if you have a lot of events you are adding to a
 *   day.
 * 
 * Included Files:
 *   Below is a list of files included with this calendar extension.  These
 *   files need to exist for this extension to work.
 *   - Calendar.php
 *   - CalendarAdjust.php
 *   - calendar_template.html
 *
 * Author Notes:
 *   Written By: Michael Walters
 *   Last Modified: 11/12/2006
 *   Email: mcw6@aol.com
 * 
 * Change Log:
 *   11-8-2006
 *   The extension now passes the script path variable defined by MediaWiki to
 *   the calendarAdjust.php page so it can determine how to include the
 *   necessary files and call the index.php page.
 * 
 *   The calendarAdjust.php file now uses the passed path to include the
 *   wiki files and call the index.php page, so it shouldn't be dependent on
 *   install location.
 * 
 *   calendarAdjust.php file now includes a minimal amount of files for looking
 *   up articles, and will not show an error message for the profiler needed by
 *   the Setup.php file in later versions of MediaWiki.  This should make it
 *   more compatible with older versions of MediaWiki.
 * 
 *   The CSS file is not longer being linked to in the template file, as it was
 *   dependent on the install location of the calendar.  The style is not added
 *   in a <style> tag instead.  You can of course do as you wish in your own
 *   template designs :) 
 *   
 *   11-12-2006
 *   Setup an alternative method for determining the root location of the web
 *   server.  The PHP server variable DOCUMENT_ROOT is not available on all
 *   servers so it had to be determine through other means.
 *   
 *   Changed the way the article content is retrieved so it doesn't alter the
 *   title of the page the calendar is on, occurs in some versions of
 *   MediaWiki.
 *   
 *   Used an alternative method of retieving the title of the current article
 *   as the methods have varied between versions of MediaWiki.  Hopefully this
 *   method works for most of the current versions.
 *   
 *   Now the extension supports being installed into other locations, rather
 *   then just the extensions folder.
 *
 *   Support for both URL formats should be OK now.  The extension will still
 *   write URLs in the long format, but there should be no problem with this.
 */

$wgExtensionFunctions[] = "wfCalendarExtension";

// function adds the wiki extension
function wfCalendarExtension() {
    global $wgParser;
    $wgParser->setHook( "calendar", "displayCalendar" );
    $wgParser->setHook( "date", "displayDate" );

}

// path to the root of the web server
$wgLocalPath = str_replace("\\", "/" , substr($_SERVER["SCRIPT_FILENAME"], 0, strlen($_SERVER["SCRIPT_FILENAME"]) - strlen($_SERVER["SCRIPT_NAME"])));

// calendar class
class Calendar
{   
    /*
        Constructor for the Calendar class
    */
    function Calendar()
    {
    	// set the calendar's date
    	$today = getdate();
    	
    	$this->month = $today['mon'];
    	$this->year = $today['year'];
    	$this->calendarStartYear = $this->year;
    	$this->name = 'Events';
    }

    // Calculate the number of days in a month, taking into account leap years.
    function getDaysInMonth()
    {
        if ($this->month < 1 || $this->month > 12)
        {
            return 0;
        }
   
        $d = $this->daysInMonth[$this->month - 1];
   
        if ($this->month == 2)
        {
            // Check for leap year
            // Forget the 4000 rule, I doubt I'll be around then...
        
            if ($this->year%4 == 0)
            {
                if ($this->year%100 == 0)
                {
                    if ($this->year%400 == 0)
                    {
                        $d = 29;
                    }
                }
                else
                {
                    $d = 29;
                }
            }
        }
    
        return $d;
    }

    // Generate the HTML for a given month
    function getHTML()
    {   
       	global $wgScriptPath, $wgLocalPath;
       	
        /***** Replacement tags *****/
        // the month select box [[MonthSelect]]
        $tag_monthSelect = "";
        // the previous month button [[PreviousMonthButton]]
        $tag_previousMonthButton = "";
        // the next month button [[NextMonthButton]]
        $tag_nextMonthButton = "";
        // the year select box [[YearSelect]]
        $tag_yearSelect = "";
        // the previous year button [[PreviousYearButton]]
        $tag_previousYearButton = "";
        // the next year button [[NextYearButton]]
        $tag_nextYearButton = "";
        // the calendar name [[CalendarName]]
        $tag_calendarName = "";
        // the calendar month [[CalendarMonth]]
        $tag_calendarMonth = "";
        // the calendar year [[CalendarYear]]
        $tag_calendarYear = "";
        // the calendar day [[Day]]
        $tag_day = "";
        // the add event link [[AddEvent]]
        $tag_addEvent = ""; 
        // the event list [[EventList]]
        $tag_eventList = "";
        
        
        /***** Calendar parts (loaded from template) *****/
        // html for the entire template
        $html_template = "";
        // calendar pieces
        $html_calendar_start = "";
        $html_calendar_end = "";
        // the calendar header
        $html_header = "";
        // the day heading
        $html_day_heading = "";
        // the calendar week pieces
        $html_week_start = "";
        $html_week_end = "";
        // the calendar footer
        $html_footer = "";
        // arrays for the day formats
    	$daysNormalHTML = array();
    	$daysMissingHTML = array();
    	$daysSelectedHTML = array();
        
        /***** Other variables *****/
        // the string to return
        $calendarString = "";
        // the days in the current month
    	$daysInMonth = $this->getDaysInMonth();
    	// the date for the first day of the month
    	$firstDate = getdate(mktime(12, 0, 0, $this->month, 1, $this->year));
    	// the first day of the month
    	$first = $firstDate["wday"];
    	// today's date
    	$todayDate = getdate();
    	// if the day being processed is today
    	$isSelected = false;
    	// if the calendar cell being processed is in the current month
    	$isMissing = false;
    	
    	/***** Paths to important files *****/
    	// the path to this extension (install location)
		$calendarExtensionPath = str_replace("\\", "/", substr(dirname(__FILE__), strlen($wgLocalPath)));
    	// referrer (the page with the calendar currently displayed)
    	$referrerURL = $_SERVER['PHP_SELF'];
    	if ($_SERVER['QUERY_STRING'] != '') {
    		$referrerURL .= "?" . $_SERVER['QUERY_STRING'];
    	}
    	// the path to the CalendarAdjust.php file
    	$calendarAdjustPath = $calendarExtensionPath . "/CalendarAdjust.php";
    	// the template file (full path needed)
    	$calendarTemplate = str_replace("\\", "/", dirname(__FILE__)) . "/calendar_template.html";
    	
    	
    	/***** Build the known tag elements (non-dynamic) *****/
    	// set the month's name tag
    	$tag_calendarName = str_replace('_', ' ', $this->name);
    	if ($tag_calendarName == "") {
    		$tag_calendarName = "Calendar";
    	}
    	
    	// set the month's mont and year tags
    	$tag_calendarMonth = $this->monthNames[$this->month - 1];
    	$tag_calendarYear = $this->year;
    	
    	// build the month select box
    	$tag_monthSelect = "<select onChange=\"javascript:document.location='" . $calendarAdjustPath . "?month=' + this.options[this.selectedIndex].value + '&year=" . $this->year . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">\n";
    	for ($i = 0; $i < count($this->monthNames); $i += 1) {
    		if ($i + 1 == $this->month) {
    			$tag_monthSelect .= "<option value=\"" . ($i + 1) . "\" selected=\"true\">" . $this->monthNames[$i] . "</option>\n";
    		}
    		else {
    			$tag_monthSelect .= "<option value=\"" . ($i + 1) . "\">" . $this->monthNames[$i] . "</option>\n";
    		}
    	}
    	$tag_monthSelect .= "</select>";
    	
    	// build the year select box, with +/- 5 years in relation to the currently selected year
    	$tag_yearSelect = "<select onChange=\"javascript:document.location='" .$calendarAdjustPath . "?year=' + this.options[this.selectedIndex].value + '&month=" . $this->month . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">\n";
    	for ($i = $this->calendarStartYear; $i <= $this->calendarStartYear + $this->yearsAhead; $i += 1) {
    		if ($i == $this->year) {
    			$tag_yearSelect .= "<option value=\"" . $i . "\" selected=\"true\">" . $i . "</option>\n";
    		}
    		else {
    			$tag_yearSelect .= "<option value=\"" . $i . "\">" . $i . "</option>\n";
    		}
    	}
    	$tag_yearSelect .= "</select>";
    	
    	// build the previous month button
    	$tag_previousMonthButton = "<input type=\"button\" value= \"<<\" onClick=\"javascript:document.location='" . $calendarAdjustPath . "?year=" . ($this->month == 1 ? $this->year - 1 : $this->year ) . "&month=" . ($this->month == 1 ? 12 : $this->month - 1) . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">";
    	
    	// build the next month button
    	$tag_nextMonthButton = "<input type=\"button\"  value= \">>\" onClick=\"javascript:document.location='" . $calendarAdjustPath . "?year=" . ($this->month == 12 ? $this->year + 1 : $this->year ) . "&month=" . ($this->month == 12 ? 1 : $this->month + 1) . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">";
    	
    	// build the previous year button
    	$tag_previousYearButton = "<input type=\"button\" value= \"<<\" onClick=\"javascript:document.location='" . $calendarAdjustPath . "?year=" . ($this->year == $this->calendarStartYear ? $this->calendarStartYear : $this->year - 1) . "&month=" . $this->month . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">";
    	
    	// build the next year button
    	$tag_nextYearButton = "<input type=\"button\"  value= \">>\" onClick=\"javascript:document.location='" . $calendarAdjustPath . "?year=" . ($this->year == $todayDate['year'] + $this->yearsAhead ? $todayDate['year'] + $this->yearsAhead : $this->year + 1) . "&month=" . $this->month . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&referer=" . urlencode($referrerURL) . "';\">";
    	
    	
    	/***** load the html code pieces from the template *****/
    	// load the template file
    	$html_template = file_get_contents($calendarTemplate);
    	
    	// grab the HTML for the calendar
        // calendar pieces
        $html_calendar_start = $this->searchHTML($html_template, "<!-- Calendar Start -->", "<!-- Header Start -->");
        $html_calendar_end = $this->searchHTML($html_template, "<!-- Footer End -->", "<!-- Calendar End -->");;
        // the calendar header
        $html_header = $this->searchHTML($html_template, "<!-- Header Start -->", "<!-- Header End -->");
        // the day heading
        $html_day_heading = $this->searchHTML($html_template, "<!-- Day Heading Start -->", "<!-- Day Heading End -->");
        // the calendar week pieces
        $html_week_start = $this->searchHTML($html_template, "<!-- Week Start -->", "<!-- Sunday Start -->");
        $html_week_end = $this->searchHTML($html_template, "<!-- Saturday End -->", "<!-- Week End -->");
        // the individual day cells
        $daysNormalHTML[0] = $this->searchHTML($html_template, "<!-- Sunday Start -->", "<!-- Sunday End -->");
        $daysNormalHTML[1] = $this->searchHTML($html_template, "<!-- Monday Start -->", "<!-- Monday End -->");
        $daysNormalHTML[2] = $this->searchHTML($html_template, "<!-- Tuesday Start -->", "<!-- Tuesday End -->");
        $daysNormalHTML[3] = $this->searchHTML($html_template, "<!-- Wednesday Start -->", "<!-- Wednesday End -->");
        $daysNormalHTML[4] = $this->searchHTML($html_template, "<!-- Thursday Start -->", "<!-- Thursday End -->");
        $daysNormalHTML[5] = $this->searchHTML($html_template, "<!-- Friday Start -->", "<!-- Friday End -->");
        $daysNormalHTML[6] = $this->searchHTML($html_template, "<!-- Saturday Start -->", "<!-- Saturday End -->");
        
        $daysSelectedHTML[0] = $this->searchHTML($html_template, "<!-- Selected Sunday Start -->", "<!-- Selected Sunday End -->");
        $daysSelectedHTML[1] = $this->searchHTML($html_template, "<!-- Selected Monday Start -->", "<!-- Selected Monday End -->");
        $daysSelectedHTML[2] = $this->searchHTML($html_template, "<!-- Selected Tuesday Start -->", "<!-- Selected Tuesday End -->");
        $daysSelectedHTML[3] = $this->searchHTML($html_template, "<!-- Selected Wednesday Start -->", "<!-- Selected Wednesday End -->");
        $daysSelectedHTML[4] = $this->searchHTML($html_template, "<!-- Selected Thursday Start -->", "<!-- Selected Thursday End -->");
        $daysSelectedHTML[5] = $this->searchHTML($html_template, "<!-- Selected Friday Start -->", "<!-- Selected Friday End -->");
        $daysSelectedHTML[6] = $this->searchHTML($html_template, "<!-- Selected Saturday Start -->", "<!-- Selected Saturday End -->");
        
        $daysMissingHTML[0] = $this->searchHTML($html_template, "<!-- Missing Sunday Start -->", "<!-- Missing Sunday End -->");
        $daysMissingHTML[1] = $this->searchHTML($html_template, "<!-- Missing Monday Start -->", "<!-- Missing Monday End -->");
        $daysMissingHTML[2] = $this->searchHTML($html_template, "<!-- Missing Tuesday Start -->", "<!-- Missing Tuesday End -->");
        $daysMissingHTML[3] = $this->searchHTML($html_template, "<!-- Missing Wednesday Start -->", "<!-- Missing Wednesday End -->");
        $daysMissingHTML[4] = $this->searchHTML($html_template, "<!-- Missing Thursday Start -->", "<!-- Missing Thursday End -->");
        $daysMissingHTML[5] = $this->searchHTML($html_template, "<!-- Missing Friday Start -->", "<!-- Missing Friday End -->");
        $daysMissingHTML[6] = $this->searchHTML($html_template, "<!-- Missing Saturday Start -->", "<!-- Missing Saturday End -->");
        
        // the calendar footer
        $html_footer = $this->searchHTML($html_template, "<!-- Footer Start -->", "<!-- Footer End -->");
    	
    	
    	/***** Begin Building the Calendar (pre-week) *****/    	
    	// add the header to the calendar HTML code string
    	$calendarString .= $html_calendar_start;
    	$calendarString .= $html_header;
    	$calendarString .= $html_day_heading;
    	
    	
    	/***** Search and replace variable tags at this point *****/
    	$calendarString = str_replace("[[MonthSelect]]", $tag_monthSelect, $calendarString);
    	$calendarString = str_replace("[[PreviousMonthButton]]", $tag_previousMonthButton, $calendarString);
    	$calendarString = str_replace("[[NextMonthButton]]", $tag_nextMonthButton, $calendarString);
    	$calendarString = str_replace("[[YearSelect]]", $tag_yearSelect, $calendarString);
    	$calendarString = str_replace("[[PreviousYearButton]]", $tag_previousYearButton, $calendarString);
    	$calendarString = str_replace("[[NextYearButton]]", $tag_nextYearButton, $calendarString);
    	$calendarString = str_replace("[[CalendarName]]", $tag_calendarName, $calendarString);
		$calendarString = str_replace("[[CalendarMonth]]", $tag_calendarMonth, $calendarString);    	
		$calendarString = str_replace("[[CalendarYear]]", $tag_calendarYear, $calendarString);    	
    	
    	
    	/***** Begin building the calendar days *****/
    	// determine the starting day offset for the month
    	$dayOffset = -$first;
    	
    	// determine the number of weeks in the month
    	$numWeeks = floor(($daysInMonth - $dayOffset + 6) / 7);  	
  	
  		// begin writing out month weeks
  		for ($i = 0; $i < $numWeeks; $i += 1) {
  			// write out the week start code
  			$calendarString .= $html_week_start;
  			
  			// write out the days in the week
  			for ($j = 0; $j < 7; $j += 1) {
  				$thedate = getdate(mktime(12, 0, 0, $this->month, ($dayOffset + 1), $this->year));
  				$today = getdate();
  				
  				// determine the HTML to grab for the day
  				$tempString = "";
				if ($dayOffset >= 0 && $dayOffset < $daysInMonth) {
					if ($thedate['mon'] == $today['mon'] && $thedate['year'] == $today['year'] && $thedate['mday'] == $today['mday']) {
	  					$tempString = $daysSelectedHTML[$j];
	  				}
	  				else {
						$tempString = $daysNormalHTML[$j];	  					
	  				}
	  				
	  				// determine variable tag values
					// day value
					$tag_day = ($dayOffset + 1);
					// add event link value
					$tag_addEvent = "<a href=\"" . $calendarAdjustPath . "?day=" . ($dayOffset + 1) . "&month=" . $this->month . "&year=" . $this->year . "&title=" . urlencode($this->title) . "&name=" . urlencode($this->name) . "&path=" . urlencode($wgScriptPath) . "\">Add Event</a>"; 
					// event list tag
					// grab the events for the day
					$events = $this->getArticlesForDay($this->month, ($dayOffset + 1), $this->year);
					
					// write out the links for each event
					$tag_eventList = "";
					if (count($events) > 0) {
						$tag_eventList .= "<ul>";
						for ($k = 0; $k < count($events); $k += 1) {
							$tag_eventList .= "<li>" . $this->getArticleSummaryLink($events[$k]) . "</li>";
						}
						$tag_eventList .= "</ul>";
					}
					
					// replace variable tags in the string
					$tempString = str_replace("[[Day]]", $tag_day, $tempString);
					$tempString = str_replace("[[AddEvent]]", $tag_addEvent, $tempString);
					$tempString = str_replace("[[EventList]]", $tag_eventList, $tempString);
				} 
				else {
					$tempString = $daysMissingHTML[$j];
				}					
					
				// add the generated day HTML code to the calendar HTML code
				$calendarString .= $tempString;
				
				// move to the next day
				$dayOffset += 1;
  			}
  			
  			// add the week end code
  			$calendarString .= $html_week_end; 
  		}
  		
  		/***** Do footer *****/
  		$tempString = $html_footer;
  		
  		// replace potential variables in footer
    	$tempString = str_replace("[[MonthSelect]]", $tag_monthSelect, $tempString);
    	$tempString = str_replace("[[PreviousMonthButton]]", $tag_previousMonthButton, $tempString);
    	$tempString = str_replace("[[NextMonthButton]]", $tag_nextMonthButton, $tempString);
    	$tempString = str_replace("[[YearSelect]]", $tag_yearSelect, $tempString);
    	$tempString = str_replace("[[PreviousYearButton]]", $tag_previousYearButton, $tempString);
    	$tempString = str_replace("[[NextYearButton]]", $tag_nextYearButton, $tempString);
    	$tempString = str_replace("[[CalendarName]]", $tag_calendarName, $tempString);
		$tempString = str_replace("[[CalendarMonth]]", $tag_calendarMonth, $tempString);    	
		$tempString = str_replace("[[CalendarYear]]", $tag_calendarYear, $tempString);
		
		$calendarString .= $tempString;
  		
		/***** Do calendar end code *****/
    	$calendarString .= $html_calendar_end;
    	
    	// return the generated calendar code
    	return $this->stripLeadingSpace($calendarString);  	
    }
    
    // returns the HTML that appears between two search strings.
    // the returned results include the text between the search strings,
    // else an empty string will be returned if not found.
    function searchHTML($html, $beginString, $endString) {
    	$temp = split($beginString, $html);
    	if (count($temp) > 1) {
    		$temp = split($endString, $temp[1]);
    		return $temp[0];
    	}
    	return "";
    }
    
    // strips the leading spaces and tabs from lines of HTML (to prevent <pre> tags in Wiki)
    function stripLeadingSpace($html) {
    	$index = 0;
    	
    	$temp = split("\n", $html);
    	
    	$tempString = "";
    	while ($index < count($temp)) {
			while (strlen($temp[$index]) > 0 && (substr($temp[$index], 0, 1) == ' ' || substr($temp[$index], 0, 1) == '\t')) {
    			$temp[$index] = substr($temp[$index], 1);
	    	}
	    	$tempString .= $temp[$index];
	    	$index += 1;    		
    	}
    	
    	return $tempString;	
    }
    
    // returns an array of existing article names for a specific day
    function getArticlesForDay($month, $day, $year) {
    	// the name of the article to check for
    	$articleName = "";
    	// the article count
    	$articleCount = 0;
    	// the array of article names
    	$articleNames = array();
    	
    	// keep searching until name not found
    	// generate name
    	$articleName = $this->title . "/Calendar_";
		if ($this->name != "") {
			$articleName .= "\"" . $this->name . "\"_";			
		}
		$articleName .= "(" . $month . "-" . $day . "-" . $year . ")_-_Event_" . ($articleCount + 1);
    	$article = new Article(Title::newFromText($articleName));
    	while ($article->exists() && $articleCount < 20) {
    		// save name
    		$articleNames[$articleCount] = $articleName;
    		$articleCount += 1;
    		$article = null;
    		
    		// generate name
	    	$articleName = $this->title . "/Calendar_";
			if ($this->name != "") {
				$articleName .= "\"" . $this->name . "\"_";			
			}
			$articleName .= "(" . $month . "-" . $day . "-" . $year . ")_-_Event_" . ($articleCount + 1);
	    	$article = new Article(Title::newFromText($articleName));
    	}
    	$dbr =& wfGetDB( DB_SLAVE );
        
    	$sPageTable = $dbr->tableName( 'page' );
        
    	$categorylinks = $dbr->tableName( 'categorylinks' );

        
    	$res = $dbr->query(
            
    	    "SELECT page_title, page_namespace, clike1.cl_to catlike1 " . 
            
    	    "FROM $sPageTable INNER JOIN $categorylinks AS c1 ON page_id = c1.cl_from ".
    	    "AND c1.cl_to='" . $this->name . "' INNER JOIN $categorylinks " . 
            
    	    "AS clike1 ON page_id = clike1.cl_from ".
    	    "AND clike1.cl_to LIKE '" . $this->name . "_" . $year . "/" . 
    	    sprintf( '%02d', intval($month)) . "/" . 
    	    sprintf( '%02d', intval($day)) . "' " .
            
    	    "WHERE page_is_redirect = 0");
        
    	while ($row = $dbr->fetchObject( $res ) ) {
            
    	    $articleName = $row->page_title;
            
    	    $articleNames[$articleCount] = Title::newFromText( $row->page_title, $row->page_namespace );
            
    	    $articleCount += 1;
        
    	}
    	
    	return $articleNames;
    }
    
    // returns the link for an article, along with summary in the title tag, given a name
    function getArticleSummaryLink($title) {
		global $wgScript;
		$linkText = '<a href="' . $title->getLocalURL() . 
		    '" title="' . $title->getText() .
		    '">' . $title->getText() .
		    '</a>';
		return $linkText;
	}
	
	// parses a wiki article for the first section's heading
	function parseSectionHeading($text) {
		$array = preg_split("/==/", $text);
		if (count($array) > 1)
			return $array[1];
		else
			return "";
	}
	
	// parses a wiki article for the text after the first section
	function parseSectionText($text) {
		$array = preg_split("/==/", $text);
		if (count($array) > 2)
			return $array[2];
		else
			return "";
	}
	
	// sets the calendar's currently displayed month
	function setMonth($month) {
		$this->month = $month;
	}
	
	// gets the calendar's currently displayed month
	function getMonth() {
		return $this->month;
	}
	
	// sets the calendar's currently displayed year
	function setYear($year) {
		$this->year = $year;
	}
	
	// gets the calendar's currently displayed year
	function getYear() {
		return $this->year;
	}
	
	// sets the title (note: replaces spaces with underscores)
	function setTitle($title) {
		$this->title = str_replace(' ', '_', $title);
	}
	
	// gets the calendar title
	function getTitle() {
		return $this->title;
	}
	
	// sets the name (note: replaces spaces with underscores)
	function setName($name) {
		$this->name = str_replace(' ', '_', $name);
	}
	
	// get the calendar name
	function getName() {
		return $this->name;
	}
	
	// sets the start year for the calendar
	function setStartYear($year) {
		$this->calendarStartYear = $year;
	}
	
	// sets the number of years to include ahead of the current year
	function setYearsAhead($years) {
		$this->yearsAhead = $years;
	}

	// the current month
	var $month = 1;
	// the current year
	var $year = 2006;
	// the title of the page the calendar is on
	var $title = "Calendar";
	// the name of the calendar (to make it unique)
	var $name = "";
	// the start year
    var $calendarStartYear = 2006;
    // the number of years to include ahead of this year
    var $yearsAhead = 3;
	
    /*
        The labels to display for the days of the week. The first entry in this array
        represents Sunday.
    */
    var $dayNames = array("Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat");
    
    /*
        The labels to display for the months of the year. The first entry in this array
        represents January.
    */
    var $monthNames = array("January", "February", "March", "April", "May", "June",
                            "July", "August", "September", "October", "November", "December");
                            
                            
    /*
        The number of days in each month. You're unlikely to want to change this...
        The first entry in this array represents January.
    */
    var $daysInMonth = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);   
}

// callback function (hook) for the calendar
function displayCalendar($paramstring = "", $params = array()) {
	global $wgParser,$wgUser, $wgScriptPath;
	
	$wgParser->disableCache();
	
	// grab the page title
	if (defined('MAG_PAGENAME')) {
		$title = $wgParser->getVariableValue(MAG_PAGENAME);
	}
	else {
		$title = $wgParser->getVariableValue("pagename");
	}
	
	// check for the calendar "name" parameter.
	$name = "";
	if (isset($params["name"])) {
		$name = $params["name"];
	}
	
	// the calendar
	$calendar = null;
	
	// generate the cookie name
	$cookie_name = 'calendar_' . str_replace(' ', '_', $title) . str_replace(' ', '_', $name);
	
	// check if this user has a calendar saved in their session	
	if (isset($_COOKIE[$cookie_name])) {
		$temp = split("`", $_COOKIE[$cookie_name]);
		$calendar = new Calendar();
		$calendar->setMonth($temp[0]);
		$calendar->setYear($temp[1]);
		$calendar->setTitle($temp[2]);
		$calendar->setName($temp[3]);
	}
	else {
		$calendar = new Calendar();
		$calendar->setTitle($title);
		$calendar->setName($name);
		
		// check for the "startyear" parameter

	        if (isset($params["year"])) {
                    $calendar->setYear($params["year"]);
                }
                
		// save the calendar back into the session
		setcookie($cookie_name, $calendar->getMonth() . "`" . $calendar->getYear() .
				"`" . $calendar->getTitle() . "`" . $calendar->getName(), 0, "/", '');
	}
	
	// check for the "startyear" parameter
	if (isset($params["startyear"])) {
		$calendar->setStartYear($params["startyear"]);
	}
	
	// check for the "yearsahead" parameter
	if (isset($params["yearsahead"])) {
		$calendar->setYearsAhead($params["yearsahead"]);
	}
	
	return "<html>" . $calendar->getHTML() . "</html>";
}

// callback function (hook) for the calendar

function displayDate($paramstring = "", $params = array()) {
	global $wgParser,$wgUser, $wgScriptPath;
	$wgParser->disableCache();
        // grab the page title

	// check for the date "name" parameter.
	$name = "";
	if (isset($params["name"])) {
		$name = $params["name"];
	}
	
	$page = "";
	if (isset($params["page"])) {
		$page = $params["page"];
	}

	//http://champs.jaburo.net/wiki/extensions/CalendarAdjust.php?year=2055&month=12&title=Test_Calendar&name=Media&referer=%2Fwiki%2Findex.php%2FTest_Calendar
	if ( preg_match('/(\d{4}).(\d{2}).(\d{2})/', $paramstring, $matches ) ) {
	    $year = $matches[1];
	    $month = $matches[2];
	    $day = $matches[3];
            $datestring = sprintf( '%02d/%02d/%s', intval($month), intval($day), $year );
            $url = sprintf( '/wiki/extensions/CalendarAdjust.php?year=%s&month=%s&title=%s&name=%s&referer=/wiki/index.php/%s',
                $year,
                $month,
                $page,
                htmlspecialchars($name),
                $page
            );
            return "<a href=\"" . $url . "\">" . $datestring . "</a>";
        }
        
        return $paramstring;
}

?>
