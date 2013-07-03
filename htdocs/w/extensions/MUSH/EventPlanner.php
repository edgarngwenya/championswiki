<?

class EventPlanner extends SpecialPage
{
	function MyExtension() {
			SpecialPage::SpecialPage("LogUploader");
			self::loadMessages();
	}

	function getDescription() {
		//wfMsg( strtolower( $this->mName ) );
		return 'Event Planner';
	}

	function getTitle() {
		//wfMsg( strtolower( $this->mName ) );
		return Title::makeTitle( NS_SPECIAL, 'EventPlanner' );
	}

	function execute( $par ) {
        global $wgRequest, $wgOut;

        $this->setHeaders();
		$wgOut->setPagetitle("Event Planner");
 
        # Get request data from, e.g.
        $param = $wgRequest->getText('param');
 
        # Do stuff
		$params = array(
			'name_method' => 'name',
			'logger' => 'Nobody',
		);
		$errors = array();

		if ( $wgRequest->wasPosted() ) {
			$params['name'] = $wgRequest->getVal( 'name' );
			$params['date'] = $wgRequest->getVal( 'date' );
			$params['time'] = $wgRequest->getVal( 'time' );
			$params['ampm'] = $wgRequest->getVal( 'ampm' );
			$params['summary'] = $wgRequest->getVal( 'summary' );

			$errors = $this->validateInput( $params, $articleTitle );			

			if ( !count($errors) ) {
				$text = $this->decorateEvent( $articleTitle, $params );
			
				// Create the article
				$t = Title::newFromText( $articleTitle, 108 );

				if ( $t->userCanCreate() ) {
					$summary = 'Log uploaded on ' . date( 'm-d-Y' );
					$a = new Article( $t );
					$a->doEdit( $text, $summary, EDIT_NEW );

					// Redirect
					$wgOut->redirect( $t->getFullURL() );
				}
				else {
					$errors['name_error'] = "You don't have permission to create the article; try logging in.";
				}
			}
		}

        $smarty = efGetSmarty();
		$smarty->template_dir = dirname(__FILE__) . '/templates/';
		$output = $smarty->fetch( 'event_planner.tpl' );
		
		$fill = new FillInForm();
		$output = $fill->fill( $output, $params, $errors );
		$wgOut->addHTML( $output );
	}

	function validateInput( $params, &$articleTitle ) {
		$errors = array();

		if ( !$params['name'] ) {
			$errors['name_error'] = "Please enter a name for this event.";
		}

		$articleTitle = $params['name'];
			
		if ( !$params['date'] ) {
			$errors['date_error'] = "Please enter the date of the event.";
		}
		else if ( !preg_match( '/^\d{1,2}[-\/]{1}\d{2}[-\/]{1}\d{4}$/', $params['date'] ) ) {
			$errors['date_error'] = "Date must be in the format MM/DD/YYYY.";
		}
		
		if ( !$params['time'] ) {
			$errors['time_error'] = "Please enter the time of the event.";
		}
		else if ( !preg_match( '/^\d{2}[:]{1}\d{2}$/', $params['time'] ) ) {
			$errors['time_error'] = "Time must be in the format HH:MM.";
		}

		if ( !$params['summary'] ) {
			$errors['summary_error'] = "Please enter a summary for this event.";
		}

		$t = Title::newFromText( $articleTitle, 108 );
		if ( $t && $t->exists() ) {
			$errors['name_error'] = "An event by this name already exists.";
		}

		return $errors;
	}

	function decorateEvent ( $articleTitle, $params ) {
		$time = $params['time'];
		$summary = $params['summary'];		
		$date = preg_replace( '/^(\d{1,2})[-\/]{1}(\d{1,2})[-\/]{1}(\d{4})$/', '$3-$1-$2', $params['date'] );
		
		$hour = substr( $time, 0, 2);
		if ( $params['ampm'] == 'pm' ) {
			$hour = intval( $hour ) + 12;
		}
		
		$time = $hour . ':' . substr( $time, 3, 5);
	
		$text = <<<EOT
<eventinfo>
  <date>$date</date>
  <time>$time</time>
  <summary>$summary</summary>
</eventinfo>
EOT;

		return $text;
	}	

}
