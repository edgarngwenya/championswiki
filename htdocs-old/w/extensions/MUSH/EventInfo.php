<?
class EventInfo {

	public function __construct() {
	}
	
	public function render() {
	
		return
			"{{Infobox event ".
			"|date=" . $this->date . "\n" .
			"|time=" . $this->time . "\n" .
			"|summary=" . $this->summary . "\n" .
			"}}\n"
			;
	}
	
	public function parse( $text ) {
		$xml = new SimpleXMLElement( "<?xml version='1.0' standalone='yes'?><root>$text</root>" );
		
		$this->date = strval( $xml->date );
		$this->time = strval( $xml->time );
		$this->summary = strval( $xml->summary );
	}
	
	public function save( $article ) {
		$page_id = $article->getID();
	
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->delete( 'event_info', array( "page_id = '$page_id'" ) );
		
		if ( $this->date ) {
			$dbw->insert( 'event_info', array(
				'page_id'		=> $page_id,
				'date' 			=> $this->date,
				'time' 			=> $this->time,
				'summary' 		=> $this->summary
			));	
		}
	}
	
	public function delete( $article ) {
		$dbw =& wfGetDB( DB_MASTER );
		$dbw->delete( 'event_info', array( "page_id = '" . $this->page_id . "'" ) );
	}

}

?>