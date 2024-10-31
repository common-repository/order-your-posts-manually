<?php
/**
 * @package Order your Posts Manually
 * @version 2.2.5
 */
/*
Plugin Name: Order your Posts Manually
Plugin URI: http://cagewebdev.com/order-posts-manually
Description: Order your Posts Manually by Dragging and Dropping them
Version: 2.2.5
Date: 10/26/2019
Author: Rolf van Gelder
Author URI: http://cagewebdev.com/
License: GPLv2 or later
*/
?>
<?php
/***********************************************************************************
 *
 * 	ORDER YOUR POSTS MANUALLY - MAIN CLASS
 *
 ***********************************************************************************/
 
// CREATE INSTANCE
global $opm_class;
$opm_class = new OrderYourPostsManually; 
 
class OrderYourPostsManually {
	var $opm_version      = '2.2.5';
	var $opm_release_date = '10/26/2019';
	var $opm_txt_domain   = 'order-your-posts-manually';
	var $opm_post_types   = array();
	var	$opm_edit_icon    = '';
	
	// OBJECTS
	var $opm_displayer_obj;
	var $opm_utilities_obj;	
	
	/*******************************************************************************
	 * 	CONSTRUCTOR
	 *******************************************************************************/
	function __construct() {

		// LOAD CLASSES AND CREATE INSTANCES
		$this->opm_classes();
		
		// USE THE NON-MINIFIED VERSION OF JS AND CSS WHILE DEBUGGING
		$this->script_minified = (defined('WP_DEBUG') && WP_DEBUG) ? '' : '.min';
		$this->script_minified = '';
		
		// GET OPTIONS FROM DB (JSON FORMAT)
		$this->opm_options = get_option('opm_options');

		// FIRST RUN: SET DEFAULT SETTINGS
		$this->opm_init_settings();

		// ADD ACTIONS FOR MENU ITEMS AND SCRIPTS
		add_action('init', array(&$this, 'opm_add_actions'));
	
		// BASE NAME OF THE PLUGIN
		$this->plugin_basename = plugin_basename(__FILE__);
		$this->plugin_basename = substr($this->plugin_basename, 0, strpos( $this->plugin_basename, '/'));
		
		// LOCALIZATION
		add_action('init', array(&$this, 'opm_i18n'));
		
		// ICON URL
		$this->opm_edit_icon = plugins_url().'/order-your-posts-manually/images/edit.png';				
	} // __construct()


	/*******************************************************************************
	 * 	LOAD CLASSES AND CREATE INSTANCES
	 *******************************************************************************/		
	function opm_classes() {
		// LOAD CLASSES
		include_once('classes/opm-displayer.php');
		include_once('classes/opm-utilities.php');
		
		// CREATE INSTANCES
		$this->opm_displayer_obj = new OPM_Displayer();
		$this->opm_utilities_obj = new OPM_Utilities();
	} // opm_classes()
	
	
	/*******************************************************************************
	 * 	INITIALIZE SETTINGS (FIRST TIME)
	 *******************************************************************************/
	function opm_init_settings() {
		$save = false;

		if (!isset($this->opm_options['opm_date_field'])) {
			// CREATION DATE
			$this->opm_options['opm_date_field']      = '0';
			$save = true;
		}
		if (!isset($this->opm_options['opm_posts_per_page'])) {
			// ALL POSTS
			$this->opm_options['opm_posts_per_page']  = '0';
			$save = true;
		}
		if (!isset($this->opm_options['opm_post_type'])) {
			// POST TYPE TO ORDER
			$this->opm_options['opm_post_type']       = 'post';
			$save = true;
		}
		if (!isset($this->opm_options['opm_cat_id'])) {
			// CATEGORY TO ORDER
			$this->opm_options['opm_cat_id']          = '0';
			$save = true;
		}		
		if (!isset($this->opm_options['opm_editors_allowed'])) {
			// ARE EDITORS ALLOWED TO USE THIS PLUGIN?
			$this->opm_options['opm_editors_allowed'] = 'N';
			$save = true;
		}
		if (!isset($this->opm_options['opm_show_drafts'])) {
			// SHOW DRAFTS?
			$this->opm_options['opm_show_drafts'] = 'N';
			$save = true;
		}
		if (!isset($this->opm_options['opm_show_excerpts'])) {
			// SHOW EXCERPTS?
			$this->opm_options['opm_show_excerpts'] = 'Y';
			$save = true;
		}
		if (!isset($this->opm_options['opm_show_edit_links'])) {
			// SHOW EDIT LINKS?
			$this->opm_options['opm_show_edit_links'] = 'Y';
			$save = true;
		}			
		if (!isset($this->opm_options['opm_show_thumbnails'])) {
			// SHOW THUMBNAILS
			$this->opm_options['opm_show_thumbnails'] = 'N';
			$save = true;
		}
		if (!isset($this->opm_options['opm_thumbnail_size'])) {
			// THUMBNAIL SIZE
			$this->opm_options['opm_thumbnail_size']  = '100';
			$save = true;
		}			

		// SOMETHING CHANGED: SAVE OPTIONS ARRAY
		if ($save) update_option('opm_options', $this->opm_options);
	} // opm_init_settings()


