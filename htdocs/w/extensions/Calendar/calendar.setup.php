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
$wgAutoloadClasses['Calendar'] = dirname(__FILE__) . '/Calendar.php';

// function adds the wiki extension
function wfCalendarExtension() {
	global $wgParser;
	$wgParser->setHook("calendar", "displayCalendar");
	$wgParser->setHook("date", "displayDate");
}

// path to the root of the web server
$wgLocalPath = str_replace("\\", "/", dirname(dirname(dirname(dirname(__FILE__)))));

// callback function (hook) for the calendar
function displayCalendar($paramstring = "", $params = array()) {
	global $wgParser, $wgUser, $wgScriptPath;

	$wgParser->disableCache();

	// grab the page title
	if (defined('MAG_PAGENAME')) {
		$title = $wgParser->getVariableValue(MAG_PAGENAME);
	} else {
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
		$temp = explode("`", $_COOKIE[$cookie_name]);
		$calendar = new Calendar();
		$calendar->setMonth($temp[0]);
		$calendar->setYear($temp[1]);
		$calendar->setTitle($temp[2]);
		$calendar->setName($temp[3]);
	} else {
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
	global $wgParser, $wgUser, $wgScriptPath, $wgLocalPath, $wgOut;
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
	
	$path = str_replace($wgLocalPath, '', dirname(__FILE__));

	//http://champs.jaburo.net/wiki/extensions/CalendarAdjust.php?year=2055&month=12&title=Test_Calendar&name=Media&referer=%2Fwiki%2Findex.php%2FTest_Calendar
	if (preg_match('/(\d{4}).(\d{2}).(\d{2})/', $paramstring, $matches)) {
		$year = $matches[1];
		$month = $matches[2];
		$day = $matches[3];
		$datestring = sprintf('%02d/%02d/%s', intval($month), intval($day), $year);
		$url = sprintf($path . '/CalendarAdjust.php?year=%s&month=%s&title=%s&name=%s&referer=/wiki/%s', 
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
