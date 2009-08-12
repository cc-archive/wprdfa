
function init() 
{
	cleanup : false,
	tinyMCEPopup.resizeToInnerSize();
}

/*
This is for to display the user selected text in tinyMCE editor.
The selected values will be displayed as a preview in pop up window.
*/
function getSelText(test)
{
    txt = tinyMCE.activeEditor.selection.getContent();
    // pre = document.getElementById('preview').value;
    window.document.RDFa.tx.value = txt;
    if(test==''){
    document.RDFa.preview.value = txt;  
    }    
}
 
/*
Get ontology selected by user.Here the ontology and the preview assign 
to values of hidden form. 
*/
function getOntology(select,preview) 
{
    var index;
    var onto;
    var closetag = document.getElementById('closetag');

	if( document.getElementById('preview').value == "" ) {
		window.document.RDFa.closing_tag.value = '1';
	}

	if( preview !="" ) {
     //window.document.RDFa.tx.value = document.getElementById('preview').value;
    }
    for(index=0; index<select.options.length; index++)
      if(select.options[index].selected) {
         if(select.options[index].value!="")
           onto=select.options[index].value;
           window.document.RDFa.tz.value = onto;
		   window.document.RDFa.selection.value = 'properties';
           window.document.forms['RDFa'].submit();
    }
}


/*
close the ontology 
*/
function closeOntology(suffix) 
{
    var closetag = document.getElementById('closetag');

	if(document.getElementById('preview').value != "") {
		preview = document.getElementById('preview').value;
		starting_tag = preview.substring(0,preview.indexOf(' '));
		closing = '</'+starting_tag.substring(1,starting_tag.length)+'>';
    	window.document.RDFa.preview.value = document.getElementById('preview').value + closing;
		window.document.RDFa.closing_tag.value = '';
    	closetag.style.display = "none";
    }
}

/*
add @About Url 
*/
function addAboutUrl() 
{
	var curie_value = document.RDFa.curie.selectedIndex;
	if(document.getElementById('at_about').value != '' && document.RDFa.curie.options[curie_value].value != '') {
	 preview = document.getElementById('preview').value;
	 window.document.RDFa.tx.value=preview;
	 window.document.RDFa.tz.value = document.getElementById('dublin_core').value;
	 window.document.RDFa.selection.value = 'about';
	 window.document.forms['RDFa'].submit();
	}
}


/*
Insert details to html editor as a RDFa format.
*/
function insertDetail() 
{
        var tagtext;
        var tagtext = document.getElementById('preview').value;
	window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, tagtext);
	tinyMCEPopup.editor.execCommand('mceRepaint');
	tinyMCEPopup.close();
	return;
}   

