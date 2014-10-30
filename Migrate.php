<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}


function getHTMLByArticleAndRev($article,$rev=0){
	$articleTitle = $article->getTitle();
	     
	$apiParams = array(
                    'action' => 'parse',
                    'page' => $articleTitle->getPrefixedDBkey(),
    );
            
    $api = new ApiMain(
            new DerivativeRequest(
                    new WebRequest(),
                    $apiParams,
                    false 
            ),
            true 
    );

    $api->execute();
    $result = $api->getResultData();
    $content = isset( $result['parse']['text']['*'] ) ? $result['parse']['text']['*'] : false;
    $revision = Revision::newFromId( $result['parse']['revid'] );
    $timestamp = $revision ? $revision->getTimestamp() : wfTimestampNow();

    return $content;
}

function getStringForRange($range,$doc){
    $xpath = new DOMXpath($doc);
    
    /*
    $resultElements  = $xpath->query("/html/".$range->start."//text()");
    $startStr = '';
    foreach($resultElements as $element){
        $startStr .=$element->textContent;
    
    }
    $startStr = substr($startStr,$range->startOffset);
    error_log($startStr);
    */
    
    $resultElements  = $xpath->query("/html/".$range->start."//text()");
    $endStr = '';
    foreach($resultElements as $element){
        $endStr .=$element->textContent;
    
    }
    $endStr = substr($endStr,0,$range->endOffset);
    error_log($endStr);
    
    
    if ($range->start == $range->end){
        return substr($endStr,$range->startOffset);
        
    }
    
    $string='';
    return $string;
}


class Migrate {
    
    public static function onPageContentSaveComplete($article, $user, $content, $summary,
                $isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId  ) {
        
        error_log("running updating");
        $dbAPI = new AnnotateDB();
        
        $newHTML =getHTMLByArticleAndRev($article);
        
        $annotations = $dbAPI->getAnnotationByPage($article->getId());
        error_log(print_r($annotations,1));
        $newRevisionId = $article->getTitle()->getLatestRevID();
        
        //domtest
        $doc = new DOMDocument();
        $doc->loadHTML("<html>".$newHTML."</html>");
        
        //update annotations
        //First check if annotations are still valid and update their revId
        $changedAnnos = array();
        foreach($annotations as $ann){
             $newQuote = '';
             foreach($ann->ranges as $range){
                $newQuote.=getStringForRange($range,$doc);
             }
             if ($ann->quote == $newQuote){
                //matched annotation, still valid, updating
                $dbAPI->updateAnnotation($ann,$newRevisionId);
                error_log("ran update revision function");
             }
        }
        return True;
    }
}


?>

