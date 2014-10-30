window.annos = {};

window.removeAnnoByKey = function(key){
    aContent.annotator('deleteAnnotation',window.annos[key]);
    delete window.annos[key];
    window.updateWrongAnnoList();
}


window.scrollToAnno = function(annotationKey){

    annotation = window.annos[annotationKey]
    xpath = annotation.ranges[0].start

    selector = xpath.replace('/',' > ').replace('[',':nth-of-type(').replace(']',')')
    selector = '#mw-content-text > div ' + selector
    console.log(selector)
    $('html, body').animate({scrollTop:($(selector).offset().top-180)},'slow');
}


window.updateWrongAnnoList = function(){

    $('#wrongAnnosBox').remove();
    if (Object.keys(window.annos).length > 0){

        list = '<ul class="wikiLearniaColumnBoxLinkList">'
        for (key in window.annos){
            li = '<li><div style="padding:0px 0px 0px 15px; background:url(\'/skins/wikilearnia/images/wikiLearniaLinkArrow.png\') no-repeat scroll left 4px transparent">'
            li = li+'\
                <div style="float:right;displat:inline">\
                    <a onclick="window.removeAnnoByKey('+key+');">X</a>\
                </div>\
                <div style="float:right;displat:inline">\
                    <a onclick="window.scrollToAnno('+key+');">Link</a>\
                    </div>\
                <p>Id: '+window.annos[key].id+'</p>\
                <p>Zitat: "'+window.annos[key].quote+'"</p>\
                <p>Anmerkung: "'+window.annos[key].text+'"</p>\
            '
            li = li+'</div></li>'

            list = list+li
        }

        list = list + '</ul>'

        $('.wikiLearniaContentColumnSidebar').append('\
            <div id="wrongAnnosBox" class="wikiLearniaColumnBox annotateWrongAnnoBox">\
              <span class="wikiLearniaColumnBoxTitle">\
               fehlerhafte Annotationen\
              </span>\
              \
              <div class="wikiLearniaColumnBoxContent" style="display: block;">\
                    <p>Annotationen die nicht automatisch an neue Revisionen angepasst werden konnten tauchen hier auf:</p> \
                    '+list+'\
              </div>\
            </div>'
        );
    }
}

var aContent = $('#mw-content-text').annotator()
aContent.annotator('addPlugin', 'Store', {
      // The endpoint of the store on your server.
      prefix: wgScriptPath+'/api.php',

      annotationData: {
      },

    });


/*
window.onload = function ()
{
	//create Event for deleted DOM elements
	(function($){
      $.event.special.destroyed = {
        remove: function(o) {
          if (o.handler) {
            o.handler()
          }
        }
      }
    })(jQuery)
    
    $("#mw-content-text").bind("destroyed",function(){
        //restore annotations
        window.annos = {};
        aContent = undefined;
        setTimeout(function(){
        alert("rem");
            aContent = $('#mw-content-text').annotator();
            aContent.annotator('addPlugin', 'Store', {
                // The endpoint of the store on your server.
                prefix: wgScriptPath+'/api.php',
                annotationData: {
                },
            });
        },3000);
    })
}
*/

window.restoreAnnotator = function(){
    //restore annotations
    window.annos = {};
    aContent = undefined;
    setTimeout(function(){
        aContent = $('#mw-content-text').annotator();
        aContent.annotator('addPlugin', 'Store', {
            // The endpoint of the store on your server.
            prefix: wgScriptPath+'/api.php',
            annotationData: {
            },
        });
    },600);
}

$('.annotateFriendSharedToggle').click(function(event){
	//toggling
  color = ''
	checkBox=$(event.target).children('input');
	checkBoxState = checkBox.attr("checked");
	if (checkBox.length == 0){
		checkBox=$(event.target);
		checkBoxState = checkBox.attr("checked");
		checkBoxState = !checkBoxState
    color = $(event.target).parent().children('div').css('background-color');
	} else{
		checkBox.attr("checked", !checkBoxState);
    color = $(event.target).children('div').css('background-color');
	}
	
	//find our annotations
	value = $(event.target).attr('value');
	if (checkBoxState){
		aContent.annotator('removeDOMAnnotationsByUID',value)
	}else{
		aContent.annotator('loadAnnotationsByUID',value,color)
	}
});



