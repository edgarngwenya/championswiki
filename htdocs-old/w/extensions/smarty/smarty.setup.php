<?

$wgExtensionFunctions[] = 'efSmartySetup';
$wgAutoloadClasses['Smarty'] = dirname(__FILE__) . '/Smarty.class.php';

function efSmartySetup() {

}

function efGetSmarty() {
	$smarty = new Smarty();
	$smarty->compile_dir = dirname(__FILE__) . '/templates_c/';
	$smarty->cache_dir = dirname(__FILE__) . '/cache/';
	return $smarty;
}


?>