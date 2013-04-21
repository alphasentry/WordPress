<?php

require_once('wp-plugin.php');

if (!class_exists('wpAlphaSentry')) 
{
    /**
     * @author AlphaSentry
     * 
     * The wpAlphaSentry class integrates the AlphaSentry API with the WordPress platform.
     *
     */
    class wpAlphaSentry extends WPPlugin 
    {
        /**
         * PHP5 Constructor
         * 
         * @param string $options_name
         */
        function __construct($options_name) 
        {
            parent::__construct($options_name);
            
            $this->register_default_options();
            
            // require the alphasentry library
            $this->require_library();
            
            // register the hooks
            $this->register_actions();
            $this->register_filters();
        }
        
        /* (non-PHPdoc)
         * Register plugin actions with Wordpress Hooks
         * 
         * @see WPPlugin::register_actions()
         */
        function register_actions() 
        {
            // options
            register_activation_hook(WPPlugin::path_to_plugin_directory() . '/wp-alphasentry.php', array(&$this, 'register_default_options')); // this way it only happens once, when the plugin is activated
            add_action('admin_init', array(&$this, 'register_settings_group'));
			
            // only register the hooks if the user wants alphasentry on the login page
            if ($this->options['track_in_login'])
            {
            	// add alphasentry tracking code
            	add_action('login_form', array(&$this, 'add_track_in_login'));
            }
            
            // only register the hooks if the user wants alphasentry on the registration page
            if ($this->options['track_in_registration']) 
            {
                // add alphasentry tracking code
                add_action('register_form', array(&$this, 'add_track_in_login'));
            }
            
            // register the login hook if the user wants
            if ($this->options['track_in_login'])
            {
            	// send tracking data to API server on login
            	add_action('wp_login', array(&$this, 'track_alphasentry_login'), 10, 2);
            }
            
            // register the registration hook if the user wants
            if ($this->options['track_in_registration'])
            {
            	// send tracking data to API server on account creation
            	add_action('user_register', array(&$this, 'track_alphasentry_account'));
            }
            
            // register the hook if the user wants to throttle logins
            if ($this->options['throttle_logins'])
            {
            	// increment failed login count on failed login
            	add_action('wp_login_failed', array(&$this, 'throttle_login_increment'));
            }
            
            if ($this->options['throttle_account_ip'])
            {
            	// increment the account count after accounts are created
            	add_action('user_register', array(&$this, 'throttle_account_ip_increment'));
            }
            
            // if user is the admin...
            if(is_admin() === true)
            {
            	// register AJAX hook
            	add_action('wp_ajax_alphasentry_ajax', 'alphasentry_process_ajax');
            }
            
            // administration (menus, pages, notifications, etc.)
            add_filter("plugin_action_links", array(&$this, 'show_settings_link'), 10, 2);
			
            // settings page
            add_action('admin_menu', array(&$this, 'add_settings_page'));
            
            // admin notices
            add_action('admin_notices', array(&$this, 'missing_keys_notice'));
        }
        
        /* (non-PHPdoc)
         * Register plugin filters with WordPress hooks
         * 
         * @see WPPlugin::register_filters()
         */
        function register_filters() 
        {
        	// if the user wants to throttle logins
            if ($this->options['throttle_logins'])
            {
            	// Make sure user hasn't failed too many login attempts
            	add_filter('authenticate', array(&$this, 'throttle_login'), 30, 3);
            }
            
            // If the user wants to throttle accounts
            if ($this->options['throttle_account_ip'])
            {
            	// check to make sure user hasn't created too many accounts
            	add_filter('registration_errors', array(&$this, 'throttle_account_ip'));
            }
        }
        
        /**
         * Load text domain
         * 
         */
        function load_textdomain() 
        {
            load_plugin_textdomain('alphasentry', false, 'languages');
        }
        
        /* (non-PHPdoc)
         * Register and set default options
         * @see WPPlugin::register_default_options()
         */
        function register_default_options() 
        {
            if ($this->options)
               return;
           
            $option_defaults = array();
           
            $old_options = WPPlugin::retrieve_options('alphasentry');
           
            if ($old_options) 
            {
               $option_defaults['api_key'] = $old_options['as_api_key']; // the API key for AlphaSentry

               // placement
               $option_defaults['track_in_registration'] = $old_options['as_registration']; // whether or not to track user registration with AlphaSentry
               $option_defaults['track_in_login'] = $old_options['as_login']; // whether or not to track logins with AlphaSentry
               
               $option_defaults['custom_class_1'] = $old_options['as_class1']; // custom class name for AlphaSentry field 1
               $option_defaults['custom_class_2'] = $old_options['as_class2']; // custom class name for AlphaSentry field 2
               $option_defaults['custom_field_1'] = $old_options['as_field1']; // custom field name for AlphaSentry field 1
               $option_defaults['custom_field_2'] = $old_options['as_field2']; // custom field name for AlphaSentry field 2
               $option_defaults['custom_div'] = $old_options['as_div']; // custom ID name for AlphaSentry div
               
               $option_defaults['throttle_logins'] = $old_options['as_throttle_logins']; // whether or not to throttle failed logins
               $option_defaults['throttle_logins_time'] = $old_options['as_throttle_logins_time']; // the timeframe to measure failed login attempts
               $option_defaults['throttle_logins_limit'] = $old_options['as_throttle_logins_limit']; // the limit on failed logins
               $option_defaults['throttle_account_ip'] = $old_options['as_throttle_account_ip']; // whether or not to throttle accounts based on IP
               $option_defaults['throttle_account_ip_time'] = $old_options['as_throttle_account_ip_time']; // the timeframe to measure account IP limits
               $option_defaults['throttle_account_ip_limit'] = $old_options['as_throttle_account_ip_limit']; // the limit on accounts per IP
            }
            else
            {
               $option_defaults['api_key'] = ''; // the API key for AlphaSentry

               // placement
               $option_defaults['track_in_registration'] = 1; // whether or not to track user registration with AlphaSentry
               $option_defaults['track_in_login'] = 1; // whether or not to track logins with AlphaSentry
               
               $option_defaults['custom_class_1'] = ''; // custom class name for AlphaSentry field 1
               $option_defaults['custom_class_2'] = ''; // custom class name for AlphaSentry field 2
               $option_defaults['custom_field_1'] = ''; // custom field name for AlphaSentry field 1
               $option_defaults['custom_field_2'] = ''; // custom field name for AlphaSentry field 2
               $option_defaults['custom_div'] = ''; // custom ID name for AlphaSentry div
               
               $option_defaults['throttle_logins'] = 1; // whether or not to throttle failed logins
               $option_defaults['throttle_logins_time'] = 'Hour'; // the timeframe to measure failed login attempts
               $option_defaults['throttle_logins_limit'] = 4; // the limit on failed logins
               $option_defaults['throttle_account_ip'] = 1; // whether or not to throttle accounts based on IP
               $option_defaults['throttle_account_ip_time'] = 'Day'; // the timeframe to measure account IP limits
               $option_defaults['throttle_account_ip_limit'] = 2; // the limit on accounts per IP
            }
            
            // add the option based on what environment we're in
            WPPlugin::add_options($this->options_name, $option_defaults);
        }
        
        /**
         * Require the AlphaSentry helper class
         */
        function require_library() 
        {
            require_once($this->path_to_plugin_directory().'/AlphaSentry.php');
        }
        
        /**
         * Register the Plugin Settings
         */
        function register_settings_group() 
        {
            register_setting('alphasentry_options_group', 'alphasentry_options', array(&$this, 'validate_options'));
        }
        
        
        /**
         * Returns whether the plugin has been enabled by the user
         * 
         * @return boolean True or False whether AlphaSentry plugin has been enabled
         */
        function alphasentry_enabled() 
        {
            return ($this->options['track_in_registration'] || $this->options['track_in_login']
            		 || $this->options['throttle_logins'] || $this->options['throttle_account_ip']);
        }
        
        
        /**
         * Returns whether the plugin is missing the API key
         * 
         * @return boolean True or False whether AlphaSentry API key is missing
         */
        function keys_missing() 
        {
            return (empty($this->options['api_key']));
        }
        
        
        /**
         * Echoes error notice if there is a problem with the Plugin Settings
         * 
         * @param string $message The error message
         * @param string $anchor The anchor link to be included in the message
         */
        function create_error_notice($message, $anchor = '') 
        {
        	$options_url = admin_url('admin.php?page=wp-alphasentry/wordpress-alphasentry.php').$anchor;
            $error_message = sprintf(__($message.' <a href="%s" title="WP-AlphaSentry Options">Fix this</a>', 'alphasentry'), $options_url);
            
            echo '<div class="error"><p><strong>Warning:</strong> '.$error_message.'</p></div>';
        }
        
        
        /**
         * Gives user a notice if the API key is missing
         * 
         */
        function missing_keys_notice() 
        {
            if ($this->alphasentry_enabled() && $this->keys_missing()) 
            {
                $this->create_error_notice('You enabled AlphaSentry, but your AlphaSentry API key is missing.');
            }
        }
        
        
        /**
         * Verifies that a settings selection is a valid option in the dropdown menu
         * @param array $array Array of options
         * @param string $key The unique key for the option
         * @param string $value The submitted value
         * @return string The validated option value
         */
        function validate_dropdown($array, $key, $value) 
        {
            // make sure that the capability that was supplied is a valid capability from the drop-down list
            if (in_array($value, $array))
                return $value;
            else // if not, load the old value
                return $this->options[$key];
        }
        
        /**
         * Takes an array of submitted options values and returns a validated array
         * @param array $input Submitted options values
         * @return array Validated values
         */
        function validate_options($input) 
        {
            // trim the spaces out of the key, as they are usually present when copied and pasted
            $validated['api_key'] = trim($input['api_key']);
            
            $validated['track_in_registration'] = ($input['track_in_registration'] == 1 ? 1 : 0);
            $validated['track_in_login'] = ($input['track_in_login'] == 1 ? 1 : 0);
            $validated['throttle_logins'] = ($input['throttle_logins'] == 1 ? 1 : 0);
            $validated['throttle_account_ip'] = ($input['throttle_account_ip'] == 1 ? 1 : 0);
            $timeframes = array ('Hour', 'Day', 'Week', 'Month', 'Year');
            $limits = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 50);
            
            $validated['throttle_logins_time'] = $this->validate_dropdown($timeframes, 'throttle_logins_time', $input['throttle_logins_time']);
            $validated['throttle_account_ip_time'] = $this->validate_dropdown($timeframes, 'throttle_account_ip_time', $input['throttle_account_ip_time']);
            
            $validated['throttle_logins_limit'] = $this->validate_dropdown($limits, 'throttle_logins_limit', $input['throttle_logins_limit']);
            $validated['throttle_account_ip_limit'] = $this->validate_dropdown($limits, 'throttle_account_ip_limit', $input['throttle_account_ip_limit']);
            
            return $validated;
        }
        
        /**
         * Echoes AlphaSentry script to gather tracking data
         */
        function add_track_in_login()
        {
        	$class_1 = 'asentry_1';
        	$class_2 = 'asentry_2';
        	$field_1 = 'ASVar1';
        	$field_2 = 'ASVar2';
        	$div = 'asentry_3';
        	
        	// Using custom values if specified
        	if(!empty($this->options['custom_class_1']))
        		$class_1 = $this->options['custom_class_1'];
        	if(!empty($this->options['custom_class_2']))
        		$class_2 = $this->options['custom_class_2'];
        	if(!empty($this->options['custom_field_1']))
        		$field_1 = $this->options['custom_field_1'];
        	if(!empty($this->options['custom_field_2']))
        		$field_2 = $this->options['custom_field_2'];
        	if(!empty($this->options['custom_div']))
        		$div = $this->options['custom_div'];
        	
        	echo '
        	<input type="hidden" class="'.$class_1.'" name="'.$field_1.'" value="" />
        	<input type="hidden" class="'.$class_2.'" name="'.$field_2.'" value="" />
        	<div id="'.$div.'" style="position:absolute; top:-1px; left:0px; height:1px; width:1px; z-index:-1;"></div>
        	<script type="text/javascript">
        	var AlphaSentryOptions = {
        		class1 : \''.$class_1.'\',
        		class2 : \''.$class_2.'\',
        		divName : \''.$div.'\'
        	};
        	</script>
        	<script type="text/javascript" src="https://as1.alphasentry.com/as1/as1.js"></script>
        	';
        	return;
        }
        
        
        /**
         * Submits account tracking data to AlphaSentry API
         * @param string $user_id User ID
         */
        function track_alphasentry_account($user_id)
        {
        	$field_1 = 'ASVar1';
        	$field_2 = 'ASVar2';
        	
        	$user_login = '';
        	
        	if(!empty($_POST['user_login']))
        		$user_login = sanitize_user($_POST['user_login']);
        	
        	if(!empty($this->options['custom_field_1']))
        		$field_1 = $this->options['custom_field_1'];
        	if(!empty($this->options['custom_field_2']))
        		$field_2 = $this->options['custom_field_2'];
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
	        		$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		// Submit TrackAccount request to the AlphaSentry API
        		if($this->alphaSentryClient->TrackAccount($_POST[$field_1], $_POST[$field_2], $user_id, $user_login))
        		{
        			$this->set_credits($this->alphaSentryClient->Response['FreeCredits'], $this->alphaSentryClient->Response['PaidCredits']);
        			if(function_exists('update_user_meta'))
        			{
	        			add_user_meta($user_id, 'alphasentry_transaction',  $this->alphaSentryClient->Response['TransactionId']);
	        			add_user_meta($user_id, 'alphasentry_login_transaction',  $this->alphaSentryClient->Response['TransactionId']);
	        			add_user_meta($user_id, 'alphasentry_device',  $this->alphaSentryClient->Response['DeviceId']);
	        			add_user_meta($user_id, 'alphasentry_risk', $this->alphaSentryClient->Response['RiskScore']);
        			}
        		}
        	}
        }
        
        
        /**
         * Submits account login data to AlphaSentry API
         * @param string $user_login WordPres Username
         * @param WP_User $user WordPress User Object
         */
        function track_alphasentry_login($user_login, $user = '')
        {
        	$field_1 = 'ASVar1';
        	$field_2 = 'ASVar2';
        	 
        	if(!empty($this->options['custom_field_1']))
        		$field_1 = $this->options['custom_field_1'];
        	if(!empty($this->options['custom_field_2']))
        		$field_2 = $this->options['custom_field_2'];
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		if($user == '')
					$user = get_userdatabylogin($user_login);
        		
        		// Submit TrackAccount request to the AlphaSentry API
        		if($this->alphaSentryClient->TrackLogin($_POST[$field_1], $_POST[$field_2], $user->ID, $user_login))
        		{
        			$this->set_credits($this->alphaSentryClient->Response['FreeCredits'], $this->alphaSentryClient->Response['PaidCredits']);
        			if(function_exists('update_user_meta'))
        				update_user_meta($user->ID, 'alphasentry_login_transaction',  $this->alphaSentryClient->Response['TransactionId']);
        		}
        	}
        }
        
        
        /**
         * Increments GreyList value for failed logins
         * @param string $username WordPress Username
         */
        function throttle_login_increment($username)
        {
        	
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		// Increment value
        		if($this->alphaSentryClient->GreyListIncrementValue(strtolower($username), 'wpFailedLoginAttempts', $this->options['throttle_logins_time']))
        		{
        			$this->set_credits($this->alphaSentryClient->GreyListResponse['FreeCredits'], $this->alphaSentryClient->GreyListResponse['PaidCredits']);
        		}
        	}
        	return;
        }
        
        /**
         * Denies login if there are too many failed attempts
         * 
         * @param WP_User|WP_Error $user WP_User object or WP_Error object from login attempt
         * @param string $username Wordpress username passed from the hook
         * @return WP_Error If the user has too many failed logins
         */
        function throttle_login($user, $username)
        {
        	$failedlogins = 0;
        	if(!empty($this->options['api_key']) && !empty($username))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		if($this->alphaSentryClient->GreyListGetValue(strtolower($username), 'wpFailedLoginAttempts', $this->options['throttle_logins_time']))
        		{
        			$this->set_credits($this->alphaSentryClient->GreyListResponse['FreeCredits'], $this->alphaSentryClient->GreyListResponse['PaidCredits']);
        			$failedlogins = $this->alphaSentryClient->GreyListResponse['Value'];
        			if(empty($failedlogins))
        				$failedlogins = 0;
        		}
        
        		if($failedlogins > $this->options['throttle_logins_limit'])
        		{
        			return new WP_Error('failedattempts', __('Too many failed login attempts.'));
        		}
        		else
        		{
        			return $user;
        		}
        	}
        	else
        	{
        		return $user;
        	}
        }
        
        /**
         * Increments account count
         */
        function throttle_account_ip_increment()
        {
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		// Increment value
        		if($this->alphaSentryClient->GreyListIncrementValue($_SERVER['REMOTE_ADDR'], 'wpAccountsPerIP', $this->options['throttle_account_ip_time']))
        		{
        			$this->set_credits($this->alphaSentryClient->GreyListResponse['FreeCredits'], $this->alphaSentryClient->GreyListResponse['PaidCredits']);
        		}
        	}
        	return;
        }
        
        /**
         * Blocks user account creation if there are too many created
         * @param array $errors WordPress registration errors array passed from hook
         * @return array WordPress registration errors list
         */
        function throttle_account_ip($errors)
        {	 
        	$accountscreated = 0;
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		// Increment value
        		if($this->alphaSentryClient->GreyListGetValue($_SERVER['REMOTE_ADDR'], 'wpAccountsPerIP', $this->options['throttle_account_ip_time']))
        		{
        			$this->set_credits($this->alphaSentryClient->GreyListResponse['FreeCredits'], $this->alphaSentryClient->GreyListResponse['PaidCredits']);
        			$accountscreated = $this->alphaSentryClient->GreyListResponse['Value'];
        		}
        		$accountscreated++;
        		if($accountscreated > $this->options['throttle_account_ip_limit'])
        		{
        			$errors->add('too_many_accounts', 'Unable to create account.');
        		}
        	}
        	return $errors;
        }
        
        /**
         * Process AJAX requests
         */
        function process_ajax()
        {
        	// Set default values
        	if(empty($_REQUEST['alphasentry_nonce']))
        		die('No nonce.');
			if (!wp_verify_nonce($_REQUEST['alphasentry_nonce'], 'alphasentry_ajax_nonce'))
				die('Wrong nonce.');
        	if(empty($_REQUEST['alphasentry_action']))
        		$_REQUEST['alphasentry_action'] = '';
        	if(empty($_REQUEST['alphasentry_itemId']))
        		$_REQUEST['alphasentry_itemId'] = '';
        	if(empty($_REQUEST['alphasentry_listName']))
        		$_REQUEST['alphasentry_listName'] = '';
        	if(empty($_REQUEST['alphasentry_expires']))
        		$_REQUEST['alphasentry_expires'] = '';
        	if(empty($_REQUEST['alphasentry_transactionId']))
        		$_REQUEST['alphasentry_transactionId'] = '';
        	
        	// Unescape certain variables
        	$unescapeVars = array('alphasentry_listName', 'alphasentry_itemId');
        	foreach($unescapeVars as $unescapeVar)
        	{
        		if (isset($_REQUEST[$unescapeVar]) && get_magic_quotes_gpc())
        		{
        			$_REQUEST[$unescapeVar] = stripslashes($_REQUEST[$unescapeVar]);
        		}
        	}
        	
        	// If the API key is set
        	if(!empty($this->options['api_key']))
        	{
        		$this->require_library();
        		// Create AlphaSentry client object with your API key
        		if(empty($this->alphaSentryClient))
        			$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        		
        		// Process the action requested via AJAX
        		switch ($_REQUEST['alphasentry_action'])
        		{
        			case 'FlagTransaction':
        				$this->alphaSentryClient->FlagTransaction($_REQUEST['alphasentry_transactionId']);
        				if($this->alphaSentryClient->Response['Success'])
        					$returnString = $this->alphaSentryClient->Response['Success'].','.$this->alphaSentryClient->Response['FreeCredits'].','.$this->alphaSentryClient->Response['PaidCredits'];
        				break;
        			case 'UnflagTransaction':
        				$this->alphaSentryClient->UnflagTransaction($_REQUEST['alphasentry_transactionId']);
        				if($this->alphaSentryClient->Response['Success'])
        					$returnString = $this->alphaSentryClient->Response['Success'].','.$this->alphaSentryClient->Response['FreeCredits'].','.$this->alphaSentryClient->Response['PaidCredits'];
        				break;
        			case 'DeleteTransaction':
        				$this->alphaSentryClient->DeleteTransaction($_REQUEST['alphasentry_transactionId']);
        				if($this->alphaSentryClient->Response['Success'])
        					$returnString = $this->alphaSentryClient->Response['Success'].','.$this->alphaSentryClient->Response['FreeCredits'].','.$this->alphaSentryClient->Response['PaidCredits'];
        				break;
        			case 'RemoveItem':
        				$this->alphaSentryClient->GreyListRemoveItem($_REQUEST['alphasentry_itemId'], $_REQUEST['alphasentry_listName'], $_REQUEST['alphasentry_expires']);
        				if($this->alphaSentryClient->GreyListResponse['Success'])
        					$returnString = $this->alphaSentryClient->GreyListResponse['Success'].','.$this->alphaSentryClient->GreyListResponse['FreeCredits'].','.$this->alphaSentryClient->GreyListResponse['PaidCredits'];
        				break;
        		}
        	}
        	// Echo success boolean and remaining credits
        	echo $returnString;
        }
        
        /**
         * Echoes relevant data for AlphaSentry Data page
         */
        function get_data()
        {
        	// Verify that API key is entered
        	if(empty($this->options['api_key']))
        	{
        		echo 'API Key Required.';
        		return;
        	}
        	$this->require_library();
        	// Create AlphaSentry client object with your API key
        	if(empty($this->alphaSentryClient))
        		$this->alphaSentryClient = new AlphaSentry($this->options['api_key']);
        	
        	// Enumerate list of views (Users, Logins, and GreyList)
        	$views = array('users' => 'Users', 'logins' => 'Logins', 'greylist' => 'GreyList');
        	// Set Users as default
        	if(!array_key_exists($_GET['alphasentry_view'], $views))
        		$_GET['alphasentry_view'] = 'users';
        	
        	// Set server domain as default domain
        	if(($_GET['alphasentry_view'] == 'users' || $_GET['alphasentry_view'] == 'logins') && !isset($_GET['alphasentry_domain']))
        		$_GET['alphasentry_domain'] = $_SERVER['SERVER_NAME'];
        	// Set "Day" as default GreyList group
        	if($_GET['alphasentry_view'] == 'greylist' && empty($_GET['alphasentry_expires']))
        		$_GET['alphasentry_expires'] = 'Day';
        	
        	// Compile and sanitize query/filter data
        	$parameters = $this->compile_data_parameters();
        	$parameters = $this->sanitize_data_parameters($parameters);
        	
        	// If view is for Users...
        	if($_GET['alphasentry_view'] == 'users')
        	{
        		// Send Browse or Get query to AlphaSentry API server, depending on if there is a search query
        		if(empty($_GET['alphasentry_searchvalue']))
	        		$this->alphaSentryClient->BrowseTransactions('Account', '', '', '', '', '', '', $parameters['alphasentry_username'], '', '', $parameters['alphasentry_domain'], '', '', $this->translate_orderby($parameters['alphasentry_orderby']), $parameters['alphasentry_order'], 50, $parameters['alphasentry_nexttoken']);
        		else
        			$this->alphaSentryClient->GetTransactions($parameters['alphasentry_transactionid'], 'Account', $parameters['alphasentry_deviceid'], $parameters['alphasentry_userid'], $parameters['alphasentry_userip'], $parameters['alphasentry_useragent'], '', $parameters['alphasentry_username'], '', $this->translate_orderby($parameters['alphasentry_orderby']), $parameters['alphasentry_order'], 50, $parameters['alphasentry_nexttoken']);
        	}
        	
        	// If view is for Logins...
        	if($_GET['alphasentry_view'] == 'logins')
        	{
        		// Send Browse or Get query to AlphaSentry API server, depending on if there is a search query
        		if(empty($_GET['alphasentry_searchvalue']))
        			$this->alphaSentryClient->BrowseTransactions('Login', '', '', '', '', '', '', $parameters['alphasentry_username'], '', '', $parameters['alphasentry_domain'], '', '', $this->translate_orderby($parameters['alphasentry_orderby']), $parameters['alphasentry_order'], 50, $parameters['alphasentry_nexttoken']);
        		else
        			$this->alphaSentryClient->GetTransactions($parameters['alphasentry_transactionid'], 'Login', $parameters['alphasentry_deviceid'], $parameters['alphasentry_userid'], $parameters['alphasentry_userip'], $parameters['alphasentry_useragent'], '', $parameters['alphasentry_username'], '', $this->translate_orderby($parameters['alphasentry_orderby']), $parameters['alphasentry_order'], 50, $parameters['alphasentry_nexttoken']);
        	}
        	
        	// If view is GreyList
        	if($_GET['alphasentry_view'] == 'greylist')
        	{
        		// Send GreyList browse request to AlphaSentry API server
        		$this->alphaSentryClient->GreyListBrowseItems($parameters['alphasentry_expires'], 50, $parameters['alphasentry_order'], $parameters['alphasentry_nexttoken'], $parameters['alphasentry_userip'], $parameters['alphasentry_useragent'], '', $parameters['alphasentry_username'], '', $this->translate_orderby($parameters['alphasentry_orderby']), $parameters['alphasentry_order'], 50, $parameters['alphasentry_nexttoken']);
        	}
        	
        	// Initialize credits values to N/A
        	$freecredits = $paidcredits = __('N/A', 'alphasentry');
        	
        	// If view is Users or Logins...
        	if($_GET['alphasentry_view'] == 'users' || $_GET['alphasentry_view'] == 'logins') 
        	{
        		// Echo errors if they exist
	        	if(is_array($this->alphaSentryClient->Response['Errors']) && count($this->alphaSentryClient->Response['Errors']))
	        	{
	        		foreach($this->alphaSentryClient->Response['Errors'] as $error)
	        		{
	        			echo '<div class="error"><p><strong>'.__('Error', 'alphasentry').':</strong> '.$error.'</p></div>';
	        		}
	        	}
	        	else // Otherwise, take note of the remaining credits
	        	{
	        		$this->set_credits($this->alphaSentryClient->Response['FreeCredits'], $this->alphaSentryClient->Response['PaidCredits']);
	        		$freecredits = $this->alphaSentryClient->Response['FreeCredits'];
	        		$paidcredits = $this->alphaSentryClient->Response['PaidCredits'];
	        	}
        	}
        	
        	// If view is GreyList
        	if($_GET['alphasentry_view'] == 'greylist')
        	{
        		// Echo errors if they exist
        		if(is_array($this->alphaSentryClient->GreyListResponse['Errors']) && count($this->alphaSentryClient->GreyListResponse['Errors']))
	        	{
	        		foreach($this->alphaSentryClient->GreyListResponse['Errors'] as $error)
	        		{
	        			echo '<div class="error"><p><strong>'.__('Error', 'alphasentry').':</strong> '.$error.'</p></div>';
	        		}
	        	}
	        	else // Otherwise take note of the remaining credits
	        	{
	        		$this->set_credits($this->alphaSentryClient->GreyListResponse['FreeCredits'], $this->alphaSentryClient->GreyListResponse['PaidCredits']);
	        		$freecredits = $this->alphaSentryClient->GreyListResponse['FreeCredits'];
	        		$paidcredits = $this->alphaSentryClient->GreyListResponse['PaidCredits'];
	        	}
        	}
        	
        	// Echo the credits reamining
        	echo '<p style="text-align:right;"><strong>'.__('Free Credits: ', 'alphasentry').'</strong><span id="alphasentry_status_value_FreeCredits">'.number_format($freecredits).'</span> <strong>'.__('Paid Credits: ').'</strong><span id="alphasentry_status_value_PaidCredits">'.number_format($paidcredits).'</span> [<a href="https://www.alphasentry.com/buy-credits.php" target="_blank">'.__('Buy Credits', 'alphasentry').'</a>] </p>';
        	
        	// Echo the list of potential views (Users, Logins, GreyList)
        	echo '<p>
        	<ul class="subsubsub">';
        	foreach($views as $currentviewid => $currentview)
        	{	
        		if($currentviewid == $_GET['alphasentry_view'])
        			echo '<li><a href="?alphasentry_view='.$currentviewid.'&amp;page=wp-alphasentry/wordpress-alphasentry.php?data=1" class="current">'.__($currentview, 'alphasentry').'</a>';
        		else
        			echo '<li><a href="?alphasentry_view='.$currentviewid.'&amp;page=wp-alphasentry/wordpress-alphasentry.php?data=1">'.__($currentview, 'alphasentry').'</a>';
        		
        		if($currentviewid == 'greylist')
        			echo '</li>';
        		else
        			echo ' |</li>';
        	}
        	echo '</ul>
        	</p>';
        	
        	// If view is Sentry API (Users or Logins)...
        	if($_GET['alphasentry_view'] == 'users' || $_GET['alphasentry_view'] == 'logins')
        	{
	        	// echo search form
	        	$tempparameters = $parameters;
	        	unset($tempparameters['alphasentry_searchby']);
	        	unset($tempparameters['alphasentry_searchvalue']);
	        	echo '<form action="" method="get">
		        	<p class="search-box">
		        	'.$this->build_data_hidden_vars($tempparameters).'
		        	<label class="" for="alphasentry_searchvalue">'.__('Search', 'alphasentry').':</label>';
		        $this->searchby_dropdown($parameters['alphasentry_searchby']);
		        echo '<input type="search" name="alphasentry_searchvalue" value="'.$parameters['alphasentry_searchvalue'].'" />
		        	<input type="submit" name="" id="" class="button" value="Search"  />
		        	</p>
	        	</form>';
	        	
	        	// echo domain selector
	        	$tempparameters = $parameters;
	        	unset($tempparameters['alphasentry_domain']);
	        	echo '
	        	<div class="tablenav top">
		        	<div class="alignleft actions">
		        	<form action="" method="get">
		        	'.$this->build_data_hidden_vars($tempparameters).'
		        	<!-- <label class="" for="alphasentry_domain">'.__('Data Domain', 'alphasentry').':</label> -->
		        	<select name="alphasentry_domain">
		        		<option value="'.$_SERVER['SERVER_NAME'].'" '.($_GET['alphasentry_domain'] == $_SERVER['SERVER_NAME'] ? 'selected="selected"' : '').'>'.$_SERVER['SERVER_NAME'].'</option>
		        		<option value="" '.($_GET['alphasentry_domain'] == $_SERVER['SERVER_NAME'] ? '' : 'selected="selected"').'>'.__('All Domains', 'alphasentry').'</option>
		        	</select>
		        	<input type="submit" name="" class="button" value="Update"  />
		        	</form>
		        	</div>';
	        	
	        	// echo pagination
		        echo '<form action="" method="get">
		        <p class="search-box">';
		        if(!empty($_GET['alphasentry_nexttoken']))
		        	echo '<input type="submit" name="" id="" class="button" title="'.__('Go to Previous Page', 'alphasentry').'" onclick="history.go(-1); return false;" value="&lsaquo; '.__('Prev', 'alphasentry').'"  />';
		        if(!empty($this->alphaSentryClient->Response['NextToken']))
		        {
		        	$tempparameters = $parameters;
		        	$tempparameters['alphasentry_nexttoken'] = $this->alphaSentryClient->Response['NextToken'];
		        	echo '
		        	'.$this->build_data_hidden_vars($tempparameters).'
		        	<input type="submit" name="" id="" class="button" title="'.__('Go to Next Page', 'alphasentry').'" value="'.__('Next', 'alphasentry').' &rsaquo;"  />';
		        }
		        echo '
		        </p>
		        </form>';
	        	
		        // Set column headers
        		$columnheaders = '
        			<tr>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_userid', false).'"><span>'.__('ID', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_username', false).'"><span>'.__('UserName', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_risk').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_risk').'"><span>'.__('Risk', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_devicesperuser').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_devicesperuser').'" title="'.__('Devices Per User').'"><span>'.__('D/U', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_usersperdevice').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_usersperdevice').'" title="'.__('Users Per Device').'"><span>'.__('U/D', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_accountsperip').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_accountsperip').'" title="'.__('Users Per IP Address').'"><span>'.__('U/IP', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_transactionid', false).'"><span>'.__('Transaction', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_deviceid', false).'"><span>'.__('Device', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_userip', false).'"><span>'.__('IP Address', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_useragent', false).'"><span>'.__('UserAgent', 'alphasentry').'</span></th>
        				<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_transactiontime').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_transactiontime').'"><span>'.__('Time', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        				<th></th>
        				<th></th>	
        		</tr>';
        		
		        // Echo table header and footer
        		echo '
        	<table class="wp-list-table widefat users" cellspacing="0">
        		<thead>
        			'.$columnheaders.'
        		</thead>
        		<tfoot>
        			'.$columnheaders.'
        		</tfoot>
        		<tbody id="the-list">';
        		
        		// Echo data
        		$alertnaterow = true;
        		if(is_array($this->alphaSentryClient->Response['Transactions']))
        		{
	        		foreach($this->alphaSentryClient->Response['Transactions'] as $transaction)
	        		{
	        			$transaction = get_object_vars($transaction);
	        			echo '
	        			<tr id="alphasentry_Transactions_row_'.$transaction['TransactionId'].'" class="'.($alternaterow ? 'alternate' : '').'">
	        				<td class=""><a href="user-edit.php?user_id='.$transaction['UserId'].'" title="'.$transaction['UserId'].'">'.$transaction['UserId'].'</a></td>
	        				<td class=""><a href="plugins.php?'.$this->build_searching_data_query($parameters, 'alphasentry_username', $transaction['UserVar1']).'" title="'.htmlspecialchars($transaction['UserVar1']).'">'.htmlspecialchars($transaction['UserVar1']).'</a></td>
	        				<td class="">'.$transaction['RiskScore'].'</td>
	        				<td class="">'.$transaction['DevicesPerUser'].'</td>
	        				<td class="">'.$transaction['UsersPerDevice'].'</td>
	        				<td class="">'.$transaction['AccountsPerIp'].'</td>
	        				<td class=""><a href="plugins.php?'.$this->build_searching_data_query($parameters, 'alphasentry_transactionid', $transaction['TransactionId']).'" title="'.$transaction['TransactionId'].'">'.substr($transaction['TransactionId'], 0, 6).'...</a></td>
	        				<td class=""><a href="plugins.php?'.$this->build_searching_data_query($parameters, 'alphasentry_deviceid', $transaction['DeviceId']).'" title="'.$transaction['DeviceId'].'">'.substr($transaction['DeviceId'], 0, 6).'...</a></td>
	        				<td class=""><a href="plugins.php?'.$this->build_searching_data_query($parameters, 'alphasentry_userip', $transaction['UserIp']).'" title="'.$transaction['UserIp'].'">'.$transaction['UserIp'].'</a></td>
	        				<td class=""><a href="plugins.php?'.$this->build_searching_data_query($parameters, 'alphasentry_useragent', $transaction['UserUserAgent']).'" title="'.htmlspecialchars($transaction['UserUserAgent']).'">'.htmlspecialchars(substr($transaction['UserUserAgent'], 13, 20)).'...</a></td>
	        				<td class="">'.date('m/d/y h:ia', $transaction['TransactionTime']).'</td>';
	        			if(!empty($transaction['Flagged']) && $transaction['Flagged'])
	        				echo '<td id="alphasentry_Transactions_'.$transaction['TransactionId'].'_Flag"><button class="button" onclick="alphasentry_UnflagTransaction(\''.$transaction['TransactionId'].'\');">'.__('Unflag', 'alphasentry').'</button></td>';
						else
	        				echo '<td id="alphasentry_Transactions_'.$transaction['TransactionId'].'_Flag"><button class="button" onclick="alphasentry_FlagTransaction(\''.$transaction['TransactionId'].'\');">'.__('Flag', 'alphasentry').'</button></td>';
	        			echo 	'<td class=""><button class="button" onclick="alphasentry_DeleteTransaction(\''.$transaction['TransactionId'].'\');">'.__('Delete', 'alphasentry').'</button></td>
	        			</tr>';
	        			if($alternaterow)
	        				$alternaterow = false;
	        			else
	        				$alternaterow = true;
	        		}
        		}
        		echo '
        		</tbody>
        	</table>
        	<div class="tablenav bottom">
        		<div class="tablenav-pages"><span class="displaying-num">'.count($this->alphaSentryClient->Response['Transactions']).(empty($this->alphaSentryClient->Response['NextToken']) ? '' : '+').' '.__('items', 'alphasentry').'</span></div>
        	</div>';
        	}
        	
        	// If view is GreyList...
        	if($_GET['alphasentry_view'] == 'greylist')
        	{
        		// echo timeframe selector
        		$tempparameters = $parameters;
        		unset($tempparameters['alphasentry_expires']);
        		echo '
        		<div class="tablenav top">
        		<div class="alignleft actions">
        		<form action="" method="get">
        		'.$this->build_data_hidden_vars($tempparameters).'
        		<!-- <label class="" for="alphasentry_expires">'.__('Select GreyList', 'alphasentry').':</label> -->';
        		$this->expires_dropdown($parameters['alphasentry_expires']);
        		echo '
        		<input type="submit" name="" class="button" value="Update"  />
        		</form>
        		</div>';
        		
        		// echo pagination
        		echo '<form action="" method="get">
        		<p class="search-box">';
        		if(!empty($_GET['alphasentry_nexttoken']))
        			echo '<input type="submit" name="" id="" class="button" title="'.__('Go to Previous Page', 'alphasentry').'" onclick="history.go(-1); return false;" value="&lsaquo; '.__('Prev', 'alphasentry').'"  />';
        		
        		if(!empty($this->alphaSentryClient->GreyListResponse['NextToken']))
        		{
        			$tempparameters = $parameters;
        			$tempparameters['alphasentry_nexttoken'] = $this->alphaSentryClient->GreyListResponse['NextToken'];
        			echo '
        			'.$this->build_data_hidden_vars($tempparameters).'
        			<input type="submit" name="" id="" class="button" title="'.__('Go to Next Page', 'alphasentry').'" value="'.__('Next', 'alphasentry').' &rsaquo;"  />';
        		}
        		echo '
        		</p>
        		</form>';
        		
        		// Set column headers
        		$columnheaders = '
        		<tr>
        		<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_userid', false).'"><span>'.__('User ID', 'alphasentry').'</span></th>
        		<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_username', false).'"><span>'.__('List', 'alphasentry').'</span></th>
        		<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_value').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_value').'"><span>'.__('Value', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        		<th scope="col" class="'.$this->data_column_header_class($parameters, 'alphasentry_transactiontime').'"><a href="plugins.php?'.$this->build_sorting_data_query($parameters, 'alphasentry_transactiontime').'"><span>'.__('Last Updated', 'alphasentry').'</span><span class="sorting-indicator"></span></a></th>
        		<th></th>
        		</tr>';
        		
        		// Echo table header and footer
        		echo '
        	<table class="wp-list-table widefat" cellspacing="0">
        		<thead>
        			'.$columnheaders.'
        		</thead>
        		<tfoot>
        			'.$columnheaders.'
        		</tfoot>
        		<tbody id="the-list">';
        		
        		// Echo data
        		$alertnaterow = true;
        		if(is_array($this->alphaSentryClient->GreyListResponse['Items']))
        		{
	        		foreach($this->alphaSentryClient->GreyListResponse['Items'] as $item)
	        		{
	        			$item = get_object_vars($item);
	        			echo '
	        			<tr id="alphasentry_BrowseItems_Row'.$item['ListName'].'-'.$item['ItemId'].'-'.$item['Expires'].'" class="'.($alternaterow ? 'alternate' : '').'">
	        				<td class="">'.$item['ItemId'].'</td>
	        				<td class="">'.$item['ListName'].'</td>
	        				<td class="">'.$item['Value'].'</td>
	        				<td class="">'.date('m/d/y h:ia', $item['ItemTime']).'</td>
	        				<td class=""><button class="button" onclick="alphasentry_RemoveItem(\''.addslashes($item['ItemId']).'\', \''.addslashes($item['ListName']).'\', \''.addslashes($item['Expires']).'\');">'.__('Remove', 'alphasentry').'</button></td>
	        			</tr>';
	        			if($alternaterow)
	        				$alternaterow = false;
	        			else
	        				$alternaterow = true;
	        		}
        		}
        		echo '
        		</tbody>
        		</table>
        		<div class="tablenav bottom">
        		<div class="tablenav-pages"><span class="displaying-num">'.count($this->alphaSentryClient->GreyListResponse['Items']).(empty($this->alphaSentryClient->GreyListResponse['NextToken']) ? '' : '+').' '.__('items', 'alphasentry').'</span></div>
        		</div>';
        	}
        }
        
        /**
         * Generates an appropriate styling class specification for data column headers
         * @param array $parameters Query aprameters
         * @param string $field Field for column
         * @param boolean $sortable Whether or not the column can be sorted
         * @return string Column header styling class
         */
        function data_column_header_class($parameters, $field, $sortable = true)
        {
        	$classstring = 'manage-column column-'.$field;
        	if($sortable)
        	{
        		if($_GET['alphasentry_orderby'] == $field)
        		{
        			$classstring .= ' sorted';
        			
        			if($_GET['alphasentry_order'] == 'ASC')
        				$classstring .= ' asc';
        			else
        				$classstring .= ' desc';
        		}
        		else
        		{
        			$classstring .= ' sortable asc';
        		}
        	}
        	return $classstring;
        }
        
        /**
         * Ensure that values passed to the get_data() function are valid
         * @param array $parameters Key-Value array of parameters passed to data query
         * @return array Sanitized Key-Value array of parameters with valid values
         */
        function sanitize_data_parameters($parameters)
        {
        	
        	// Only allow one searchby field
        	$filters = array('alphasentry_transactionid', 'alphasentry_userid', 'alphasentry_username', 'alphasentry_ip', 'alphasentry_useragent', 'alphasentry_deviceid', 'alphasentry_city');
        	$filterfound = false;
        	$searchby = '';
        	if(!empty($parameters['alphasentry_searchby']) && in_array($parameters['alphasentry_searchby'], $filters))
        	{
        		$searchby = $parameters['alphasentry_searchby'];
        		$filterfound = true;
        	}
        	foreach($filters as $filter)
        	{
        		if($filterfound)
        		{
        			unset($parameters[$filter]);
        		}
        		else
        		{
        			if(isset($parameters[$filter]))
        			{
        				$filterfound = true;
        			}
        		}
        	}
        	if(strlen($searchby))
        	{
        		$parameters[$searchby] = $parameters['alphasentry_searchvalue'];
        	}
        	return $parameters;
        }
        
        /**
         * Translates orderby values from the plugin to valid API values
         * @param string $field Name of the field to orderby
         * @return string Valid orderby value for AlphaSentry API
         */
        function translate_orderby($field)
        {
        	$fields = array (
        			'alphasentry_risk' => 'RiskScore',
        			'alphasentry_devicesperuser' => 'DevicesPerUser',
        			'alphasentry_usersperdevice' => 'UsersPerDevice',
        			'alphasentry_accountsperip' => 'AccountsPerIp',
        			'alphasentry_transactiontime' => 'TransactionTime'
        	);
        	return $fields[$field];
        }
        
        /**
         * Generates a Key-Value array of GET fields that have the alphasentry prefix
         * @return array Key-Value array of AlphaSentry GET fields and values
         */
        function compile_data_parameters()
        {
        	$parameters = array();
        	foreach($_GET as $key => $value)
        	{
        		if(preg_match('/^alphasentry_/', $key))
        			$parameters[$key] = $value;
        	}
        	return $parameters;
        }
        
        /**
         * Generates a query string with specific sorting parameters
         * @param array $parameters Key-Value array of search/filter values
         * @param string $field Name of the field to sort by
         * @return string Query string to sort by the specified field
         */
        function build_sorting_data_query($parameters, $field)
        {
        	if(isset($parameters['alphasentry_orderby']) && $parameters['alphasentry_orderby'] == $field)
        	{
        		if($parameters['alphasentry_order'] == 'ASC')
        			$parameters['alphasentry_order'] = 'DESC';
        		else
        			$parameters['alphasentry_order'] = 'ASC';
        	}
        	else
        		$parameters['alphasentry_order'] = 'DESC';
        	
        	$parameters['alphasentry_orderby'] = $field;
        	return $this->build_data_query($parameters);
        }
        
        /**
         * Generates a query string with a specific search query specified
         * @param array $parameters Key-Value array of search/filter values
         * @param string $field Name of the field to search by
         * @param string $value Value to search for
         * @return string Query string to search for specified values
         */
        function build_searching_data_query($parameters, $field, $value)
        {
        	$parameters['alphasentry_searchby'] = $field;
        	$parameters['alphasentry_searchvalue'] = $value;
        	return $this->build_data_query($parameters);
        }
        
        /**
         * Generates a query string of search/filter variables for a GET action form
         * @param array $parameters Key-Value array of search/filter values
         * @return string Query string to be echoed into a link
         */
        function build_data_query($parameters)
        {
        	unset($parameters['alphasentry_nexttoken']);
        	$querystring = 'page='.urlencode('wp-alphasentry/wordpress-alphasentry.php?data=1');
        	foreach($parameters as $key => $value)
        	{
        		$querystring .= '&amp;'.$key.'='.urlencode($value);
        	}
        	return $querystring;
        }
        
        /**
         * Generates a list of hidden variables to be used when filtering and searching data parameters in a POST action form
         * @param array $parameters Key-Value array of search/filter values
         * @return string Hidden form fields to be echoed into a search/filter form
         */
        function build_data_hidden_vars($parameters)
        {
        	$varlist = '<input type="hidden" name="page" value="wp-alphasentry/wordpress-alphasentry.php?data=1" />';
        	foreach($parameters as $key => $value)
        	{
        		$varlist .= '
        		<input type="hidden" name="'.$key.'" value="'.$value.'" />';
        	}
        	return $varlist;
        }
        
        // add a settings link to the plugin in the plugin list
        /**
         * Add a settings link to list of links in admin plugins menu
         * @param array $links Links passed to function from WordPress
         * @param string $file Path to the file to be added to the links list
         * @return array Links list returned, with settings link added
         */
        function show_settings_link($links, $file) 
        {
            if ($file == plugin_basename($this->path_to_plugin_directory().'/wp-alphasentry.php')) {
               $settings_title = __('Settings for this Plugin', 'alphasentry');
               $settings = __('Settings', 'alphasentry');
               //$settings_link = '<a href="options-general.php?page=wp-alphasentry/wordpress-alphasentry.php" title="' . $settings_title . '">' . $settings . '</a>';
               $settings_link = '<a href="admin.php?page=wp-alphasentry/wordpress-alphasentry.php" title="' . $settings_title . '">' . $settings . '</a>';
                
               //array_unshift($links, $settings_link);
               $links[] = $settings_link;
            }
            
            return $links;
        }
        
        /**
         * Store the credit counts in a php file
         * @param integer $freecredits AlphaSentry free credits remaining
         * @param integer $paidcredits AlphaSentry paid credits remaining
         */
        function set_credits($freecredits, $paidcredits)
        {
        	$filecontents = '<?php $credits = \''.$freecredits.','.$paidcredits.'\'; ?>';
        	file_put_contents($this->path_to_plugin_directory().'/credits.php', $filecontents);
        }
        
        /**
         * Returns a comma delimited string indicating the number of free and paid AlphaSentry credits remaining, respectively
         * 
         * @return string Free Credits,Paid Credits
         */
        function get_credits()
        {
        	if(!is_file($this->path_to_plugin_directory().'/credits.php'))
        		return '';
        	$filecontents = file_get_contents($this->path_to_plugin_directory().'/credits.php');
        	$credits = str_replace('<?php $credits = \'', '', str_replace('\'; ?>', '', $filecontents));
        	return $credits;
        }
       	
        /**
         * Add the AlphaSentry settings and data pages
         */
        function add_settings_page()
        {
            // add the options page
            add_submenu_page('plugins.php', __('AlphaSentry Settings'), __('AlphaSentry Settings'), 'manage_options', __FILE__, array(&$this, 'show_settings_page'));
            add_submenu_page('plugins.php', __('AlphaSentry Data'), __('AlphaSentry Data'), 'manage_options', __FILE__.'?data=1', array(&$this, 'show_data_page'));
        }
        
        
        /**
         * Include the AlphaSentry settings page
         */
        function show_settings_page() 
        {
            include('settings.php');
        }
        
        /**
         * Include the AlphaSentry Data page
         */
        function show_data_page()
        {
        	include('data.php');
        }
        
        /**
         * Echoes a dropdown form input
         * 
         * @param string $name Name of the input
         * @param array $keyvalue Key-Value paired array of possible input values
         * @param string $checked_value the value that is currently selected
         */
        function build_dropdown($name, $keyvalue, $checked_value) 
        {
            echo '<select name="'.$name.'" id="'.$name.'">'."\n";
            
            foreach ($keyvalue as $key => $value) 
            {
                $checked = ($value == $checked_value) ? ' selected="selected" ' : '';
                
                echo "\t <option value=\"".$value.'"'.$checked.'>'.$key."</option> \n";
                $checked = NULL;
            }
            
            echo "</select> \n";
        }
        
        /**
         * Echoes a dropdown input for the transaction search
         * 
         * @param string $currentvalue Current value of the input
         */
        function searchby_dropdown($currentvalue)
        {
        	// define choices: Display text => permission slug
        	$searchbys = array (
        			__('TransactionID', 'alphasentry') => 'alphasentry_transactionid',
        			__('UserID', 'alphasentry') => 'alphasentry_userid',
        			__('UserName', 'alphasentry') => 'alphasentry_username',
        			__('IP Address', 'alphasentry') => 'alphasentry_ip',
        			__('UserAgent', 'alphasentry') => 'alphasentry_useragent',
        			__('DeviceID', 'alphasentry') => 'alphasentry_deviceid',
        			__('GeoIP City', 'alphasentry') => 'alphasentry_city'
        	);
        
        	$this->build_dropdown('alphasentry_searchby', $searchbys, $currentvalue);
        }
        
        /**
         * Echoes a dropdown input for GreyList timeframes
         * 
         * @param string $currentvalue Current value of the input
         */
        function expires_dropdown($currentvalue)
        {
        	// define choices: Display text => permission slug
        	$timeframes = array (
        			__('Hourly', 'alphasentry') => 'Hour',
        			__('Daily', 'alphasentry') => 'Day',
        			__('Weekly', 'alphasentry') => 'Week',
        			__('Monthly', 'alphasentry') => 'Month',
        			__('Yearly', 'alphasentry') => 'Year'
        	);
        
        	$this->build_dropdown('alphasentry_expires', $timeframes, $currentvalue);
        }
        
        /**
         * Echoes a dropdown input for throttling timeframes
         * 
         * @param string $fieldname Name of the form input field
         */
        function timeframe_dropdown($fieldname)
        {        	
        	// define choices: Display text => permission slug
        	$timeframes = array (
        			__('Hourly', 'alphasentry') => 'Hour',
        			__('Daily', 'alphasentry') => 'Day',
        			__('Weekly', 'alphasentry') => 'Week',
        			__('Monthly', 'alphasentry') => 'Month',
        			__('Yearly', 'alphasentry') => 'Year'
        	);
        
        	$this->build_dropdown('alphasentry_options['.$fieldname.']', $timeframes, $this->options[$fieldname]);
        }
        
        /**
         * Echoes a dropdown input for throttling limits
         * 
         * @param string $fieldname Name of the form input field
         */
        function limits_dropdown($fieldname)
        {
        	// define choices: Display text => permission slug
        	$limits = array (
        			__('1', 'alphasentry') => '1',
        			__('2', 'alphasentry') => '2',
        			__('3', 'alphasentry') => '3',
        			__('4', 'alphasentry') => '4',
        			__('5', 'alphasentry') => '5',
        			__('6', 'alphasentry') => '6',
        			__('7', 'alphasentry') => '7',
        			__('8', 'alphasentry') => '8',
        			__('9', 'alphasentry') => '9',
        			__('10', 'alphasentry') => '10',
        			__('15', 'alphasentry') => '15',
        			__('20', 'alphasentry') => '20',
        			__('25', 'alphasentry') => '25',
        			__('50', 'alphasentry') => '50'
        	);
        
        	$this->build_dropdown('alphasentry_options['.$fieldname.']', $limits, $this->options[$fieldname]);
        }        
    } // end class declaration
} // end of class exists clause

if (!function_exists('file_put_contents')) 
{
	
	/**
	 * Save string to a file
	 * @param string $filename Name of file
	 * @param string $data Data to apply
	 * @return boolean|number Fales if failure, Bytes if success
	 */
	function file_put_contents($filename, $data) 
	{
		$f = @fopen($filename, 'w');
		if (!$f) 
		{
			return false;
		} else {
			$bytes = fwrite($f, $data);
			fclose($f);
			return $bytes;
		}
	}
}

if (!function_exists('file_get_contents'))
{
	/**
	 * Gets the contents of a local file
	 * @param string $filename Name of the file
	 * @return string File contents
	 */
	function file_get_contents($filename)
	{
		$fhandle = fopen($filename, 'r');
		$contents = fread($fhandle, filesize($filename));
		fclose($fhandle);
		return $contents;
	}
}

?>
