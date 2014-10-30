<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}


$dir = __DIR__ . '/';
//require_once( 'Annotate.body.php' );
$wgAutoloadClasses['Annotate'] = $dir . 'Annotate.body.php';
$wgAutoloadClasses['AnnotateDB'] = $dir . 'Annotate.db.php';
$wgExtensionMessagesFiles[ 'Annotate' ] = $dir . 'Annotate.l18n.php';
$wgAutoloadClasses[ 'Migrate' ] = $dir . 'Migrate.php';
$wgDefaultUserOptions['anSharedByDefault'] = 1;


$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'Annotate',
	'author' =>'Tom Jaster', 
	'url' => 'http://tbd.com',
	'description' => 'This Extension lets you annotate your Wikisites.',
	'version'  => 13.37,
);

/*
$wgResourceModules['ext.annotate.base'] = array(
	'scripts' => 'js/annotator-full.min.js',
	'styles' => $wgAnnotateCSSFile,
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'Annotate',
	'dependancies' => 'jquery'
);*/
$wgAutoloadClasses['AnnotateAPI'] = dirname( __FILE__ )
        . '/AnnotateAPI.php';
 

$wgAPIModules['annotateapi'] = 'AnnotateAPI';
$wgHooks['BeforePageDisplay'][] = 'Annotate::onBeforePageDisplay';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'Annotate::AddTables';
$wgHooks['SkinBuildSidebar'][] = 'Annotate::buildSidebar';
$wgHooks['GetPreferences'][] = 'Annotate::wfPrefHook';
$wgHooks['ArticleDeleteComplete'][] = 'Annotate::onArticleDeleteComplete';
$wgHooks['DeleteAccount'][] = 'Annotate::onDeleteAccount';
$wgHooks['PageContentSaveComplete'][] = 'Migrate::onPageContentSaveComplete';
?>

 

