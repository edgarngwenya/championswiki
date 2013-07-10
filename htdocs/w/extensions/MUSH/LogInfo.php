<?
class LogInfo {
	private $plot = false;
	private $sequenceNumber = false;
	private $date = false;
	private $cast = array();
	
	public function __construct() {
	}
	
	public function render() {
		return
			"{{Template:Log_Summary\n" .
			"|name={{PAGENAME}}\n" .
			"|plot=" . ( $this->plot ? "[[" . $this->plot . "]]" : "None" ) . "\n" .
			"|cast=" . $this->getCastLinks() . "\n" .
			"|prevscene=" . $this->getPreviousSceneLink() . "\n" .
			"|nextscene=" . $this->getNextSceneLink(). "\n" .
			"|date=<date name=\"Logs\" page=\"Logs\">" . $this->date . "</date>\n" .
			"}}"
			;
	}
	
	public function parse( $text ) {
		$xml = new SimpleXMLElement( "<?xml version='1.0' standalone='yes'?><root>$text</root>" );
		
		$this->plot = htmlspecialchars(strval( $xml->plot ));
		$this->date = htmlspecialchars(strval( $xml->date ));
		$this->sequenceNumber = htmlspecialchars(strval( $xml->sequence_number ));
		
		foreach ($xml->cast->children() as $member) {
			$this->cast[] = htmlspecialchars(strval($member));
		}
	}
	
	public function save( $article ) {
		$result = true;
		$page_id = $article->getID();
	
		$dbw =& wfGetDB( DB_MASTER );
		$result &= $dbw->delete( 'log_info', array( "page_id = '$page_id'" ) );		
		$result &= $dbw->insert( 'log_info', array(
			'page_id'	=> $page_id,
			'sequence_number' => htmlspecialchars($this->sequenceNumber),
			'plot' 		=> htmlspecialchars($this->plot),
			'date' 		=> htmlspecialchars(preg_replace( '/(\d\d).(\d\d).(\d\d\d\d)/', '$3-$1-$2', $this->date )),
		));	
		
		foreach ($this->cast as $member) {
			$result &= $dbw->insert( 'log_info_cast', array(
				'page_id' => $page_id,
				'person' => htmlspecialchars($member),
			));
		}
		
		return $result;
	}
	
	public function delete( $article ) {
		$dbw =& wfGetDB( DB_MASTER );
		return $dbw->delete( 'log_info', array( "page_id = '" . $this->page_id . "'" ) );
	}
	
	public function getCastLinks() {
		$text = '';
		
		foreach ($this->cast as $member) {
			$text .= ($text ? ', ' : '') . "[[$member]]";
		}
		
		return $text;
	}
	
	public function getPreviousSceneLink() {
		return $this->getSceneLink($this->plot, '<', $this->sequenceNumber, 'desc');
	}
	
	public function getNextSceneLink() {
		return $this->getSceneLink($this->plot, '>', $this->sequenceNumber, 'asc');
	}
	
	private function getSceneLink( $plot, $comparison, $sequenceNumber, $sortOrder ) {
		if (!$plot)
			return 'None';
		
		$dbr =& wfGetDB( DB_SLAVE );
		$name = false;		
		$query = 
			"select p.page_id, p.page_namespace, p.page_title " . 
			"from page p, log_info l ".
			"where p.page_id = l.page_id ".
			"and l.plot = '" . addSlashes( $plot ). "' " .
			"and l.sequence_number $comparison '" . addSlashes( $sequenceNumber ) . "' " . 
			"order by l.sequence_number $sortOrder";
			
		$res = $dbr->query( $query );		
		while ( $row = $dbr->fetchObject( $res ) ) {
			$name = Title::makeName( $row->page_namespace, $row->page_title );
			$title = $name;
			break;
		}
		$dbr->freeResult( $res );

		return $name ? "[[$name|$title]]" : 'None';
	}

}

?>