	/*******************************************************************************
	 * 	ADD ACTIONS FOR MENU ITEMS AND SCRIPTS
	 *******************************************************************************/	
	function opm_add_actions() {
		if (!$this->opm_is_frontend_page() && is_user_logged_in()) {
			// BACKEND PAGE
			// ADD BACKEND STYLE SHEET
			add_action('admin_init', array(&$this, 'opm_be_scripts'));
			add_action('admin_init', array(&$this, 'opm_be_styles'));		
			add_action('admin_menu', array(&$this, 'opm_admin_menu'));
			add_action('admin_menu', array(&$this, 'opm_admin_tools'));
			add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'opm_settings_link'));
		} // if (!$this->opm_is_frontend_page() && is_user_logged_in())
	} // opm_add_actions()


	/*******************************************************************************
	 * 	LOAD SETTINGS PAGE
	 *******************************************************************************/
	function opm_settings() {
		// LOAD THE SETTINGS PAGE
		include_once(trailingslashit(dirname( __FILE__ )).'/admin/settings.php');
	} // opm_settings()	
	
	
	/*******************************************************************************
	 * 	DEFINE TEXT DOMAIN (FOR LOCALIZATION)
	 *******************************************************************************/	
	function opm_i18n() {
		load_plugin_textdomain($this->opm_txt_domain, false, dirname(plugin_basename( __FILE__ )).'/languages/');
	} // opm_i18n()	
	
	
	/*******************************************************************************
	 * 	IS THIS A FRONTEND PAGE?
	 *******************************************************************************/
	function opm_is_frontend_page() {	
		if (isset($GLOBALS['pagenow']))
			return !is_admin() && !in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
		else
			return !is_admin();
	} // opm_is_frontend_page()
	

	/*******************************************************************************
	 * 	ARE WE ON A, FOR THIS PLUGIN, RELEVANT PAGE?
	 *******************************************************************************/	
	function opm_is_relevant_page() {
		$this_page = '';
		if(isset($_GET['page'])) $this_page = $_GET['page'];
		return ($this_page == 'opm_settings' || $this_page == 'opm-order-posts.php');
	} // opm_is_relevant_page()


	/*******************************************************************************
	 * 	LOAD BACKEND JAVASCRIPT (ONLY ON RELEVANT PAGES)
	 *******************************************************************************/
	function opm_be_scripts() {	
		if ($this->opm_is_relevant_page()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery-ui-core');
			wp_enqueue_script('jquery-ui-draggable');
			wp_enqueue_script('jquery-ui-sortable');
			wp_enqueue_script('jquery-ui-position');
		} // if ($this->opm_is_relevant_page())
	} // opm_be_scripts()
	

	/*******************************************************************************
	 * 	LOAD BACKEND STYLESHEET (ONLY ON RELEVANT PAGES)
	 *******************************************************************************/
	function opm_be_styles() {
		if ($this->opm_is_relevant_page()) {
			wp_register_style('opm-be-style', plugins_url('css/order_your_posts_manually'.$this->script_minified.'.css', __FILE__));
			wp_enqueue_style('opm-be-style');			
		} // if ($this->opm_is_relevant_page())
	} // opm_be_styles()


	/*******************************************************************************
	 * 	ADD PAGE TO THE SETTINGS MENU
	 *******************************************************************************/
	function opm_admin_menu() {
		$capability = 'manage_options';	
		if (function_exists('add_options_page'))
			add_options_page(__('Order Your Posts Manually', $this->opm_txt_domain), __('Order Your Posts Manually', $this->opm_txt_domain), $capability, 'opm_settings', array(&$this, 'opm_settings'));		

	} // opm_admin_menu()


	/*******************************************************************************
	 * 	ADD THE 'ORDER POSTS MANUALLY' ITEM TO THE ADMIN TOOLS MENU
	 *******************************************************************************/	
	function opm_admin_tools() {
		if (function_exists('add_management_page')) {
			$capability = 'manage_options';	
			if ($this->opm_options['opm_editors_allowed'] == 'Y') $capability = 'edit_others_posts';
			add_management_page(__('Order Your Posts Manually',$this->opm_txt_domain), __('Order Your Posts Manually',$this->opm_txt_domain), $capability, 'opm-order-posts.php', array( &$this, 'opm_list_posts'));
		} // if (function_exists('add_management_page'))
	} // opm_admin_tools()
	

	/*******************************************************************************
	 * 	SHOW A LINK TO THE PLUGIN SETTINGS ON THE MAIN PLUGINS PAGE
	 *******************************************************************************/		
	function opm_settings_link($links) { 
	  array_unshift($links, '<a href="options-general.php?page=opm_settings">Settings</a>'); 
	  return $links;
	} // opm_settings_link()
	

	/*******************************************************************************
	 * 	MAIN FUNCTION: LIST THE POSTS
	 *******************************************************************************/
	function opm_list_posts() {
		global $wpdb, $opm_version, $opm_release_date;
		
		// GET SORTING ORDER FROM OPTIONS
		$opm_date_field = $this->opm_options['opm_date_field'];
		$field_name = ($opm_date_field == 0) ? 'post_date' : 'post_modified';
	
		// GET NUMBER OF POSTS PER PAGE FROM OPTIONS
		$opm_posts_per_page = $this->opm_options['opm_posts_per_page'];
		
		// DEFAULT: ALL POSTS AT ONCE
		if(!$opm_posts_per_page) $opm_posts_per_page = 0;
		
		// TYPES TO ORDER, DEFAULT = POST
		$opm_post_type = $this->opm_options['opm_post_type'];
		if(!$opm_post_type) $opm_post_type = 'post';
	
		/*************************************************************************
		*
		*	UPDATE POST DATES
		*
		*************************************************************************/
		if(count($_POST)>0 && $_POST['action'] == 'update_dates') {
			$dates   = explode('#', $_POST['dates']);
			$postids = explode('&', $_POST['sortdata']);
			
			for($p=0; $p<count($postids); $p++) {
				$q = explode('=', $postids[$p]);
				$post_id = $q[1];
				$sql = "
				UPDATE $wpdb->posts SET `".$field_name."` = '$dates[$p]' WHERE `ID` = $post_id";
				$wpdb -> get_results($sql);
			} // for($p=0; $p<count($postids); $p++)
			echo "<div class='updated'><p><strong>".__('SORT ORDER SAVED!', $this->opm_txt_domain)."</strong></p></div>";
		} // if(count($_POST)>0 && $_POST['action'] == 'update_dates')
		
	
		/*************************************************************************
		*
		*	GET THE POSTS
		*
		*************************************************************************/
		if($this->opm_options['opm_show_drafts'] == 'Y') {
			$post_status = array('publish', 'draft');
		} else {
			$post_status = array('publish');
		} // if($this->opm_options['opm_show_drafts'] == 'Y')

		$post_type = $this->opm_options['opm_post_type'];
		if(isset($_REQUEST['post_type'])) $post_type = substr($_REQUEST['post_type'], 1);
		
		$cat_id    = $this->opm_options['opm_cat_id'];
		if(isset($_REQUEST['cat_id'])) $cat_id = $_REQUEST['cat_id'];

		if($cat_id > 0) {
			// SPECIFIC CATEGORY
			$args = array(
				'posts_per_page' => 999999,
				'post_status' => $post_status,
				'post_type' => $post_type,
				'category' => $cat_id,
				'orderby' => $field_name
			);
		} else {
			// ALL CATEGORIES
			$args = array(
				'posts_per_page' => 999999,
				'post_status' => $post_status,
				'post_type' => $post_type,
				'orderby' => $field_name
			);
		} // if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] > 0)
		
		// GET THE POSTS
		$myposts = get_posts($args);	

		$mode           = '';	
		$dates          = '';
		$nr_of_stickies = 0;
		$nr_of_posts    = 0;
		
		/*************************************************************************************
		 *
		 *	COUNT THE NUMBER OF STICKIES AND SAVE THE ORIGINAL DATES TO A STRING
		 *
		 ************************************************************************************/
		foreach($myposts as $post) {
			if(is_sticky($post->ID)) $nr_of_stickies++;
			$nr_of_posts++;
			if($dates) $dates .= "#";
			if($field_name == 'post_date') {
				$dates .= $post->post_date;
				$mode = __('creation date', $this->opm_txt_domain);
			} else {
				$dates .= $post->post_modified;
				$mode = __('modification date', $this->opm_txt_domain);
			} // if($field_name == 'post_date')
		} // foreach($myposts as $post)
	?>
<script>
	var pagnr = 1;
	var busy  = false;
	var done  = false;
	
	/*************************************************************************************
	 *
	 *	GET SETS OF POSTS (PER PAGE)
	 *
	 ************************************************************************************/
	function opm_get_posts() {
		if(done) return;

		// PARAMETERS FOR THE AJAX CALL
		var data = {
			'action': 'opm_action',
			'cat_id': <?php echo $cat_id;?>,
			'opm_posts_per_page': <?php echo $this->opm_options['opm_posts_per_page'];?>,
			'opm_post_type': '<?php echo $post_type;?>',
			'opm_show_drafts': '<?php echo $this->opm_options['opm_show_drafts']?>',
			'opm_show_excerpts': '<?php echo $this->opm_options['opm_show_excerpts']?>',
			'opm_show_edit_links': '<?php echo $this->opm_options['opm_show_edit_links']?>',
			'opm_show_thumbnails': '<?php echo $this->opm_options['opm_show_thumbnails']?>',
			'opm_thumbnail_size': '<?php echo $this->opm_options['opm_thumbnail_size']?>',
			'nr_of_stickies': <?php echo $nr_of_stickies;?>,
			'nr_of_posts': <?php echo $nr_of_posts;?>,	// INCL. STICKIES
			'pagnr': pagnr,
			'field_name': '<?php echo $field_name;?>'
		};
	
		// <ajaxurl> IS DEFINED SINCE WP v2.8!
		jQuery.post(ajaxurl, data, function(response) {
			jQuery("#opm-sortable").append(response);
			pagnr++;
			busy = false;
			jQuery("#opm-loading").hide();
			if(!done)
				jQuery("#opm-more-posts").show();
			else
				jQuery("#opm-no-more-posts").show();
		});
		
		var end = ((pagnr-1)*<?php echo $opm_posts_per_page;?>)+<?php echo $opm_posts_per_page;?>;
		if((end > <?php echo $nr_of_posts;?>) || (<?php echo $opm_posts_per_page;?> == 0)) done = true;
	} // opm_get_posts()
	
	
	/*************************************************************************************
	 *
	 *	INITIALIZE JQUERY
	 *
	 ************************************************************************************/
	jQuery(document).ready(function () {
		// TAKE CARE OF THE DRAGGING AND DROPPING
		// http://api.jqueryui.com/sortable/
		jQuery('#opm-sortable').sortable({
				// CONTAINER
				placeholder: 'opm-placeholder',
				// OBJECT DROPPED
				stop: function (event, ui) {
					var oData = jQuery(this).sortable('serialize');
					jQuery('#sortdata').val(oData);
					jQuery('.save-changes').prop('disabled', false).css('font-weight', 'bold');
					// HIDE THE EDIT LINK v2.2
					jQuery('.opm-listing-right').hide();
					// DISABLE THE EDIT LINKS v2.2
					$('.edit-link').click(function () {return false;}).attr('title', '').css('cursor', 'move');
					//$('.edit-link').attr('title', '').css('cursor', 'move');
				}
		});
		// GET FIRST SET OF POSTS
		if(!done) opm_get_posts();
	});
	
	
	/*************************************************************************************
	 *
	 *	CHECK IF WE ARE AT THE END OF THE PAGE
	 *
	 ************************************************************************************/
	jQuery(window).scroll(function() {
		// alert(busy+" "+done);
		if(!busy && !done && (jQuery(window).scrollTop() + jQuery(window).height() >= jQuery(document).height())) {
			busy = true;
			// HIDE THE 'MORE POSTS AVAILABLE' MSG
			jQuery("#opm-more-posts").hide();
			// SHOW LOADER
			jQuery("#opm-loading").show();
			// GET NEXT SET OF POSTS
			opm_get_posts();
		}
	});
	</script>
<?php
	/*************************************************************************************
	 *
	 *	DISPLAY THE PAGE
	 *
	 ************************************************************************************/
	?>
<script>
function opm_onchange() {
	var post_type = jQuery("#opm_post_type").val();
	var cat_id    = jQuery("#opm_cat_id").val();
	self.location = '<?php echo site_url().'/wp-admin/tools.php?page=opm-order-posts.php&'?>' + "post_type=_" + post_type + "&cat_id=" + cat_id;
}
</script>

<form action="" method="post">
  <input type="hidden" id="action" name="action" value="update_dates">
  <input type="hidden" id="sortdata" name="sortdata" value="">
  <input type="hidden" id="dates" name="dates" value="<?php echo $dates;?>">
  <div id="opm-post-table">
    <div class="opm-title-bar">
      <h2>
        <?php
			$sorttype = __('sort type', $this->opm_txt_domain);
		?>
        <?php _e('Order Your Posts Manually (' . $sorttype . ': ' . $mode . ')', $this->opm_txt_domain); ?>
      </h2>
    </div>
    <?php
$this->opm_displayer_obj->display_header();
?>
    <span class="opm-stickies-txt">
    <?php _e('STICKY POSTS', $this->opm_txt_domain)?>
    (<?php echo $nr_of_stickies?>) -
    <?php _e('REGULAR POSTS', $this->opm_txt_domain); ?>
    (<?php echo ($nr_of_posts-$nr_of_stickies);?>)</span><br>
    <br>
    <strong>
    <?php _e('Drag and drop the posts to change the display order.', $this->opm_txt_domain); ?>
    </strong> <br>
    <strong>
    <?php _e('NOTE: STICKY POSTS will always stay on top!', $this->opm_txt_domain); ?>
    </strong><br>
    <br>
    (<?php _e('After changing the order, don\'t forget to click the <strong>SAVE CHANGES</strong> button to actually update the posts', $this->opm_txt_domain); ?>)<br>
    <br>
    <?php
$this->opm_post_types = $this->opm_utilities_obj->opm_get_relevant_post_types();
?>
    <span id="opm_div_post_type">
    <?php _e('Post type', $this->opm_txt_domain)?>
    :
    <select name="opm_post_type" id="opm_post_type">
      <?php
if(isset($_REQUEST['post_type']) && isset($_REQUEST['cat_id'])) {
	$opm_post_type = substr($_REQUEST['post_type'], 1);
	$this->opm_options['opm_post_type'] = $opm_post_type;
	$opm_cat_id = $_REQUEST['cat_id'];
	$this->opm_options['opm_cat_id'] = $opm_cat_id;
	update_option('opm_options', $this->opm_options);
}
foreach($this->opm_post_types as $cpt) {
	$selected = '';
	if($opm_post_type == $cpt) $selected = 'selected="selected"';	
?>
      <option value="<?php echo $cpt?>" <?php echo $selected?>><?php echo $cpt?></option>
      <?php
} // foreach($this->opm_post_types as $cpt)
?>
    </select>
    </span> <span id="opm_div_cat_id"> &nbsp;&nbsp;
    <?php _e('Category', $this->opm_txt_domain)?>
    :
    <select name="opm_cat_id" id="opm_cat_id">
      <option value="0">
      <?php _e('* ALL *', $this->opm_txt_domain)?>
      </option>
      <?php
		$args = array(
		  'hide_empty' => 1,
		  'orderby' => $field_name,
		  'order' => 'ASC'
		);
		$cat_id = $this->opm_options['opm_cat_id'];
		if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] > 0) $cat_id = $_REQUEST['cat_id'];
		$categories = get_categories($args);
		foreach ( $categories as $category )
		{	$selected = '';		
			if($category->cat_ID == $cat_id) $selected = 'selected="selected"';
?>
      <option value="<?php echo $category->cat_ID?>" <?php echo $selected?>><?php echo __($category->name, $this->opm_txt_domain)?></option>
      <?php
		}
