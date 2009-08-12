<?php



/*
Get the prefix
*/
function get_prefix($ontology,$onto_prefix) {
  if (array_key_exists($ontology, $onto_prefix)) {
    return $onto_prefix[$ontology];
  }
  
}

/*
Get the suffix 
*/
function get_suffix($ontology,$onto_suffix) {
 if (array_key_exists($ontology, $onto_suffix)) {
    return $onto_suffix[$ontology];
    }
    
}
/*
Get the last preview to display as a final preview.User can see the last preview
with prefix,suffix and ontology
*/
function get_last_display_preview($preview,$prefix,$suffix) {
  if($preview == NULL) {
    $last_preview = $prefix;
 }
 else {
    $last_preview = $prefix.$preview.$suffix;
 }

 return $last_preview;
}

/**
* Validate URL
* Allows for port, path and query string validations
* @param    string      $url	   string containing url user input
* @return   boolean     Returns TRUE/FALSE
*/
function validateURL($url) {
	$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
	return preg_match($pattern, $url);
}


?>
