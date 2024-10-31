<?php
/***********************************************************************************
 *
 * 	ORDER YOUR POSTS MANUALLY - SETTINGS PAGE
 *
 ***********************************************************************************/
if (!function_exists('add_action')) exit;

if (isset($_POST['action']) && 'save_settings' === $_POST['action']) {
	// SAVE SETTINGS
	check_admin_referer('opm_settings_'.$this->opm_version);

	if(isset($_POST['opm_editors_allowed']))
		$editors = $_POST['opm_editors_allowed'];
	else
		$editors = 'N';

	if(isset($_POST['opm_show_drafts']))
		$drafts = $_POST['opm_show_drafts'];
	else
		$drafts = 'N';

	// v2.2
	if(isset($_POST['opm_show_excerpts']))
		$showexcerpts = $_POST['opm_show_excerpts'];
	else
		$showexcerpts = 'N';	
		
	// v2.2
	if(isset($_POST['opm_show_edit_links']))
		$editlinks = $_POST['opm_show_edit_links'];
	else
		$editlinks = 'N';		

	if(isset($_POST['opm_show_thumbnails']))
		$thumbs = $_POST['opm_show_thumbnails'];
	else
		$thumbs = 'N';

	$this->opm_options['opm_date_field']      = sanitize_text_field($_POST['opm_date_field']);
	$this->opm_options['opm_posts_per_page']  = sanitize_text_field($_POST['opm_posts_per_page']);
	$this->opm_options['opm_editors_allowed'] = sanitize_text_field($editors);
	$this->opm_options['opm_show_drafts']     = sanitize_text_field($drafts);
	$this->opm_options['opm_show_excerpts']   = sanitize_text_field($showexcerpts);
	$this->opm_options['opm_show_edit_links'] = sanitize_text_field($editlinks);
	$this->opm_options['opm_show_thumbnails'] = sanitize_text_field($thumbs);
	$this->opm_options['opm_thumbnail_size']  = sanitize_text_field($_POST['opm_thumbnail_size']);
	
	update_option('opm_options', $this->opm_options);

	echo "<div class='updated'><p><strong>".__('Order Your Posts Manually SETTINGS UPDATED!',$this->opm_txt_domain)."</strong></p></div>";		
} // if (isset($_POST['action']) && 'save_settings' === $_POST['action'])
?>
<div class="opm-title-bar">
  <h2>
    <?php _e('Order Your Posts Manually - change the display order of your posts by dragging and dropping', $this->opm_txt_domain); ?>
  </h2>
