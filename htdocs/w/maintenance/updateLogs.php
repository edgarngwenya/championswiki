<?
/*
 * Script to update Media articles from the MUSH
 *
 * Usage: php importMedia.php
 *
 * @package MediaWiki
 * @subpackage maintenance
 */

require_once( 'commandLine.inc' );

// Get All Articles in the Log Space

// If the Log is in the old format
// Convert and Save the Log.
$user = User::newFromName('Angstrom');

$query =
	"select p.page_id, p.page_title, p.page_namespace ".
	"from page p ".
	"where p.page_namespace = 104 ";

$articles = array();
$dbr =& wfGetDB( DB_SLAVE );
$res = $dbr->query( $query );

while ( $row = $dbr->fetchObject( $res ) ) {
	$title = Title::newFromText( $row->page_title, $row->page_namespace );
	$articles[] = new Article($title);
}

foreach ($articles as $article) {
	if (isOldLogFormat($article)) {
		try {
			$newContent = updateContent($article->getTitle(), $article->getContent());

			if ($newContent) {
				$article->doEdit(
						$newContent,
						'Use new loginfo tag', 
						EDIT_MINOR,
						false,
						$user);
				print "Converted article: " . $article->getTitle() . "\n";
			}
			else {
				print "Failed to convert article: " . $article->getTitle() . "\n";
			}
		}
		catch (Exception $e) {
			print "Exception in convert article: " . $article->getTitle() . "\n";
		}
	}
	else {
		print "No need to convert article: " . $article->getTitle() . "\n";
	}
}

function isOldLogFormat($article) {
	return (strpos($article->getContent(), "Template:Log_Summary") !== false);
}

function convertCast($cast) {
	$result = "\n";
	
	foreach (explode(",", str_replace("<br>", "", $cast)) as $member) {
		$result .= "\t<character>"
			. trim(str_replace("]", "", str_replace("[", "", $member)))
			. "</character>\n";
	}
	
	return $result;
}

function updateContent($articleTitle, $content) {
	preg_match('/^\\{\\{Template\\:Log_Summary.*plot=([^\n]+).*cast=([^\n]+).*(\d{4}\\/\d{2}\\/\d{2}).*\\}\\}$/ms', $content, $matches);
	
	if ($matches) {
		$plot = trim(str_replace("]", "", str_replace("[", "",$matches[1])));
		$cast = convertCast($matches[2]);
		$date = $matches[3];
		
		$sequenceNumber = 1;
		if (preg_match('/^(.*), Scene (\d+)$/', $articleTitle, $match)) {
			$sequenceNumber = $match[2];
		}
		
		$template = <<<EOT
<loginfo>
<plot>$plot</plot>
<date>$date</date>
<cast>$cast</cast>
<sequence_number>$sequenceNumber</sequence_number>
</loginfo>

EOT;
		return $template . preg_replace('/^\\{\\{Template\\:Log_Summary.*plot=([^\n]+).*cast=([^\n]+).*(\d{4}\\/\d{2}\\/\d{2}).*\\}\\}$/ms', '', $content);
	}
	
	return false;
}
?>