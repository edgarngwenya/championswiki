<?

class Searchable {

	protected $options = array();
	protected $dateColumn = 'date';
	
	public function __construct( $args ) {
		$this->format = $args['format'] ? $args['format'] : 'list';
		$this->calendar_id = $args['id'] ? $args['id'] : 'calendar';
		$this->options = $args;
	}


	public function render() {
		$smarty = efGetSmarty();
		$smarty->template_dir = dirname(__FILE__) . '/templates/';
		$smarty->assign( 'id', $this->calendar_id );
		$smarty->assign( 'dateColumn', $this->dateColumn );
		$smarty->assign( 'articles', $this->articles );
		return $smarty->fetch( 'searchable_' . $this->format . '.tpl' );
	}
}

?>