</div>
<?php
$this->opm_displayer_obj->display_header();
?>
<hr>
<div id="opm-options-form">
  <h2><?php echo __('Settings', $this->opm_txt_domain); ?></h2>
  <p><?php echo __('Per default WordPress orders the posts using the <strong>CREATION date</strong> (also known as \'<strong>post_date</strong>\'). Last created posts first.',$this->opm_txt_domain);?></p>
  <p><?php echo __('Some site designers (including myself) prefer the ordering of the posts using the <strong>MODIFICATION date</strong> (go to <a href="http://web20bp.com/kb/wordpress-sort-posts-on-modified-date/" target="_blank">this page</a> to see how to do that).<br>So, last modified posts will show up first.', $this->opm_txt_domain); ?></p>
  <p><?php echo __('If you belong to the second category (<strong>MODIFICATION date</strong>) select the second option in the first drop down box below!', $this->opm_txt_domain); ?></p><br>
  <form action="" method="post" name="opm_settings" id="opm_settings">
    <?php wp_nonce_field('opm_settings_'.$this->opm_version); ?>
    <input name="action" type="hidden" value="save_settings">
    <table border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td><strong><?php echo __('Sorting Field', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><select name="opm_date_field" id="opm_date_field">
            <option value="0"><?php echo __('use CREATION DATES of the posts', $this->opm_txt_domain); ?></option>
            <option value="1"><?php echo __('use MODIFICATION DATES of the posts', $this->opm_txt_domain); ?></option>
          </select>
          <script type="text/javascript">
jQuery("#opm_date_field").val(<?php echo $this->opm_options['opm_date_field']; ?>);
</script></td>
      </tr>
      <tr>
        <td><strong><?php echo __('Number of posts to show per page', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><select name="opm_posts_per_page" id="opm_posts_per_page">
            <option value="0"><?php echo __('show ALL posts at once', $this->opm_txt_domain); ?></option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
            <option value="100">100</option>
            <option value="250">250</option>
            <option value="500">500</option>
          </select>
          <script type="text/javascript">
	jQuery("#opm_posts_per_page").val('<?php echo $this->opm_options['opm_posts_per_page']; ?>');
	</script></td>
      </tr>
      <tr>
        <td><strong><?php echo __('Also allow editors to use this plugin', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><input name="opm_editors_allowed" id="opm_editors_allowed" type="checkbox" value="Y">
          <?php
  if($this->opm_options['opm_editors_allowed'] == 'Y')
  {
  ?>
          <script type="text/javascript">jQuery("#opm_editors_allowed").prop("checked", true);</script>
          <?php
  }
  ?></td>
      </tr>
      <tr>
        <td><strong><?php echo __('Show drafts', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><input name="opm_show_drafts" id="opm_show_drafts" type="checkbox" value="Y">
          <?php
  if($this->opm_options['opm_show_drafts'] == 'Y')
  {
  ?>
          <script type="text/javascript">jQuery("#opm_show_drafts").prop("checked", true);</script>
          <?php
  }
  ?></td>
      </tr>
      
      <tr>
        <td><strong><?php echo __('Show excerpts', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><input name="opm_show_excerpts" id="opm_show_excerpts" type="checkbox" value="Y">
          <?php
  if($this->opm_options['opm_show_excerpts'] == 'Y')
  {
  ?>
          <script type="text/javascript">jQuery("#opm_show_excerpts").prop("checked", true);</script>
          <?php
  }
  ?></td>
      </tr>       
      
      <tr>
        <td><strong><?php echo __('Show edit links', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><input name="opm_show_edit_links" id="opm_show_edit_links" type="checkbox" value="Y">
          <?php
  if($this->opm_options['opm_show_edit_links'] == 'Y')
  {
  ?>
          <script type="text/javascript">jQuery("#opm_show_edit_links").prop("checked", true);</script>
          <?php
  }
  ?></td>
      </tr>        
      <tr>
        <td><strong><?php echo __('Show thumbnails with posts', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><input name="opm_show_thumbnails" id="opm_show_thumbnails" type="checkbox" value="Y">
          <?php
  if($this->opm_options['opm_show_thumbnails'] == 'Y')
  {
  ?>
          <script type="text/javascript">jQuery("#opm_show_thumbnails").prop("checked", true);</script>
          <?php
  }
  ?></td>
      </tr>
      <tr>
        <td><strong><?php echo __('Thumbnail size (in px)', $this->opm_txt_domain); ?></strong></td>
        <td class="opm-right-column"><select name="opm_thumbnail_size" id="opm_thumbnail_size">
            <option value="50">50px</option>
            <option value="75">75px</option>
            <option value="100">100px</option>
            <option value="125">125px</option>
            <option value="150">150px</option>
          </select>
          <script type="text/javascript">
	jQuery("#opm_thumbnail_size").val('<?php echo $this->opm_options['opm_thumbnail_size']; ?>');
	</script></td>
      </tr>      
      <tr>
        <td colspan="2">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2"><input name="submit" type="submit" value="<?php echo __('Save Settings', $this->opm_txt_domain); ?>" class="button-primary button-large">
        &nbsp;
        <input class="button opm-normal" type="button" name="run_plugin" value="Order Your Posts!" onclick="self.location='tools.php?page=opm-order-posts.php'">
        </td>
      </tr>
    </table>
  </form>
</div>
