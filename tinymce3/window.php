<?php
require_once('window_info.php');
$wpconfig = realpath("../../../../wp-config.php");

// stop when wp-config is not there
if (!file_exists($wpconfig)) {
	echo "Could not found wp-config.php. Error in path :\n\n".$wpconfig ;	
	die;	
}

require_once($wpconfig);
require_once(ABSPATH.'/wp-admin/admin.php');

// check for rights
if(!current_user_can('edit_posts')) die;

// get the values of from database table and create a associative arrays to identify the prefix,suffix and ontology
$onto_arr = array(); 
$prefix_arr = array();
$suffix_arr = array();
$o = get_option('ccrdfaTagSettings');
for ($i = 0; $i < count($o['tags']); $i++){
$b = $o['tags'][$i];
$b['ontology']  = htmlentities(stripslashes($b['ontology']), ENT_COMPAT, get_option('blog_charset'));
array_push($onto_arr,$b['ontology']);
$b['prefix'] = htmlentities($b['prefix'], ENT_COMPAT, get_option('blog_charset'));
array_push($prefix_arr,$b['prefix']);
$b['suffix'] = htmlentities($b['suffix'], ENT_COMPAT, get_option('blog_charset'));
array_push($suffix_arr,$b['suffix']);
}

$onto_prefix=array_combine($onto_arr,$prefix_arr);
$onto_suffix=array_combine($onto_arr,$suffix_arr);

$last_preview=NULL;
$ontology=$_POST['tz'];
$preview=stripslashes($_POST['tx']);

$selection=$_POST['selection'];


switch ($selection) {
    case 'properties':
		//Get the prefix property
		$prefix=get_prefix($ontology,$onto_prefix);
		
		//Get the suffix
		$suffix=get_suffix($ontology,$onto_suffix);
		
		//GEt the last preview if prefix property and preview are available.Othervise display as no value
		// if ($prefix != NULL && $preview != NULL) {
		if ($prefix != NULL) {
			$last_display_preview=get_last_display_preview($preview,$prefix,$suffix);
		}
		else {
			$last_display_preview='no value';
		}
		
        break;
		
    case 'about':
		
		if(validateURL($_POST['at_about'])) {
	  		$preview = substr($preview, 0, strpos($preview, ' ')). ' about="' .$_POST['at_about'] . '" ' .stristr($preview, ' '); 
		}
  
        $last_display_preview = $preview;
		
        break;
		
    case '':
        break;
}
		
?>

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>RDFa</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/tiny_mce_popup.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/mctabs.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo get_option('siteurl') ?>/wp-includes/js/tinymce/utils/form_utils.js"></script>
	<script language="javascript" type="text/javascript" src="<?php echo RDFa_URLPATH ?>tinymce3/tinymce.js"></script>
	<style type="text/css" media="all">
		.hidden { display: none; }
		.shown{ display: inline; }
	</style>
	<base target="_self" />
</head>
<body id="link" onLoad="getSelText('<?php if($last_display_preview == "") { ?><?php } else { ?>1<?php } ?>')">
  <form name="RDFa" action="<?php print $php_SELF;?>" method="POST">
   <div class="tabs">
     <ul>
	 	<li id="dublin_core_properties_tab" class="current"><span><a href="javascript:mcTabs.displayTab('dublin_core_properties_tab','dublin_core_panel');" onMouseDown="return false;"><?php _e("Dublin Core Properties", 'rdfa'); ?></a></span></li>
	 	<li id="foaf_properties_tab"><span><a href="javascript:mcTabs.displayTab('foaf_properties_tab','foaf_properties_panel');" onMouseDown="return false;"><?php _e("FOAF Properties", 'rdfa'); ?></a></span></li>
	 	<li id="advanced_tab"><span><a href="javascript:mcTabs.displayTab('advanced_tab','advanced_rdfa_panel');" onMouseDown="return false;"><?php _e("Advanced", 'rdfa'); ?></a></span></li>
	</ul>
   </div>
   <div class="panel_wrapper" style=" height: 175px;">
		<!-- rdfa panel -->
    <div id="dublin_core_panel" class="panel current">
	   <table border="0" cellpadding="4" cellspacing="0">
           <tr>
            <td nowrap="nowrap"><label for="dublin_core_properties"><?php _e("Duplin Core Properties", 'rdfa'); ?></label></td>
            <td></td>
            <td><select id="dublin_core" name="dublin_core_properties" onChange="getOntology(this, '<?php if($last_display_preview == "") { ?><?php } else { ?>1<?php } ?>')">
                 <option selected>
                 <?php 

                    if ($_POST['tz']=="") {
                      $_POST['tz']="Select";
                      echo $_POST['tz']; 
                    }
                    else {
                      echo $_POST['tz']; 
                    }

                 ?>
                 </option>
                 <?php 
                   for($i = 0; $i < sizeof($onto_arr); $i++) {
                     if($onto_arr[$i]!=$_POST['tz']) {
                       echo "<option>";
                       echo $onto_arr[$i]; 
                       echo "</option>";
                     }
                   }
                 ?>
                 </select>
            </td>
          </tr>
          <tr>
            <td nowrap="nowrap"><label for="Preview"><?php _e("@about", 'rdfa'); ?></label></td>
            <td></td>
            <td>
				<select>
					 <option selected>Select</option>
					<option value="about">About</option>
					<option value="resource">Resource</option>
				</select> 
				<input type="text" id="at_about" onBlur="addAboutUrl(this)" value="<?php echo $_POST['at_about']; ?>" size="35" name="at_about"/></td>
         </tr>
          <tr>
            <td nowrap="nowrap"><label for="Preview"><?php _e("Preview", 'rdfa'); ?></label></td>
            <td></td>
            <td id="preview_td"><textarea id="preview" name="preview" rows="4" cols="33"><?php echo $last_display_preview;?></textarea> <?php if( $_POST['closing_tag'] != '') { ?><a id="closetag" style="vertical-align:top; text-decoration: none;" href="#" onClick="closeOntology('<?php echo $suffix; ?>')"><?php print "Add Closing Tag"; ?></a><?php } ?></td>
         </tr>
		</table>
        <p class="content"></p>
      </div>

    <div id="foaf_properties_panel" class="panel">
	   <table border="0" cellpadding="4" cellspacing="0">
           <tr>
            <td nowrap="nowrap"><label for="foaf_properties"><?php _e("FOAF Properties", 'rdfa'); ?></label></td>
          </tr>
		</table>
        <p class="content"></p>
      </div>

    <div id="advanced_rdfa_panel" class="panel">
	   <table border="0" cellpadding="4" cellspacing="0">
           <tr>
            <td nowrap="nowrap"><label for="advanced_rdfa_panel"><?php _e("Advanced", 'rdfa'); ?></label></td>
         </tr>
		</table>
        <p class="content"></p>
      </div>

   </div>

   <div class="mceActionPanel">
		<div style="float: left">
			<input type="button" id="cancel" name="cancel" value="<?php _e("Cancel", 'rdfa'); ?>" onClick="tinyMCEPopup.close();" />
		</div>

		<div style="float: right">
	      <input type="submit" id="insert" name="insert" value="<?php _e('Insert', 'rdfa'); ?>" onClick="insertDetail()" />
		</div>
   </div>

    <input type='hidden' name='tz' value='onto' /> 
    <input type='hidden' name='tx' value='text' />
	<input type='hidden' name='closing_tag' value='<?php if( $_POST['closing_tag'] != '') { ?>1<?php } ?>' />
	<input type='hidden' name='selection' value='' /> 
   </form>
</body>
</html>
