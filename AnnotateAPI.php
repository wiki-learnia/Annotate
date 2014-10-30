<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

class AnnotateAPI extends ApiBase {
    public function execute() {
		global $wgUser, $wgRequest;

		$dbAPI = new AnnotateDB();

        // Get the parameters
        $params = $this->extractRequestParams();
	 
		// Anon token check
		if ( !$wgUser->isLoggedIn() )
			$this->dieUsage( 'User is not logged in therefore we cant save/load annotations' );
		else {
			$token = '';
		}

		//params 
		$pageId = $params['pageid'];
		$revisionId = $params['revid'];
		$annotations = $params['annotations'];
		$userId = $wgUser->getId();
        $shared = $params['modSharing'];

        $action = $params['subaction'];
		if ($userId == 0){
			$this->dieUsage( 'User is not logged in therefore we dont save/load annotations' );	
		}
		

        if($annotations){
            //we are saving or deleting some annotations
            $annotations = ltrim($annotations,'"');//needed?
            $annotations = rtrim($annotations,'"');
            $json = json_decode($annotations);
        }
        //print $action;
        switch ($action) {
            case 'read':
                $userAnnos = $dbAPI->getAnnotationByUserAndPage($userId,$pageId);
                $friends = $dbAPI->getSharingFriends($wgUser,$pageId);
                $dbAPI->addValidAnnotationsToFriendList($friends,$pageId, $revisionId);

                $json = array('user' => $userAnnos);

                foreach ($friends as $friend) {

                    $json[$friend['user_id']] = $friend['annotations'];
                }
                break;
            
            case 'create':
                $dbAPI->modAnnotation($userId,$pageId,$json,$revisionId);
                $shared = ( $dbAPI->isPageShared($pageId,$userId) ? 1 : 0);
                $dbAPI->modSharingPageEntry($pageId,$userId,$shared);
                break;
            case 'update':
                $dbAPI->modAnnotation($userId,$pageId,$json,$revisionId);
                break;
            
            case 'destroy':
                $dbAPI->delAnnotationByIdAndPageAndUser($json->id,$userId,$pageId);
                break;

            case 'modsharing':
                $dbAPI->modSharingPageEntry($pageId,$userId,$shared);

            case 'search':
            default:
                exit();
                break;
        }

        $this->getResult()->addValue( null, $this->getModuleName(), array ( 'pageid' => $pageId ) );
        $this->getResult()->addValue( null, $this->getModuleName(), array ( 'revid' => $revisionId ) );
        $this->getResult()->addValue( null, $this->getModuleName(), array ( 'annotations' => $json ) );
        return true;
    }
 
    // Description
    public function getDescription() {
         return 'Lets a user save and restore annotations.';
     }
 
    // Face parameter.
    public function getAllowedParams() {
    //may be important
        return array(
            'user' => array (
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_REQUIRED => false
            ),
            'pageid' => array (
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_REQUIRED => true
            ),
            'revid' => array (
                ApiBase::PARAM_TYPE => 'integer',
                ApiBase::PARAM_REQUIRED => true
            ),
            'annotations' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false
            ),
            'subaction' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
            'modSharing' => array (
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false
            ),
        );
    }
 
    // Describe the parameter
    public function getParamDescription() {
        return array(
            'tbd' => 'tbd'
        );
    }
 
     // Get examples
     public function getExamples() {
         return array(
             'api.php?action=annotateapi'
         );
    }
     // Get examples
     public function getVersion() {
         return __CLASS__ . ': 0.1337';
    }
}
?>

