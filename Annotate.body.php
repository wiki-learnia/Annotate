<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

class Annotate {

	function onBeforePageDisplay($out,$skin){
        // Here we are adding all the js assets we are using for the annotator
		global $wgUser,$wgScriptPath,$wgArticle;
		if ( $wgUser->isLoggedIn() && $skin->getTitle()->isContentPage()&& !$skin->getTitle()->isMainPage() ){
			$wgAnnotateJSFile  = $wgScriptPath."/extensions/Annotate/libs/js/annotator-full.min.js";
			$wgAnnotateCSSFile = $wgScriptPath."/extensions/Annotate/libs/css/annotator.min.css";
			$wgAnnotateJSMain  = $wgScriptPath."/extensions/Annotate/js/main.js";
            //$user= new UserRelationship("wikiadmin");
            //print_r($user->getRelationshipList());
            //print_r($wgArticle->getTitle()->mArticleID);

			//I srsly dont know why this wont work!!!
			//$out->addModules('ext.annotate.base');
			$out->addScriptFile($wgAnnotateJSFile, 1);
			$out->addStyle($wgAnnotateCSSFile, 1);
			$out->addScriptFile($wgAnnotateJSMain, 1);
		}
		return true;
	}

	
	function AddTables( DatabaseUpdater $updater ) {
        //setup of datastructure on plugininstall
		$updater->addExtensionTable( 'annotate_annotations',
		        dirname( __FILE__ ) . '/annotateTables.sql', true );
		return true;
	}
    
	function buildSidebar( $skin, &$bar ) {
        //Our Annotationsharing sidebar
        global $wgUser, $data, $wgRequest,$wgUser;
        $wikiPage = $skin->getTitle();
        $revisionId = $wikiPage->getLatestRevID();

        if($wgUser->isLoggedIn() && $wikiPage->isContentPage() && !$wikiPage->isMainPage()) {
            $dbAPI = new AnnotateDB();
            $pageId = $skin->getWikiPage()->getId();
            $sharingFriends = $dbAPI->getSharingFriends($wgUser,$pageId);
            $dbAPI->addValidAnnotationsToFriendList($sharingFriends,$pageId, $revisionId);
            $bar['annotateFriendBox'] = '
                  <ul class="wikiLearniaColumnBoxLinkList" style="display:block">';
            $colors = array('rgb(255,199,199)',
                                'rgb(255,241,199)',
                                'rgb(227,255,199)',
                                'rgb(199,255,213)',
                                'rgb(199,255,255)',
                                'rgb(199,213,255)',
                                'rgb(227,199,255)',
                                'rgb(255,199,241)',
                                'rgb(255,143,143)',
                                'rgb(255,227,143)',
                                'rgb(199,255,143)',
                                'rgb(143,255,171)',
                                'rgb(143,255,255)',
                                'rgb(143,171,255)',
                                'rgb(199,143,255)',
                                'rgb(255,143,227)',
                                'rgb(217,121,121)',
                                'rgb(217,193,121)',
                                'rgb(169,217,145)',
                                'rgb(121,217,217)',
                                'rgb(121,145,217)',
                                'rgb(169,121,217)',
                                'rgb(217,121,193)',
                                'rgb(217,169,169)',
                                'rgb(217,205,169)',
                                'rgb(193,217,169)',
                                'rgb(169,217,181)',
                                'rgb(169,217,217)',
                                'rgb(169,181,217)',
                                'rgb(193,169,217)',
                                'rgb(217,169,205)'
            );
            $idx = 0;
            $length = count($colors);
            foreach ($sharingFriends as $friend) {
                if ($friend['ann_count'] >0){
                    if ($idx = $length)
                        $idx = 0;
                    $bar['annotateFriendBox'] .=
                        '<li title="'.$friend['user_name'].'">
                            <a href="javascript:void(0)" class="markedLink annotateFriendSharedToggle"  value="'.$friend['user_id'].'"><input autocomplete="off" type="checkbox" name="'.$friend['user_name'].'" value="'.$friend['user_id'].'"/>
                         '.''.$friend['user_name'].' ('.$friend['ann_count'].' <div style="background-color:'.$colors[$idx].';display:inline-block;width:8px;height:8px;border:1px solid #000" ></div>)
                        </a>
                       </li>';
                    $idx = $idx +1;
                }
            }
                   
            $bar['annotateFriendBox'] .= '</ul>
                <p style="display:none">Keiner deiner Freunde hat Annotationen (freigegeben).</p> ';
        }
        return true;
    }
    
    function wfPrefHook( $user, &$preferences ) {
            // User profile page setting for annotation sharing default
            $preferences['anSharedByDefault'] = array(
                    'type' => 'toggle',
                    'label-message' => 'annot-def-sharing-toggle', // a system message
                    'section' => 'annotationen/freigabe',
            );
            // Required return value of a hook function.
            return true;
    }
    function onArticleDeleteComplete( &$article, User &$user, $reason, $id ) {
        // if a page is deleted we shall also delete all annotations... 
        // or better not. mediawiki lets you restore a whole lot of shit
        
    }
    
    function onDeleteAccount( &$deletedUser ) {
        //user weg - daten weg
        $db = new AnnotateDB();
        $db->delAllUserSharingPageEntries($deletedUser->getId());
        return True;
    }
}
?>
