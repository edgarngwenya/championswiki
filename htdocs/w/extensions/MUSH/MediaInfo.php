<?
class MediaInfo {

	private $media_id = false;
	
	public function __construct() {
	}
	
	public function render() {
		$prev = $this->getMediaLink( $this->media_id - 1 );
		$next = $this->getMediaLink( $this->media_id + 1 );
	
		return
			"{{Infobox media ".
			"|number=" . $this->media_id . "\n" .
			"|source=" . $this->source . "\n".
			"|title=" . $this->title . "\n".
			"|byline=" . $this->reporter . "\n".
			"|date=<date name=\"Media\" page=\"Media\">" . $this->ic_date . "</date>\n" .
			"|next=" . ($next ? $next : 'None') . "\n".
			"|prev=" . ($prev ? $prev : 'None') . "\n".
			"}}\n".
			"[[Category:Media|" . $this->title . "]]\n"
			;
	}
	
	public function parse( $text ) {
		$xml = new SimpleXMLElement( "<?xml version='1.0' standalone='yes'?><root>$text</root>" );
		
		$this->media_id = strval( $xml->media_id );
		$this->title = strval( $xml->title );
		$this->source = strval( $xml->source );
		$this->reporter = strval( $xml->reporter );
		$this->ooc_date = strval( $xml->ooc_date );
		$this->ic_date = strval( $xml->ic_date );
	}
	
	public function save( $article ) {
		$result = true;
		$page_id = $article->getID();
	
		$dbw =& wfGetDB( DB_MASTER );
		$result &= $dbw->delete( 'media_info', array( "page_id = '$page_id'" ) );
		
		if ( $this->media_id ) {
			$result &= $dbw->insert( 'media_info', array(
				'page_id'		=> $page_id,
				'media_id' 		=> $this->media_id,
				'title' 		=> $this->title,
				'source' 		=> $this->source,
				'reporter' 		=> $this->reporter,
				'ooc_date' 		=> preg_replace( '/(\d\d).(\d\d).(\d\d\d\d)/', '$3-$1-$2', $this->ooc_date ),
				'ic_date' 		=> preg_replace( '/(\d\d).(\d\d).(\d\d\d\d)/', '$3-$1-$2', $this->ic_date ),
			));	
		}
		
		return $result;
	}
	
	public function delete( $article ) {
		$dbw =& wfGetDB( DB_MASTER );
		return $dbw->delete( 'media_info', array( "page_id = '" . $this->page_id . "'" ) );
	}
	
	public function getMediaLink( $media_id ) {
		$dbr =& wfGetDB( DB_SLAVE );
		$name = false;		
		$query = 
			"select p.page_id, p.page_namespace, p.page_title, m.title " . 
			"from page p, media_info m ".
			"where p.page_id = m.page_id ".
			"and m.media_id = '" . addSlashes( $media_id ). "'";
			
		$res = $dbr->query( $query );		
		while ( $row = $dbr->fetchObject( $res ) ) {
			$name = Title::makeName( $row->page_namespace, $row->page_title );
			$title = $row->title;
		}
		$dbr->freeResult( $res );

		return $name ? "[[$name|$title]]" : '';
	}

}

?>