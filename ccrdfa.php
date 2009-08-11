<?php
/**
 * @package ccRDFa
 * @author Dinishika nuwangi
 * @version 1.3.12
 */
 
/**
Plugin Name: Creative Commons RDFa plugin
Plugin URI:  http://dinishi.com/
Description: Creative Commons RDFa plugin (GSoC 2009)
Author:      Dinishika Nuwangi
Author URI:  http://dinishi.com/
Version:     1.3.12
License:     GNU General Public License
*/

define('IS_WP25', version_compare($wp_version, '2.4', '>=') );
$myabspath = str_replace("\\","/",ABSPATH);  // required for Windows & XAMPP
define('WINABSPATH', $myabspath);
define('RDFaFOLDER', dirname(plugin_basename(__FILE__)));
define('RDFa_ABSPATH', $myabspath.'wp-content/plugins/' . RDFaFOLDER .'/');
define('RDFa_URLPATH', get_option('siteurl').'/wp-content/plugins/' . RDFaFOLDER.'/');

if (IS_WP25)
	include_once (dirname (__FILE__)."/tinymce3/tinymce.php");
else
	include_once (dirname (__FILE__)."/tinymce/tinymce.php");


if ( !function_exists ('add_action') ) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

// Pre-2.6 compatibility
if ( !defined('WP_CONTENT_URL') )
	define( 'WP_CONTENT_URL', get_option('url') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( !defined('WP_CONTENT_FOLDER') )
	define( 'WP_CONTENT_FOLDER', str_replace(ABSPATH, '/', WP_CONTENT_DIR) );

// send file for save
if ( isset( $_GET['ccrdfa_export'] ) ) {
	wpccrdfa_export();
	die();
}

/**
 * active for multilanguage
 *
 * @package ccRDFa
 */
function wpccrdfa_textdomain() {

	if ( function_exists('load_plugin_textdomain') ) {
		if ( !defined('WP_PLUGIN_DIR') ) {
			load_plugin_textdomain('ccrdfa', str_replace( ABSPATH, '', dirname(__FILE__) ) . '/languages');
		} else {
			load_plugin_textdomain('ccrdfa', false, dirname( plugin_basename(__FILE__) ) . '/languages');
		}
	}
}


/**
 * install options in table _options
 *
 * @package ccRDFa
 */
function wpccrdfa_install() {
	
	$ccrdfaTagSettings = array(
																'tags' => array(
																									array(  
																												'ontology'  => 'Title',
																												'prefix' => '<span property="dc:title">',
																												'suffix'   => '</span>'
																												)
																									)
																);
	add_option('ccrdfaTagSettings', $ccrdfaTagSettings);
}


/**
 * install options in table _options
 *
 * @package ccRDFa
 */
function wpccrdfa_reset() {
	
	$ccrdfaTagSettings = array(
																'tags' => array(
																									array(
																												'ontology'  => 'Reset',
																												'prefix' => '<reset>',
																												'suffix'   => '</reset>'
																												)
																									)
																);
	update_option('ccrdfaTagSettings', $ccrdfaTagSettings);
}


/**
 * uninstall options in table _options
 *
 * @package ccRDFa
 */
function wpccrdfa_uninstall() {
	
	delete_option('ccrdfaTagSettings');
}


/**
 * export options in file 
 *
 * @package ccRDFa
 */
function wpccrdfa_export() {
	global $wpdb;
	$filename = 'wpccrdfa_export-' . date('Y-m-d_G-i-s') . '.wpccrdfa';
		
	header("Content-Description: File Transfer");
	header("Content-Disposition: attachment; filename=" . urlencode($filename));
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header('Content-Type: text/wpccrdfa; charset=' . get_option('blog_charset'), true);
	flush();
		
	$wpccrdfa_data = mysql_query("SELECT option_value FROM $wpdb->options WHERE option_name = 'ccrdfaTagSettings'");
	$wpccrdfa_data = mysql_result($wpccrdfa_data, 0);
	echo $wpccrdfa_data;
	flush();
}

/**
 * import options in table _options
 *
 * @package ccRDFa
 */
function wpccrdfa_import() {
	
	if ( !current_user_can('manage_options') )
		wp_die( __('Options not update - you don&lsquo;t have the privilidges to do this!', 'secure_wp') );

	//cross check the given referer
	check_admin_referer('rmnl_nonce');

	// check file extension
	$str_file_name = $_FILES['datei']['name'];
	$str_file_ext  = explode(".", $str_file_name);

	if ($str_file_ext[1] != 'wpccrdfa') {
		$addreferer = 'notexist';
	} elseif (file_exists($_FILES['datei']['name'])) {
		$addreferer = 'exist';
	} else {
		// path for file
		$str_ziel = WP_CONTENT_DIR . '/' . $_FILES['datei']['name'];
		// transfer
		move_uploaded_file($_FILES['datei']['tmp_name'], $str_ziel);
		// access authorisation
		chmod($str_ziel, 0644);
		// SQL import
		ini_set('default_socket_timeout', 120);
		$import_file = file_get_contents($str_ziel);
		wpccrdfa_reset();
		$import_file = unserialize($import_file);
		update_option('ccrdfaTagSettings', $import_file);
		unlink($str_ziel);
		$addreferer = 'true';
	}

	$referer = str_replace('&update=true&update=true', '', $_POST['_wp_http_referer'] );
	wp_redirect($referer . '&update=' . $addreferer );
}

/**
 * options page in backend of WP
 *
 * @package ccRDFa
 */
function wpccrdfa_options_page() {
	global $wp_version;
	
	if ($_POST['wpccrdfa']) {
		if ( current_user_can('edit_plugins') ) {
			check_admin_referer('rmnl_nonce');

			$tags = array();
			for ($i = 0; $i < count($_POST['wpccrdfa']['tags']); $i++){
				$b = $_POST['wpccrdfa']['tags'][$i];
				if ($b['ontology'] != '' && $b['prefix'] != '') {
					$b['ontology']    = ($b['ontology']);
					$b['prefix']   = stripslashes($b['prefix']);
					$b['suffix']     = stripslashes($b['suffix']);
					$tags[]    = $b;
				}
			}
			$_POST['wpccrdfa']['tags'] = $tags;
			update_option('ccrdfaTagSettings', $_POST['wpccrdfa']);
			$message = '<br class="clear" /><div class="updated fade"><p><strong>' . __('Options saved.', 'ccrdfa') . '</strong></p></div>';

		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.', 'ccrdfa').'</p>');
		}
	}

	// Uninstall options
	if ( ($_POST['action'] == 'uninstall') ) {
		if ( current_user_can('edit_plugins') ) {

			check_admin_referer('rmnl_nonce');
			wpccrdfa_uninstall();
			$message_export = '<br class="clear" /><div class="updated fade"><p>';
			$message_export.= __('RDFa Ontologies have been deleted!', 'ccrdfa');
			$message_export.= '</p></div>';

		} else {
			wp_die('<p>'.__('You do not have sufficient permissions to edit plugins for this blog.', 'ccrdfa').'</p>');
		}
	}
	
	$string1 = __('Add or delete RDFa Ontologies', 'ccrdfa');
	$string2 = __('Fill in the fields below to add or edit the RDFa ontology. Fields with * are required. To delete a tag simply empty all fields.', 'ccrdfa');
	$field1  = __('Ontology Label*', 'ccrdfa');
	$field2  = __('Prefix Tag(s)*', 'ccrdfa');
	$field3  = __('Suffix Tag(s)', 'ccrdfa');
	$button1 = __('Update &raquo;', 'ccrdfa');

	// Export strings
	$button2 = __('Export &raquo;', 'ccrdfa');
	$export1 = __('Export/Import RDFa Ontologies', 'ccrdfa');
	$export2 = __('You can save a .wpccrdfa file with your RDFa Ontologies.', 'ccrdfa');
	$export3 = __('Export', 'ccrdfa');

	// Import strings
	$button3 = __('Upload file and import &raquo;', 'ccrdfa');
	$import1 = __('Import', 'ccrdfa');
	$import2 = __('Choose a RDFa Ontologies (<em>.wpccrdfa</em>) file to upload, then click <em>Upload file and import</em>.', 'ccrdfa');
	$import3 = __('Choose a file from your computer: ', 'ccrdfa');

	// Uninstall strings
	$button4    = __('Uninstall Options &raquo;', 'ccrdfa');
	$uninstall1 = __('Uninstall options', 'ccrdfa');
	$uninstall2 = __('This button deletes all RDFa ontologies of the plugin. <strong>Attention: </strong>You cannot undo this!', 'ccrdfa');

	// Info
	$info0   = __('About the plugin', 'ccrdfa');
	$info1   = __('Further information: Visit the <a href=\'http://dinishi.com/\'>plugin homepage</a> for further information or to grab the latest version of this plugin.', 'ccrdfa');
	
	// message for import, after redirect
	if ( strpos($_SERVER['REQUEST_URI'], 'ccrdfa.php') && $_GET['update'] && !$_POST['uninstall'] ) {
		$message_export = '<br class="clear" /><div class="updated fade"><p>';
		if ( $_GET['update'] == 'true' ) {
			$message_export .= __('RDFa Ontologies imported!', 'ccrdfa');
		} elseif( $_GET['update'] == 'exist' ) {
			$message_export .= __('File is exist!', 'ccrdfa');
		} elseif( $_GET['update'] == 'notexist' ) {
			$message_export .= __('Invalid file extension!', 'ccrdfa');
		}
		$message_export .= '</p></div>';
	}
	
	$o = get_option('ccrdfaTagSettings');
	
	?>
	<div class="wrap">
		<h2><?php _e('RDFa Management', 'ccrdfa'); ?></h2>
		<?php echo $message . $message_export; ?>
		<br class="clear" />
		<div id="poststuff" class="ui-sortable">
			<div class="postbox">
				<h3><?php echo $string1; ?></h3>
				<div class="inside">
					<br class="clear" />
					<form name="form1" method="post" action="">
						<?php wp_nonce_field('rmnl_nonce'); ?>
						<table summary="rmnl" class="widefat">
							<thead>
								<tr>
									<th scope="col"><?php echo $field1; ?></th>
									<th scope="col"><?php echo $field2; ?></th>
									<th scope="col"><?php echo $field3; ?></th>
								</tr>
							</thead>
							<tbody>
				<?php
					for ($i = 0; $i < count($o['tags']); $i++) {
						$b          = $o['tags'][$i];
						$b['ontology']  = htmlentities(stripslashes($b['ontology']), ENT_COMPAT, get_option('blog_charset'));
						$b['prefix'] = htmlentities($b['prefix'], ENT_COMPAT, get_option('blog_charset'));
						$b['suffix']   = htmlentities($b['suffix'], ENT_COMPAT, get_option('blog_charset'));
						$nr         = $i + 1;
						echo '
								<tr valign="top">
									<td><input type="text" name="wpccrdfa[tags][' . $i . '][ontology]" value="' . $b['ontology'] . '" style="width: 95%;" /></td>
									<td><textarea class="code" name="wpccrdfa[tags][' . $i . '][prefix]" rows="2" cols="25" style="width: 95%;">' . $b['prefix'] . '</textarea></td>
									<td><textarea class="code" name="wpccrdfa[tags][' . $i . '][suffix]" rows="2" cols="25" style="width: 95%;">' . $b['suffix'] . '</textarea></td>
								</tr>
						';
					}
					?>
								<tr valign="top">
									<td><input type="text" name="wpccrdfa[tags][<?php _e( $i ); ?>][ontology]" value="" tyle="width: 95%;" /></td>
									<td><textarea class="code" name="wpccrdfa[tags][<?php _e( $i ); ?>][prefix]" rows="2" cols="25" style="width: 95%;"></textarea></td>
									<td><textarea class="code" name="wpccrdfa[tags][<?php _e( $i ); ?>][suffix]" rows="2" cols="25" style="width: 95%;"></textarea></td>
								</tr>
							</tbody>
						</table>
						<p><?php echo $string2; ?></p>
						<p class="submit">
							<input class="tags tags-primary" type="submit" name="Submit" value="<?php _e( $button1 ); ?>" />
						</p>
					</form>
		
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox closed">
				<h3><?php echo $export1; ?></h3>
				<div class="inside">
					
					<h4><?php echo $export3; ?></h4>
					<form name="form2" method="get" action="">
						<p><?php echo $export2; ?></p>
						<p id="submitbutton">
							<input class="button" type="submit" name="submit" value="<?php echo $button2; ?>" />
							<input type="hidden" name="ccrdfa_export" value="true" />
						</p>
					</form>
					
					<h4><?php echo $import1; ?></h4>
					<form name="form3" enctype="multipart/form-data" method="post" action="admin-post.php">
						<?php wp_nonce_field('rmnl_nonce'); ?> 
						<p><?php echo $import2; ?></p>
						<p>
							<label for="datei_id"><?php echo $import3; ?></label>
							<input name="datei" id="datei_id" type="file" />
						</p>
						<p id="submitbutton">
							<input class="button" type="submit" name="Submit_import" value="<?php echo $button3; ?>" />
							<input type="hidden" name="action" value="wpccrdfa_import" />
						</p>
					</form>
					
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox closed">
				<h3><?php echo $uninstall1; ?></h3>
				<div class="inside">
					
					<form name="form4" method="post" action="">
						<?php wp_nonce_field('rmnl_nonce'); ?>

						<p><?php echo $uninstall2; ?></p>
						<p id="submitbutton">
							<input class="button" type="submit" name="Submit_uninstall" value="<?php _e($button4); ?>" /> 
							<input type="hidden" name="action" value="uninstall" />
						</p>
					</form>
					
				</div>
			</div>
		</div>
		
		<div id="poststuff" class="ui-sortable">
			<div class="postbox closed">
				<h3><?php echo $info0; ?></h3>
				<div class="inside">

					<p><?php echo $info1; ?></p>
			
				</div>
			</div>
		</div>
		
		<script type="text/javascript">
		<!--
		<?php if ( version_compare( $wp_version, '2.6.999', '<' ) ) { ?>
		jQuery('.postbox h3').prepend('<a class="togbox">+</a> ');
		<?php } ?>
		jQuery('.postbox h3').click( function() { jQuery(jQuery(this).parent().get(0)).toggleClass('closed'); } );
		jQuery('.postbox.close-me').each(function(){
			jQuery(this).addClass("closed");
		});
		//-->
		</script>
		
	</div>
<?php
} //End function wpccrdfa_options_page


// add to wp
if ( function_exists('register_activation_hook') )
	register_activation_hook(__FILE__, 'wpccrdfa_install');
if ( function_exists('register_uninstall_hook') )
	register_uninstall_hook(__FILE__, 'wpccrdfa_uninstall');
if ( is_admin() ) {
	add_action('init', 'wpccrdfa_textdomain');
	add_action('admin_menu', 'wpccrdfa_add_settings_page');
	add_action('in_admin_footer', 'wpccrdfa_admin_footer');
	add_action('admin_post_wpccrdfa_import', 'wpccrdfa_import' );
}


/**
 * Add action link(s) to plugins page
 * 
 *
 * @package ccRDFa
 */
function wpccrdfa_filter_plugin_actions($links, $file){
	static $this_plugin;

	if( ! $this_plugin ) $this_plugin = plugin_basename(__FILE__);

	if( $file == $this_plugin ){
		$settings_link = '<a href="options-general.php?page=ccrdfa/ccrdfa.php">' . __('Settings') . '</a>';
		$links = array_merge( array($settings_link), $links); // before other links
	}
	return $links;
}


/**
 * @version WP 2.7
 * Add action link(s) to plugins page
 *
 * @package ccRDFa
 */
function wpccrdfa_filter_plugin_actions_new($links) {
 
	$settings_link = '<a href="options-general.php?page=ccrdfa/ccrdfa.php">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
 
	return $links;
}

/**
 * settings in plugin-admin-page
 *
 * @package ccRDFa
 */
function wpccrdfa_add_settings_page() {
	global $wp_version;
	
	if ( function_exists('add_options_page') && current_user_can('manage_options') ) {
		$plugin = plugin_basename(__FILE__);
		$menutitle = '';
		$menutitle .= __('RDFa', 'ccrdfa');

		add_options_page( __('RDFa', 'ccrdfa'), $menutitle, 9, $plugin, 'wpccrdfa_options_page');
		
		if ( version_compare( $wp_version, '2.6.999', '<' ) ) {
			add_filter('plugin_action_links', 'wpccrdfa_filter_plugin_actions', 10, 2);
		} else {
			add_filter( 'plugin_action_links_' . $plugin, 'wpccrdfa_filter_plugin_actions_new' );
		}
	}
}


/**
 * credit in wp-footer
 *
 * @package ccRDFa
 */
function wpccrdfa_admin_footer() {
	if( basename($_SERVER['REQUEST_URI']) == 'ccrdfa.php') {
		$plugin_data = get_plugin_data( __FILE__ );
		printf('%1$s plugin | ' . __('Version') . ' %2$s | ' . __('Author') . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author']);
	}
}


/**
 * Add to extended_valid_elements for TinyMCE
 *
 * @param $init assoc. array of TinyMCE options
 * @return $init the changed assoc. array
 */
function tmve_mce_valid_elements( $init ) {

    $eleList = array();
            $eleList[0] = "span[class|title|width|height|align|about|property]";

    // Extended string to add to tinyMCE.init();
    $extStr = implode(',', $eleList);
    // Only add ext valid ele's if a correct string was made
    if ( $extStr != null && $extStr != '' ) {
        // Add to extended_valid_elements if it alreay exists
        if ( isset( $init['extended_valid_elements'] ) 
               && ! empty( $init['extended_valid_elements'] ) ) {
            $init['extended_valid_elements'] .= ',' . $extStr;
        } else {
            $init['extended_valid_elements'] = $extStr;
        }
    }

    // Super important: return $init!
    return $init;
}

add_filter('tiny_mce_before_init', 'tmve_mce_valid_elements');

?>
