<?


$wgExtensionFunctions[] = 'efMUSHSetup';
$wgHooks['ArticleSaveComplete'][] = 'efMediaInfoSave';
$wgHooks['ArticleDelete'][] = 'efMediaInfoDelete';
$wgHooks['ArticleDeleteComplete'][] = 'efMediaInfoDeleteComplete';
$wgHooks['ArticleSaveComplete'][] = 'efEventInfoSave';
$wgHooks['ArticleDelete'][] = 'efEventInfoDelete';
$wgHooks['ArticleDeleteComplete'][] = 'efEventInfoDeleteComplete';
$wgAutoloadClasses['MediaInfo'] = dirname(__FILE__) . '/MediaInfo.php';
$wgAutoloadClasses['Searchable'] = dirname(__FILE__) . '/Searchable.php';
$wgAutoloadClasses['MediaSearch'] = dirname(__FILE__) . '/MediaSearch.php';
$wgAutoloadClasses['MUSHRobot'] = dirname(__FILE__) . '/MUSHRobot.php';
$wgAutoloadClasses['LogUploader'] = dirname(__FILE__) . '/LogUploader.php';
$wgAutoloadClasses['LogScrubber'] = dirname(__FILE__) . '/LogScrubber.php';
$wgAutoloadClasses['EventInfo'] = dirname(__FILE__) . '/EventInfo.php';
$wgAutoloadClasses['EventSearch'] = dirname(__FILE__) . '/EventSearch.php';
$wgAutoloadClasses['EventPlanner'] = dirname(__FILE__) . '/EventPlanner.php';
$wgSpecialPages['LogUploader'] = 'LogUploader';
$wgSpecialPages['EventPlanner'] = 'EventPlanner';



function efMUSHSetup() {
    global $wgParser;
    $wgParser->setHook( 'mediainfo', 'efMediaInfoRender' );
    $wgParser->setHook( 'mediasearch', 'efMediaSearchRender' );
    $wgParser->setHook( 'loginfo', 'efLogInfoRender' );
    $wgParser->setHook( 'logsearch', 'efLogSearchRender' );
    $wgParser->setHook( 'eventinfo', 'efEventInfoRender' );
    $wgParser->setHook( 'eventsearch', 'efEventSearchRender' );
}
 
function efMediaInfoRender( $input, $args, $parser ) {
	$parser->disableCache();
	global $mediaInfo;
	$mediaInfo = new MediaInfo();
    $mediaInfo->parse( $input );
    return $parser->recursiveTagParse( $mediaInfo->render() );
}

function efMediaSearchRender( $input, $args, $parser ) {
	$parser->disableCache();
	$mediaSearch = new MediaSearch( $args );
    $mediaSearch->parse( $input );
	$output = $mediaSearch->render();
	return $output;
}

function efMediaInfoSave( &$article ) {
	global $mediaInfo;		
	if ( !$mediaInfo ) {
		$mediaInfo = new MediaInfo();
	}
	$mediaInfo->save( $article );
	return true;
}

function efMediaInfoDelete( &$article ) {
	global $mediaInfo;
	$mediaInfo = new MediaInfo();
	$mediaInfo->page_id = $article->getID();
	return true;
}

function efMediaInfoDeleteComplete( &$article ) {
	global $mediaInfo;
	$mediaInfo->delete( $article );
}

function efEventInfoRender( $input, $args, $parser ) {
	$parser->disableCache();
	global $eventInfo;
	$eventInfo = new EventInfo();
    $eventInfo->parse( $input );
    return $parser->recursiveTagParse( $eventInfo->render() );
}

function efEventSearchRender( $input, $args, $parser ) {
	$parser->disableCache();
	$eventSearch = new EventSearch( $args );
    $eventSearch->parse( $input );
	$output = $eventSearch->render();
	return $output;
}

function efEventInfoSave( &$article ) {
	global $eventInfo;		
	if ( !$eventInfo ) {
		$eventInfo = new EventInfo();
	}
	$eventInfo->save( $article );
	return true;
}

function efEventInfoDelete( &$article ) {
	global $eventInfo;
	$eventInfo = new EventInfo();
	$eventInfo->page_id = $article->getID();
	return true;
}

function efEventInfoDeleteComplete( &$article ) {
	global $eventInfo;
	$eventInfo->delete( $article );
}


?>