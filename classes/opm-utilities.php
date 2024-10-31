<?php
/************************************************************************************************
 *
 *	UTILITIES
 *
 ************************************************************************************************/
?>
<?php
class OPM_Utilities {
	/********************************************************************************************
	 *	CONSTRUCTOR
	 ********************************************************************************************/	
    function __construct() {
	} // __construct()


	/********************************************************************************************
	 *	GET THE RELEVANT POST TYPES, INCLUDING CUSTOM POST TYPES (from v2.1)
	 ********************************************************************************************/
	function opm_get_relevant_post_types() {
		$relevant_pts = array();
		$posttypes    = get_post_types();

		foreach ($posttypes as $posttype) {
			// SKIP THE DEFAULT POST TYPES (EXCEPT FOR 'post')
			if (	$posttype != 'page' &&
					$posttype != 'attachment' &&
					$posttype != 'revision' &&
					$posttype != 'nav_menu_item' &&
					$posttype != 'custom_css' &&
					$posttype != 'customize_changeset') {
				array_push($relevant_pts, $posttype);
			}
		} // foreach ($posttypes as $posttype)
		
		return $relevant_pts;
	} // opm_get_relevant_post_types()	
} // OPM_UTILITIES