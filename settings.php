<?php
	// Deny direct access
    if (defined('ALLOW_INCLUDE') === false)
        die('no direct access');
    
    // Get AlphaSentry credits remaining
    $creditsstring = $this->get_credits();
    $credits = explode(',', $creditsstring);
    $freecredits = 'N/A';
    $paidcredits = 'N/A';
    if(count($credits) > 0)
    {
    	$freecredits = $credits[0];
    	$paidcredits = $credits[1];
    }
    
?>

<div class="wrap">
	<!-- Page heading -->
	<div id="icon-options-general" class="icon32"><br /></div><h2><?php _e('AlphaSentry Settings', 'alphasentry'); ?></h2>
	<p style="text-align:right;"><?php echo '<strong>'.__('Free Credits: ', 'alphasentry').'</strong>'.number_format($freecredits).' <strong>'.__('Paid Credits: ').'</strong>'.number_format($paidcredits); ?> [<a href="https://www.alphasentry.com/buy-credits.php" target="_blank"><? _e('Buy Credits', 'alphasentry'); ?></a>] </p>
	<p><?php _e('AlphaSentry is an API service to fight spam, fraud, and abuse. This WordPress plugin simplifies the integration of the AlphaSentry APIs to a WordPress-powered community', 'alphasentry'); ?></p>
   	
   	<!-- Settings form -->
   	<form method="post" action="options.php">
      <?php settings_fields('alphasentry_options_group'); ?>
      <table class="form-table">
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[api_key]"><?php _e('API Key', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[api_key]" id="alphasentry_options[api_key]" size="40" class="regular-text" value="<?php echo $this->options['api_key']; ?>" />
               <p class="description"><?php _e('Your API key is required to use this plugin. ', 'alphasentry'); ?> <?php _e('You can get your key', 'alphasentry'); ?> <a href="https://www.alphasentry.com/login.php?action=register" title="<?php _e('Get your AlphaSentry API Keys', 'alphasentry'); ?>"><?php _e('here', 'alphasentry'); ?></a>.</p>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[track_in_registration]"><?php _e('User Registration Tracking', 'alphasentry'); ?></label></th>
            <td>
            	<select id="alphasentry_options[track_in_registration]" name="alphasentry_options[track_in_registration]">
            		<option value="0" <?php echo ($this->options['track_in_registration'] == 1 ? '' : 'selected="selected"'); ?> ><?php _e('Off', 'alphasentry'); ?></option>
            		<option value="1" <?php echo ($this->options['track_in_registration'] == 1 ? 'selected="selected"' : ''); ?>><?php _e('On', 'alphasentry'); ?></option>
            	</select>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[track_in_login]"><?php _e('User Login Tracking', 'alphasentry'); ?></label></th>
            <td>
            	<select id="alphasentry_options[track_in_login]" name="alphasentry_options[track_in_login]">
            		<option value="0" <?php echo ($this->options['track_in_login'] == 1 ? '' : 'selected="selected"'); ?> ><?php _e('Off', 'alphasentry'); ?></option>
            		<option value="1" <?php echo ($this->options['track_in_login'] == 1 ? 'selected="selected"' : ''); ?>><?php _e('On', 'alphasentry'); ?></option>
            	</select>
            </td>
         </tr>
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[custom_class_1]"><?php _e('Custom Class 1', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[custom_class_1]" id="alphasentry_options[custom_class_1]" size="40" class="regular-text" value="<?php echo $this->options['custom_class_1']; ?>" />
               <p class="description"><?php _e('If left blank, value will default to a_sentry1', 'alphasentry'); ?></p>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[custom_class_2]"><?php _e('Custom Class 2', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[custom_class_2]" id="alphasentry_options[custom_class_2]" size="40" class="regular-text" value="<?php echo $this->options['custom_class_2']; ?>" />
               <p class="description"><?php _e('If left blank, value will default to a_sentry2', 'alphasentry'); ?></p>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[custom_field_1]"><?php _e('Custom Field 1', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[custom_field_1]" id="alphasentry_options[custom_field_1]" size="40" class="regular-text" value="<?php echo $this->options['custom_field_1']; ?>" />
               <p class="description"><?php _e('If left blank, value will default to ASVar1', 'alphasentry'); ?></p>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[custom_field_2]"><?php _e('Custom Field 2', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[custom_field_2]" id="alphasentry_options[custom_field_2]" size="40" class="regular-text" value="<?php echo $this->options['custom_field_2']; ?>" />
               <p class="description"><?php _e('If left blank, value will default to ASVar2', 'alphasentry'); ?></p>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[custom_div]"><?php _e('Custom Div', 'alphasentry'); ?></label></th>
            <td>
               <input type="text" name="alphasentry_options[custom_div]" id="custom_div" size="40" class="regular-text" value="<?php echo $this->options['custom_div']; ?>" />
               <p class="description"><?php _e('If left blank, value will default to asentry_3', 'alphasentry'); ?></p>
            </td>
         </tr>
         
         <tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_logins]"><?php _e('Throttle Failed Logins', 'alphasentry'); ?></label></th>
            <td>
              <select id="alphasentry_options[throttle_logins]" name="alphasentry_options[throttle_logins]">
            		<option value="0" <?php echo ($this->options['throttle_logins'] == 1 ? '' : 'selected="selected"'); ?> ><?php _e('Off', 'alphasentry'); ?></option>
            		<option value="1" <?php echo ($this->options['throttle_logins'] == 1 ? 'selected="selected"' : ''); ?>><?php _e('On', 'alphasentry'); ?></option>
            	</select>
            </td>
         </tr>
      	<tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_logins_limit]"><?php _e('Max Failed Logins', 'alphasentry'); ?></label></th>
            <td>
               <?php echo $this->limits_dropdown('throttle_logins_limit'); ?>
            </td>
         </tr>
      	<tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_logins_time]"><?php _e('Login Throttle Timeframe', 'alphasentry'); ?></label></th>
            <td>
            	<?php echo $this->timeframe_dropdown('throttle_logins_time'); ?>
            </td>
         </tr>
      	<tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_account_ip]"><?php _e('Throttle Accounts by IP', 'alphasentry'); ?></label></th>
            <td>
              <select id="track_in_login" name="alphasentry_options[throttle_account_ip]">
            		<option value="0" <?php echo ($this->options['throttle_account_ip'] == 1 ? '' : 'selected="selected"'); ?> ><?php _e('Off', 'alphasentry'); ?></option>
            		<option value="1" <?php echo ($this->options['throttle_account_ip'] == 1 ? 'selected="selected"' : ''); ?>><?php _e('On', 'alphasentry'); ?></option>
            	</select>
            </td>
         </tr>
      	<tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_account_ip_limit"><?php _e('Max Accounts per IP', 'alphasentry'); ?></label></th>
            <td>
	            <?php echo $this->limits_dropdown('throttle_account_ip_limit'); ?>
            </td>
         </tr>
      	<tr valign="top">
            <th scope="row"><label for="alphasentry_options[throttle_account_ip_time]"><?php _e('Account Throttle Timeframe', 'alphasentry'); ?></label></th>
            <td>
            	<?php echo $this->timeframe_dropdown('throttle_account_ip_time'); ?>
            </td>
         </tr>
      </table>
      <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" title="<?php _e('Save AlphaSentry Changes') ?>" value="<?php _e('Save AlphaSentry Changes') ?>" /></p>
   </form>
   
   <?php do_settings_sections('alphasentry_options_page'); ?>
</div>