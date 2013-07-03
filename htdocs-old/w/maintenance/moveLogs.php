<?


/*
 * Script to update image metadata records
 *
 * Usage: php moveLogs.php
 *
 * @package MediaWiki
 * @subpackage maintenance
 */
 
// Move logs into the Logs namespace
// Special logs tag: recent logs, logs including this character


require_once( 'commandLine.inc' );


$dbr =& wfGetDB( DB_SLAVE );

$query = 
	"select cl.cl_from, p.page_namespace, p.page_title ".
	"from categorylinks cl, page p ".
	"where cl.cl_to = 'Logs' ".
	"and p.page_id = cl.cl_from ".
	"and p.page_namespace = 0 " .
	"and p.page_title != 'Logs'";
	
$res = $dbr->query( $query );
while ($row = $dbr->fetchObject( $res ) ) {
	$articleName = $row->page_title;
            
	print "Moving: $articleName\n";     
	
	$oldTitle = Title::newFromText( $articleName );
	$newTitle = Title::newFromText( $articleName, 104 );
	$a = new Article( $oldTitle );
	
	$pattern = '/\[\[([^\]]*Category[^\|\]]+)\]\]/';
	$text = preg_replace( $pattern, '[[$1|' . $articleName . ']]', $a->getContent() );
	$a->doEdit( $text, 'Changing the category links', EDIT_UPDATE|EDIT_FORCE_BOT );
	$oldTitle->moveTo( $newTitle, false, 'Moving logs to separate namespace.' );
}


?>