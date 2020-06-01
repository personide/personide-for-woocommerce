<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://www.quanrio.com
 * @since      1.0.0
 *
 * @package    Personide
 * @subpackage Personide/admin/partials
 */

?>


<div style="max-width: 800px">
  <div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <form method="post" name="access_token" action="options.php">

      <?php
      
      $logger = wc_get_logger();
      $options = get_option($this->plugin_name);

      $keys = ['access_token', 'remove_wc_related_products'];
      foreach ($keys as $key) {
        $options[$key] = (isset($options[$key]) && !empty($options[$key])) ? $options[$key] : '';
      }

      // $logger->debug('Form input' . print_r($options, TRUE));

      settings_fields($this->plugin_name);
      do_settings_sections($this->plugin_name);

      ?>

      <table class="form-table">
        <tbody>

          <tr valign="top">
            <th class="row"><?php esc_attr_e('Unique Store ID', $this->plugin_name); ?></th>
            <td>
              <input class="widefat" type="text" placeholder="Your Unique Store ID from Personide App Settings Page"
              id="<?php echo $this->plugin_name ?>-access_token" name="<?php echo $this->plugin_name ?>[access_token]" value= <?php echo $options['access_token'] ?> />
            </td>
          </tr>

          <tr valign="top">
            <th class="row"><?php esc_attr_e('Remove "WC Related Products"', $this->plugin_name); ?></th>
            <td>
              <input type="checkbox"
              id="<?php echo $this->plugin_name ?>-remove_wc_related_products" name="<?php echo $this->plugin_name ?>[remove_wc_related_products]" <?php echo ($options['remove_wc_related_products']) ? 'checked' : '' ?> />
            </td>
          </tr>
          
        </tbody>
      </table>

      <?php submit_button('Save all changes', 'primary','submit', TRUE); ?>

    </div>
  </div> 