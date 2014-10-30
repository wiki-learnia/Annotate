<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

class AnnotateDB {
    private $db = NULL;
    
    function __construct( ) {
        $this->db = wfGetDB(DB_MASTER);
    }
    
    //sharing related functions
    function delSharingPageEntry($page_id,$user_id){
	    $this->db->delete(
			'annotate_shared',
			array(
				'user_id' => $user_id,
				'page_id' => $page_id,
			),
			__METHOD__
		);
    }

    function delAllUserSharingPageEntries($user_id){
	    $this->db->delete(
			'annotate_shared',
			array(
				'user_id' => $user_id
			),
			__METHOD__
		);
    }
    
    function modSharingPageEntry($page_id,$user_id,$shared=NULL){
        //if sharing empty obey default
        if ($shared == NULL){
            $user = User::newFromId($user_id);
            $shared = $user->getOption('anSharedByDefault');
        }
    
        //get db row
		$res = $this->db->select(
			'annotate_shared',
			array('shared'),
			array('user_id'=>$page_id,
			      'page_id'=>$user_id),
			__METHOD__,
			array()
		);

		if($res->numRows() == 0){
		    //if not exist. create
			$this->db->insert(
				'annotate_shared',
				array(
					'user_id'=> $user_id,
					'page_id'=>$page_id,
					'shared'=>$shared
				),
				__METHOD__,
				array()
			); 
		}else{
		    //otherwise update
		    if ($res->fetchObject()->$shared != $shared)
			    $this->db->update(
				    'annotate_shared',
				    array(
					    'shared'=>$shared
				    ),
				    array(
					    'user_id'=> $user_id,
					    'page_id'=>$page_id
				    ),
				    __METHOD__,
				    array()
			    );
		}
    }
    
    function isPageShared($page_id,$user_id){
        //if no entry obey default for user
        //if entry: entry
        //get db row
        
		$res = $this->db->select(
			'annotate_shared',
			array('shared'),
			array('user_id'=>$page_id,
			      'page_id'=>$user_id,),
			__METHOD__,
			array()
		);

		if ($res->numRows()){
		    return $res->fetchObject()->shared;
		}else{
            $user = User::newFromId($user_id);
            return $user->getOption('anSharedByDefault');
		}
		
    }
    
    //annotation related functions
    function delAnnotationsByUser($user_id){
		$this->db->delete(
			'annotate_annotations',
			array(
				'user_id' => $user_id,
			),
			__METHOD__
		);
    }
    function delAnnotationsByPage($page_id){
		$this->db->delete(
			'annotate_annotations',
			array(
				'page_id' => $page_id,
			),
			__METHOD__
		);
    }
    function delAnnotationsByPageAndUser($page_id,$user_id){
		$this->db->delete(
			'annotate_annotations',
			array(
				'user_id' => $user_id,
				'page_id' => $page_id,
			),
			__METHOD__
		);
    }
    function delAnnotationByIdAndPageAndUser($id,$user_id,$page_id){
		$this->db->delete(
			'annotate_annotations',
			array(
				'annotation_id' => $id,
				'user_id' => $user_id,
				'page_id' => $page_id
			),
			__METHOD__
		);
    }
    function getAnnotationByUserAndPage($user_id,$page_id){
        $res = $this->db->select(
            'annotate_annotations',
            array('annotation_id','revision_id','annotations'),
            array(
                'user_id' => $user_id,
                'page_id' => $page_id,
            ),
            __METHOD__,
            array('ORDER BY' => 'annotation_id')

        );
        $annotations=array();
        foreach($res as $row){
            $annotation = json_decode($row->annotations );
            $annotation->revision_id = $row->revision_id;
            array_push($annotations,$annotation);   
        }
        return $annotations;
    }
    function getAnnotationByUserAndPageAndRevId($user_id,$page_id,$rev_id){
        $res = $this->db->select(
            'annotate_annotations',
            array('annotation_id','revision_id','annotations'),
            array(
                'user_id' => $user_id,
                'page_id' => $page_id,
                'revision_id' => $rev_id,
            ),
            __METHOD__,
            array('ORDER BY' => 'annotation_id')

        );
        $annotations=array();
        foreach($res as $row){
            $annotation = json_decode($row->annotations );
            $annotation->revision_id = $row->revision_id;
            array_push($annotations,$annotation);   
        }
        return $annotations;
    }
    
    function getAnnotationByPage($page_id){
		$res = $this->db->select(
			'annotate_annotations',
			array('annotation_id','revision_id','annotations'),
			array(
				'page_id' => $page_id,
			),
			__METHOD__,
			array('ORDER BY' => 'annotation_id')

		);
		$annotations=array();
		foreach($res as $row){
		    $annotation = json_decode($row->annotations );
		    $annotation->revision_id = $row->revision_id;
			array_push($annotations,$annotation);	
		}
		return $annotations;
    }

    function modAnnotation($user_id,$page_id,&$annotation,$revision_id){
    	//if no id we create a new annotation and return with id.
    	//if id we just update, if created previously we update it with id too 
		if(!$annotation->id){
			//no id -> create
			$res = $this->db->insert(
				'annotate_annotations',
				array(
					'user_id'=> $user_id,
					'page_id'=>$page_id,
					'revision_id'=>$revision_id,
				),
				__METHOD__,
				array()
			);
			//now get the id
			$res = $this->db->select(
				'annotate_annotations',
				array('id'=>'LAST_INSERT_ID()'),
				array(),
				__METHOD__,
				array()
			);
			
			$annotation->id = $res->fetchObject()->id;
		}

		//updating / filling annotation in
		$this->db->update(
			'annotate_annotations',
			array(
				'annotations' =>json_encode($annotation),
				'revision_id' => $revision_id,
			),
			array(
				'user_id' => $user_id,
				'page_id' => $page_id,
				'annotation_id' => $annotation->id
			),
			__METHOD__,
			array()
		);
    }


    function updateAnnotation(&$annotation,$revision_id){
		//updating / filling annotation in
		$this->db->update(
			'annotate_annotations',
			array(
				'annotations' =>json_encode($annotation),
				'revision_id' => $revision_id,
			),
			array(
				'annotation_id' => $annotation->id
			),
			__METHOD__,
			array()
		);
    }

    function filterOutSharingFriends($friends,$page_id){
    	$sharingFriends = array();
		foreach($friends as $friend){
		    if($this->isPageShared($page_id, $friend['user_id'])){
		    	$sharingFriends[] = $friend;
		    }
		}
		return $sharingFriends;
    }

    //friend sharing related functions
    function getAllFriends($user){
    	$userRel = new UserRelationship($user->getName());
    	return $userRel->getRelationshipList();
    }

    function getSharingFriends($user,$page_id){
    	$friends = $this->getAllFriends($user);
    	return $this->filterOutSharingFriends($friends,$page_id);
    }

    function addAnnotationsToFriendList(&$friends,$page_id){
        foreach ($friends as &$friend) {
            $annotations = $this->getAnnotationByUserAndPage($friend['user_id'],$page_id);
            $friend['annotations'] = $annotations;
            $friend['ann_count'] = sizeof($annotations);
        }
        //return $friends;
    }
    function addValidAnnotationsToFriendList(&$friends,$page_id,$rev_id){
        foreach ($friends as &$friend) {
            $annotations = $this->getAnnotationByUserAndPageAndRevId($friend['user_id'],$page_id, $rev_id);
            $friend['annotations'] = $annotations;
            $friend['ann_count'] = sizeof($annotations);
        }
        //return $friends;
    }
}
?>

