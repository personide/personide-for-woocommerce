<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Loquat
 * @subpackage Loquat/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
	</div>
</div> 







    <form method="post" name="access_token" action="options.php">

    <?php
        //Grab all options
        $options = get_option($this->plugin_name);

        $access_token = (isset($options['access_token']) && !empty($options['access_token'])) ? $options['access_token'] : '';
    ?>

    <?php
        settings_fields($this->plugin_name);
        do_settings_sections($this->plugin_name);
    ?>
    
    <!-- remove some meta and generators from the <head> -->
    <div>
           <fieldset>
            <legend class="screen-reader-text">
                <span>Enter Your Access token</span>
            </legend>
            <label for="<?php echo $this->plugin_name; ?>-access_token">
                <input type="text" id="<?php echo $this->plugin_name; ?>-access_token" name="<?php echo $this->plugin_name; ?>[access_token]" value= <?php echo $access_token ?> />
                <span><?php esc_attr_e('Enter Your Access token', $this->plugin_name); ?></span>
            </label>
        </fieldset>
    </div>


    <?php submit_button('Save all changes', 'primary','submit', TRUE); ?>
