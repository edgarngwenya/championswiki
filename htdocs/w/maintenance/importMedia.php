<?


/*
 * Script to update Media articles from the MUSH
 *
 * Usage: php importMedia.php
 *
 * @package MediaWiki
 * @subpackage maintenance
 */
 
// Move logs into the Logs namespace
// Special logs tag: recent logs, logs including this character



/*
select p.page_title
from page p, categorylinks c
where p.page_id = c.cl_from
and c.cl_to = 'Media'
;
*/
  
  





require_once( 'commandLine.inc' );
//require_once( '/usr/share/php/PEAR.php' );
//require_once( '/usr/share/php/Net/Socket.php' );


$listCommand = '+media'; //'research';
$viewCommand = '+media'; //'view';

$mush = new MUSHRobot( 'championsmush.com', 6363 );
$mush->connect();
print $mush->login( 'Robbie', 'robbie' );
$text = $mush->send( $listCommand );

preg_match_all( '/\s+(\d\d\d\d)/', $text, $media );
foreach ( $media[1] as $m ) {
	$m = intval($m);
	print "# $m\n";

	$text = $mush->send( "$viewCommand $m" );
	
	$patterns = array(
		'media_id' => '/#(\d{4})/',
		'source' => '/Source\: ([^\s]+)/',
		'ooc_date' => '/Date\: ([^\s]+)/',
		'reporter' => '/Reporter\: ([^\n\r]+)/',
		'title' => '/Title\:(?:")(.*)(?:")/',
		'text' => "/\*\*\*\*\n(.*?)----/ms",
	);
	
	
	$info = array();
	foreach ( $patterns as $k => $pattern ) {
		if ( preg_match( $pattern, $text, $match ) ) {
			$info[$k] = $match[1];
		}
	}
	$orig = $info['text'];
	$info['title'] = preg_replace( "/^\s*(.*?)\s*$/", '$1', $info['title'] );
	$info['text'] = preg_replace( "/^\s+/m", "\n", $info['text'] );
	
	preg_match( '/(\d\d).(\d\d).(\d\d\d\d)/', $info['ooc_date'], $date );
	$info['ic_date'] = $date[1] . '/' . $date[2] . '/' . ($date[3]+50);
	
	
	// Does the article exist?
	if ( !$info['title'] ) {
		print "$text\n";
	}	
	
	$t = Title::newFromText( $info['title'], 102 );
	if ( $t->exists() ) {
		print 'Skipping ' . $info['title'] . "\n";
		continue;
	}
	
	// Create the article
	$smarty = efGetSmarty();
	$smarty->template_dir = dirname(__FILE__);
	$smarty->assign( 'media', $info );
	$text = $smarty->fetch( 'importMedia.tpl' );

	$summary = 'Media imported on ' . date( 'm-d-Y' );
	$a = new Article( $t );
	$a->doEdit( $text, $summary, EDIT_NEW );
	print $info['media_id'] . ': ' . $info['title'] . "\n";
}



?>