?>
    </select>
    </span> &nbsp;&nbsp;
    <input name="opm_find_posts" id="opm_find_posts" type="button" value="<?php _e('FIND POSTS', $this->opm_txt_domain); ?>" class="button-primary button-large opm-find-posts-btn" onclick="opm_onchange();">
    <br>
    <br>
    <hr>
    <br>
    <input name="save_changes_top" id="save_changes_top" type="submit" value="<?php _e('SAVE CHANGES', $this->opm_txt_domain); ?>" class="button-primary button-large save-changes" disabled="disabled">
    &nbsp;&nbsp;&nbsp;
    <input name="cancel" value="<?php _e('RELOAD POSTS', $this->opm_txt_domain); ?>" type="button" onclick="self.location='';" class="button">
    <br>
    <br>
    <?php    
		/*************************************************************************
		*
		*	PLACEHOLDER FOR THE ACTUAL POSTS
		*
		*************************************************************************/
		$loader_image = plugins_url().'/order-your-posts-manually/images/loader.gif';
	?>
    <ul id="opm-sortable">
    </ul>
    <br>
    <?php
		/*************************************************************************
		*
		*	LOADING ANIMATION
		*
		*************************************************************************/	
		?>
    <div id="opm-loading" align="center"><img src="<?php echo $loader_image;?>"><br>
      <br>
      <br>
    </div>
    <div id="opm-more-posts" align="center"><a href="javascript:;" onclick="opm_get_posts();">
      <?php _e('more posts available (scroll down)', $this->opm_txt_domain)?>
      </a><br>
      <br>
      <br>
    </div>
    <div id="opm-no-more-posts" align="center">(<?php _e('all posts loaded', $this->opm_txt_domain)?>)<br>
      <br>
      <br>
    </div>
    <?php    
		/*************************************************************************
		*
		*	BOTTOM BUTTONS
		*
		*************************************************************************/
	?>
    <input name="save_changes_bottom" id="save_changes_bottom" type="submit" value="<?php _e('SAVE CHANGES', $this->opm_txt_domain); ?>" class="button-primary button-large save-changes" disabled="disabled">
    &nbsp;&nbsp;&nbsp;
    <input name="cancel" value="<?php _e('RELOAD POSTS', $this->opm_txt_domain); ?>" type="button" onclick="self.location='';" class="button">
  </div>
