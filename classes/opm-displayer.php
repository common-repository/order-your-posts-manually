<?php
/************************************************************************************************
 *
 *	DISPLAYER CLASS: DISPLAY HEADERS, CURRENT SETTINGS
 *
 ************************************************************************************************/
?>
<?php
class OPM_Displayer {
	/********************************************************************************************
	 *	CONSTRUCTOR
	 ********************************************************************************************/	
    function __construct() {
	} // __construct()


	/********************************************************************************************
	 *	DISPLAY THE PAGE HEADER
	 ********************************************************************************************/	
	function display_header() {
		global $opm_class;
?>
<div class="opm-intro">
  <div class="opm-left">
  <?php _e('Plugin version', $opm_class->opm_txt_domain); ?>: v<?php echo $opm_class->opm_version?> [<?php echo $opm_class->opm_release_date?>]<br>
  <a href="http://cagewebdev.com/order-posts-manually/" target="_blank">
  <?php _e('Plugin page', $opm_class->opm_txt_domain); ?></a> - <a href="http://wordpress.org/plugins/order-your-posts-manually/" target="_blank">
  <?php _e('Download page', $opm_class->opm_txt_domain); ?></a> - <a href="http://rvg.cage.nl/" target="_blank">
  <?php _e('Author', $opm_class->opm_txt_domain); ?></a> - <a href="http://cagewebdev.com/" target="_blank">
  <?php _e('Company', $opm_class->opm_txt_domain); ?></a>
  </div>
  <!-- opm-left -->
  <div class="opm-right" title="Click here to make your donation!">
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank"><input name="cmd" type="hidden" value="_s-xclick" />
<input name="hosted_button_id" type="hidden" value="KBP4S8MNSGJYW" />
<input alt="PayPal - The safer, easier way to pay online!" name="submit" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" type="image" />
<img src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" alt="" width="1" height="1" border="0" /></form>
  </div>
  <!-- opm-right -->
</div>
<!-- opm-intro -->
<br clear="all">
<?php	
	} // display_header
	
} // OPM_Displayer
?>