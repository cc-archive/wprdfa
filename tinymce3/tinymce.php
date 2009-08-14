<?php

//Add to extended_valid_elements for TinyMCE
function my_change_mce_options($init) 
{
       // Command separated string of extended elements
       $ext = 'span[property|name|class|style|about|resource]';

       // Add to extended_valid_elements if it alreay exists
       if ( isset( $init['extended_valid_elements'] ) ) {
         $init['extended_valid_elements'] .= ',' . $ext;
       } else {
         $init['extended_valid_elements'] = $ext;
       }
	   $init['content_css'] = RDFa_URLPATH.'tinymce3/wpccrdfa_css.css';

      // Super important: return $init!
      return $init;
}

add_filter('tiny_mce_before_init', 'my_change_mce_options');


function rdfa_addbuttons() 
{
    // Don't bother doing this stuff if the current user lacks permissions
	if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;
	 
	// Add only in Rich Editor mode
	if ( get_user_option('rich_editing') == 'true') {
	 
	// add the button for wp25 in a new way
		add_filter("mce_external_plugins", "add_rdfa_tinymce_plugin", 5);
		add_filter('mce_buttons', 'register_rdfa_button', 5);
	}
}

// used to rdfa button in wordpress 2.5x editor
function register_rdfa_button($buttons) 
{
        array_push($buttons, "separator", "RDFa");
        return $buttons;
}

// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
function add_rdfa_tinymce_plugin($plugin_array) 
{    
        $plugin_array['RDFa'] = RDFa_URLPATH.'tinymce3/editor_plugin.js';
	return $plugin_array;
}

function rdfa_change_tinymce_version($version) 
{
	return ++$version;
}

// Modify the version when tinyMCE plugins are changed.
add_filter('tiny_mce_version', 'rdfa_change_tinymce_version');

// init process for button control
add_action('init', 'rdfa_addbuttons');

?>
