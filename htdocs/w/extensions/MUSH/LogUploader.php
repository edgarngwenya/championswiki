<?

class LogUploader extends SpecialPage {

	function __construct() {
		parent::__construct('LogUploader', 'uploadlog');
		//wfLoadExtensionMessages('MyExtension');
	}

	/*
	  function getDescription() {
	  //wfMsg( strtolower( $this->mName ) );
	  return 'Log Uploader';
	  }

	  function getTitle() {
	  //wfMsg( strtolower( $this->mName ) );
	  return Title::makeTitle( NS_SPECIAL, 'LogUploader' );
	  }
	 */

	function execute($par) {
		global $wgRequest, $wgOut;

		if (!$this->userCanExecute($this->getUser())) {
			$this->displayRestrictionError();
			return;
		}

		$this->setHeaders();
		$wgOut->setPagetitle("Log Uploader");

		# Get request data from, e.g.
		$param = $wgRequest->getText('param');

		# Do stuff
		$params = array(
			'name_method' => 'name',
			'logger' => 'Nobody',
		);
		$errors = array();

		if ($wgRequest->wasPosted()) {
			$params['name'] = $wgRequest->getVal('name');
			$params['name_method'] = $wgRequest->getVal('name_method');
			$params['plot'] = $wgRequest->getVal('plot');
			$params['date'] = $wgRequest->getVal('date');
			$params['logger'] = $wgRequest->getVal('logger');
			$params['file'] = $wgRequest->getFileTempname('file');

			$errors = $this->validateInput($params, $articleTitle);



			if (!count($errors)) {
				$data = $this->scrub($articleTitle, $params);
				$text = $this->decorateLog($articleTitle, $data, $params);

				// Create the article
				$t = Title::newFromText($articleTitle, 104);
				$summary = 'Log uploaded on ' . date('m-d-Y');
				$a = new Article($t);
				$a->doEdit($text, $summary, EDIT_NEW);

				// Redirect
				$wgOut->redirect($t->getFullURL());
			}
		}

		$smarty = efGetSmarty();
		$smarty->template_dir = dirname(__FILE__) . '/templates/';
		$output = $smarty->fetch('log_uploader.tpl');

		$fill = new FillInForm();
		$output = $fill->fill($output, $params, $errors);
		$wgOut->addHTML($output);
	}

	function loadMessages() {
		static $messagesLoaded = false;
		global $wgMessageCache;
		if (!$messagesLoaded) {
			$messagesLoaded = true;

			require( dirname(__FILE__) . '/LogUploader.i18n.php' );
			foreach ($allMessages as $lang => $langMessages) {
				$wgMessageCache->addMessages($langMessages, $lang);
			}
		}
		return true;
	}

	function validateInput($params, &$articleTitle) {
		$errors = array();

		if ($params['name_method'] == 'plot') {
			if (!$params['plot']) {
				$errors['name_error'] = "Please enter the name of the plot.";
			}

			$count = 1;
			$articleTitle = sprintf('%s, Scene %d', $params['plot'], $count);
			while (Title::newFromText($articleTitle, 104)->exists()) {
				$articleTitle = sprintf('%s, Scene %d', $params['plot'], ++$count);
			}
		} else {
			if (!$params['name']) {
				$errors['name_error'] = "Please enter a title for the log.";
			}

			$articleTitle = $params['name'];
		}

		if (!$params['date']) {
			$errors['date_error'] = "Please enter the date of the log.";
		} else if (!preg_match('/^\d{1,2}[-\/]{1}\d{2}[-\/]{1}\d{4}$/', $params['date'])) {
			$errors['date_error'] = "Date must be in the format MM/DD/YYYY.";
		}

		if (!$params['file']) {
			$errors['file_error'] = "Please select a log file to upload.";
		}

		if (!$params['logger']) {
			$errors['logger_error'] = "Please enter the name of the character who logged the scene.";
		}

		$t = Title::newFromText($articleTitle, 104);
		if ($t && $t->exists()) {
			$errors['name_error'] = "An article by this name already exists.";
		}

		return $errors;
	}

	function scrub($articleTitle, $params) {
		$log = new LogScrubber();
		$data = $log->scrub($params['file'], $params);
		return $data;
	}

	function decorateLog($articleTitle, $data, $params) {
		$text = $data['text'];

		preg_match('/^(\d\d)[-\/]{1}(\d\d)[-\/]{1}(\d\d\d\d)$/', $params['date'], $match);
		$date = sprintf('%s/%s/%s', $match[3], $match[1], $match[2]);
		$plot = ( $params['plot'] ) ? $params['plot'] : '';
		$text .= "\n\n[[Category:Logs|$articleTitle]] [[Category:Logs $date]]";

		$cast = '';
		foreach ($data['cast'] as $person) {
			$cast .= "\t<character>$person</character>\n";
		}

		$sequenceNumber = 1;

		if (preg_match('/^(.*), Scene (\d+)$/', $articleTitle, $match)) {
			$sequenceNumber = $match[2];
		}

		$template = <<<EOT
<loginfo>
<plot>$plot</plot>
<date>$date</date>
<cast>
$cast
</cast>
<sequence_number>$sequenceNumber</sequence_number>
</loginfo>

EOT;

		$text = $template . "\n" . $text;
		return $text;
	}

}
