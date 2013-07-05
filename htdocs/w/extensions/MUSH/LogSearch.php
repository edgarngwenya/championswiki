<?

class LogSearch extends Searchable {

	public function __construct( $args ) {
		parent::__construct( $args );
		$this->dateColumn = 'date';
	}
	
	public function parse( $input ) {
		$terms = array();
		$xml = new SimpleXMLElement( "<?xml version='1.0' standalone='yes'?><root>$input</root>" );
		foreach ( $xml->term as $term ) {
			$t = array();
			foreach ( $term->attributes() as $k => $v ) {
				$t[$k] = strval($v);
			}
			$terms[] = $t;
		}
		
		$query =
			"select p.page_id, p.page_title, p.page_namespace, ".
			"m.ic_date, m.ooc_date, m.title, m.source, m.reporter ".
			"from page p, media_info m ".
			"where m.page_id = p.page_id ";
		
		
			
		$query .=
			"order by ooc_date desc ";
		
		$limit = array();
		if ( $xml->limit ) {
			foreach ( $xml->limit->attributes() as $k => $v ) {
				$limit[$k] = strval($v);
			}
		}
		
		if ( array_key_exists( 'start', $limit ) && array_key_exists( 'count', $limit ) ) {
			$query .= "limit " . $limit['start'] . ", " . $limit['count'] . " ";
		}
		else if ( array_key_exists( 'count', $limit ) ) {
			$query .= "limit " . $limit['count'] . " ";
		}

		$this->query = $query;
		
		global $wgContLang;
		$this->articles = array();
		$dbr =& wfGetDB( DB_SLAVE );
		$res = $dbr->query( $query );		
		while ( $row = $dbr->fetchObject( $res ) ) {
			$title = Title::newFromText( $row->page_title, $row->page_namespace );
			$this->articles[] = array(
				'page_id' => $row->page_id,
				'page_title' => $row->page_title,
				'page_namespace' => $row->page_namespace,
				'page_link' => $title->getLocalURL(),
				'name' => Title::makeName( $row->page_namespace, $row->page_title ),
				'ic_date' => $row->ic_date,
				'ooc_date' => $row->ooc_date,
				'title' => $row->title,
				'source' => $row->source,
				'reporter' => $row->reporter,
			);
		}
		$dbr->freeResult( $res );
	}
	


}


?>