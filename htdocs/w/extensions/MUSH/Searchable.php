<?

class Searchable {

	protected $options = array();
	protected $dateColumn = 'date';

	public function __construct($args) {
		$this->format = array_key_exists('format', $args) && $args['format'] ? $args['format'] : 'list';
		$this->calendar_id = array_key_exists('id', $args) && $args['id'] ? $args['id'] : 'calendar';
		$this->options = $args;
	}

	public function render() {
		$smarty = efGetSmarty();
		$smarty->template_dir = dirname(__FILE__) . '/templates/';
		$smarty->assign('id', $this->calendar_id);
		$smarty->assign('dateColumn', $this->dateColumn);
		$smarty->assign('articles', $this->articles);

		$date = array_key_exists('calendar-' . $this->calendar_id, $_COOKIE)
				? $_COOKIE['calendar-' . $this->calendar_id] 
				: date('Y-m-d');

		$start = date('N', strtotime(substr($date, 0, 7) . '-01')) % 7;
		$daysinmonth = date('t', strtotime($date));
		$smarty->assign('date', strtotime($date));
		$smarty->assign('start', $start);
		$smarty->assign('daysinmonth', $daysinmonth);
		$smarty->assign('weeks', ceil(($start + $daysinmonth) / 7));

		$events = array();
		for ($i = 0; $i < 32; $i++)
			$events[$i] = array();
		
		foreach ($this->articles as $m) {
			if (substr($date, 0, 7) == substr($m[$this->dateColumn], 0, 7)) {
				$n = intval(substr($m[$this->dateColumn], 8, 2));
				$events[$n][] = $m;
			}
		}
				
		$smarty->assign('events', $events);
		$smarty->assign('months', array(
			'January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December'
		));

		$options = array();
		$endYear = array_key_exists('endYear', $options) && $options['endYear'] 
				? $options['endYear'] 
				: date('Y');
		
		$startYear = array_key_exists('startYear', $options) && $options['startYear'] 
				? $options['startYear'] 
				: $endYear - 4;

		$years = array();
		for ($year = $startYear; $year <= $endYear; $year++) {
			$years[] = $year;
		}
		
		$smarty->assign('years', $years);
		$smarty->assign('currentDate', ( date('m') == substr($date, 5, 2)) ? date('d') : 0 );
		return $smarty->fetch('searchable_' . $this->format . '.tpl');
	}

}

?>