</form>
<?php
	} // function opm_list_posts()
	
} // OrderYourPostsManually

?>
<?php

/********************************************************************************************
 *
 *	AJAX SERVER FOR RETRIEVING SETS OF POSTS
 *
 *********************************************************************************************/
function opm_action_callback() {
	global $opm_class, $wpdb;

	// GET THE PARAMETERS
	if(!isset($_POST['pagnr'])) wp_die();
	
	$opm_post_type = $_POST['opm_post_type'];
	if(substr($opm_post_type, 0, 1) == '_')
		$opm_post_type = substr($opm_post_type, 1);
	
	$pagnr               = intval($_POST['pagnr']);
	$cat_id              = $_POST['cat_id'];
	$opm_posts_per_page  = intval($_POST['opm_posts_per_page']);
	$opm_show_drafts     = $_POST['opm_show_drafts'];
	$opm_show_excerpts   = $_POST['opm_show_excerpts'];
	$opm_show_edit_links = $_POST['opm_show_edit_links'];
	$opm_show_thumbnails = $_POST['opm_show_thumbnails'];
	$opm_thumbnail_size  = $_POST['opm_thumbnail_size'];
	$nr_of_stickies      = intval($_POST['nr_of_stickies']);
	$nr_of_posts         = intval($_POST['nr_of_posts']);
	$field_name          = $_POST['field_name'];

//echo 'RvG 601 ' . $nr_of_posts . '<br>';	

	if($opm_posts_per_page > 0) {
		// LIMITED NUMBER OF POSTS PER PAGE
		$start = ($pagnr - 1) * $opm_posts_per_page;
		$end   = $start + $opm_posts_per_page;
		$end   = min($end, $nr_of_posts);
	} else {
		// ALL POSTS
		$start = 0;
		$end   = $nr_of_posts;
	}

	
	/**************************************************************************************
	 *
	 *	STICKY POSTS
	 *
	 **************************************************************************************/
	if($opm_show_drafts == 'Y') {
		$post_status = array('publish', 'draft');
	} else {
		$post_status = array('publish');
	}
	
	// echo $opm_post_type.' - '.$cat_id.'<br>';
	 
	if(isset($cat_id) && $cat_id > 0) {
		// SPECIFIC CATEGORY
		$mystickies = get_posts( array(
			'category' => $cat_id,
			'post_type' => $opm_post_type,
			'post_status' => $post_status,
			'post__in' => get_option( 'sticky_posts' ),
			'posts_per_page' => 999999,
			'orderby' => $field_name
		) );
	} else {
		// ALL CATEGORIES
		$mystickies = get_posts( array(
			'post_type' => $opm_post_type,
			'post_status' => $post_status,
			'post__in' => get_option( 'sticky_posts' ),
			'posts_per_page' => 999999,
			'orderby' => $field_name
		) );	
	} // if(isset($cat_id) && $cat_id > 0)

	if (count($mystickies) > 0) {
		// COLLECT THE STICKIES
		$posts = '';
		for($i = $start; $i < $nr_of_stickies; $i++) {
			if ($mystickies[$i]->post_status == 'draft') {
				$draft = __('DRAFT', $opm_class->opm_txt_domain).' * ';
				$class = ' opm-draft';
			} else {
				$draft = '';
				$class = '';
			} // if ($myposts[$i]->post_status == 'draft')			
			
			$thumb = wp_get_attachment_image_src(get_post_thumbnail_id($mystickies[$i]->ID), 'thumbnail');
			$url   = $thumb['0'];			
			if($field_name == 'post_date')
				$this_date = $mystickies[$i]->post_date;
			else
				$this_date = $mystickies[$i]->post_modified;

			// v2.2
			$link_title = 'ID: ' . $mystickies[$i]->ID;
			if ($opm_show_excerpts == "Y") {
				if ($myposts[$i]->post_excerpt) {
					$link_title .= "\n" . strip_tags($mystickies[$i]->post_excerpt);
				} // if ($myposts[$i]->post_excerpt)
			} // if ($opm_show_excerpts == "Y"

			// v2.2
			$edit_title = __('Edit this post', $opm_class->opm_text_domain);
				
			if($url && $opm_show_thumbnails == "Y") {
				// THUMBNAILS
				$posts .= '<li id="post-id-'.$mystickies[$i]->ID.'" class="ui-state-default opm-sticky" style="height:'.$opm_thumbnail_size.'px;" title="' . $link_title . '"><div class="opm-post-text"><small>'.$this_date.'</small><strong> * STICKY * '.$mystickies[$i]->post_title.'</strong></div><div class="opm-post-thumb"><a href="/wp-admin/post.php?post=' . $mystickies[$i]->ID  . '&action=edit" class="opm-listing-edit-normal" title="' . $edit_title . '"><img src="'.$url.'" width="'.$opm_thumbnail_size.'" height="'.$opm_thumbnail_size.'"></a></div></li>';
			} else {
				// NO THUMBNAILS
				if($opm_show_edit_links == "Y") {
					// SHOW EDIT LINK? v2.2
					$posts .= '<li id="post-id-'.$mystickies[$i]->ID.'" class="ui-state-default opm-sticky" title="'. $link_title . '"><div class="opm-listing-container"><div class="opm-listing-left"><small>'.$this_date.'</small><strong> * STICKY * '.$draft.$mystickies[$i]->post_title.'</strong></div><div class="opm-listing-right"><a href="/wp-admin/post.php?post=' . $mystickies[$i]->ID  . '&action=edit" class="opm-listing-edit-normal" title="' . $edit_title . '"><img src="' . $opm_class->opm_edit_icon . '"></a></div></div></li>';
				} else {
					$posts .= '<li id="post-id-'.$mystickies[$i]->ID.'" class="ui-state-default opm-sticky" title="'. $link_title . '"><div class="opm-listing-container"><div class="opm-listing-left"><small>'.$this_date.'</small><strong> * STICKY * '.$draft.$mystickies[$i]->post_title.'</strong></div><div class="opm-listing-right"></div></div></li>';				
				} // if($opm_show_edit_links == "Y")
			} // if($url && $opm_show_thumbnails == "Y")
		} // for($i = $start; $i < $nr_of_stickies; $i++)

		// RETURN THE SET OF POSTS TO THE CALLER
		echo $posts;
	}

	/**************************************************************************************
	 *
	 *	REGULAR POSTS
	 *
	 **************************************************************************************/
	if(isset($cat_id) && $cat_id > 0) {
		// SPECIFIC CATEGORY
		$myposts = get_posts( array(
			'category' => $cat_id,
			'post_type' => $opm_post_type,
			'post_status' => $post_status,
			'post__not_in' => get_option( 'sticky_posts' ),
			'posts_per_page' => 999999,
			'orderby' => $field_name
		) );
	} else {
		// ALL CATEGORIES
		$myposts = get_posts( array(
			'post_type' => $opm_post_type,
			'post_status' => $post_status,
			'post__not_in' => get_option( 'sticky_posts' ),
			'posts_per_page' => 999999,
			'orderby' => $field_name
		) );	
	} // if(isset($cat_id) && $cat_id > 0)

	if (count($myposts) < 1) {
		// NOTHING FOUND
		echo '<b>' . _e('Nothing found', $this->opm_txt_domain) . " (post type: '" . $opm_post_type . "')</b>";
	} else {
		// COLLECT THE POSTS
		$posts = '';
		for($i = $start; $i < $end - $nr_of_stickies; $i++) {
			if ($myposts[$i]->post_status == 'draft') {
				$draft = __('DRAFT', $opm_class->opm_txt_domain).' * ';
				$class = ' opm-draft';
			} else {
				$draft = '';
				$class = '';
			} // if ($myposts[$i]->post_status == 'draft')
			
			$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($myposts[$i]->ID), 'thumbnail' );
			$url = $thumb['0'];	
					
			if($field_name == 'post_date')
				$this_date = $myposts[$i]->post_date;
			else
				$this_date = $myposts[$i]->post_modified;

			// v2.1.2
			$link_title = "ID: ".$myposts[$i]->ID;
			if ($opm_show_excerpts == "Y") {
				if ($myposts[$i]->post_excerpt) {
					$link_title .= "\n" . strip_tags($myposts[$i]->post_excerpt);
				} // if ($myposts[$i]->post_excerpt)
			} // if ($opm_show_excerpts == "Y")
			
			// v2.2
			$edit_title = __('Edit this post', $opm_class->opm_text_domain);

			if($url && $opm_show_thumbnails == "Y") {
				// THUMBNAILS
				$posts .= '<li id="post-id-'.$myposts[$i]->ID.'" class="ui-state-default'.$class.'" style="height:'.$opm_thumbnail_size.'px;" title="' . $link_title . '"><div class="opm-post-text"><small>'.$this_date.'</small> * <strong>'.$draft.$myposts[$i]->post_title.'</strong></div><div class="opm-post-thumb"><a href="/wp-admin/post.php?post=' . $myposts[$i]->ID  . '&action=edit" class="opm-listing-edit-normal edit-link" title="' . $edit_title . '"><img src="'.$url.'" width="'.$opm_thumbnail_size.'" height="'.$opm_thumbnail_size.'"></a></div></li>';
			} else {
				// NO THUMBNAILS
				if($opm_show_edit_links == "Y") {
					// SHOW EDIT LINK? v2.2
					$posts .= '<li id="post-id-'.$myposts[$i]->ID.'" class="ui-state-default'.$class.'" title="' . $link_title . '"><div class="opm-listing-container"><div class="opm-listing-left"><small>'.$this_date.'</small> * <strong>'.$draft.$myposts[$i]->post_title.'</strong></div><div class="opm-listing-right"><a href="/wp-admin/post.php?post=' . $myposts[$i]->ID  . '&action=edit" class="opm-listing-edit-normal" title="' . $edit_title . '"><img src="' . $opm_class->opm_edit_icon . '"></a></div></div></li>';							
				} else {
					$posts .= '<li id="post-id-'.$myposts[$i]->ID.'" class="ui-state-default'.$class.'" title="' . $link_title . '"><div class="opm-listing-container"><div class="opm-listing-left"><small>'.$this_date.'</small> * <strong>'.$draft.$myposts[$i]->post_title.'</strong></div><div class="opm-listing-right"></div></div></li>';						
				} // if($opm_show_edit_links == "Y")
			} // if($url && $opm_show_thumbnails == "Y")
		} // for($i=$start; $i<$end-$nr_of_stickies; $i++)

		// RETURN THE SET OF POSTS TO THE CALLER
		echo $posts;
	}

	// NEEDED FOR AN AJAX SERVER
	wp_die();
} // opm_action_callback()
add_action('wp_ajax_opm_action', 'opm_action_callback');
?>
