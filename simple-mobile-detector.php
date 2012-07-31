<?php
/*
Plugin Name: Simple Mobile Detector
Plugin URI: http://nois3lab.it/
Description: Helps you defining simple yet specific configuration for every kind of mobile device and browser.
Author: Marco Antonutti
Author URI: http://marktheweb.it/
Version: 0.1
*/
/*
* This plugin is based on a very smart and lightweight php library called php-mobile-detect by Victor Stanciu. http://code.google.com/p/php-mobile-detect/
* Integration with wordpress and coding by Marco Antonutti, senior developer at http://nois3lab.it/en
* @File
* Main file. Function namespace: simple_mobile_detector
*/

/* Hooks area */
add_action('setup_theme', 'simple_mobile_detector_init');
add_action('admin_init', 'simple_mobile_detector_admin_init');
add_action('admin_menu', 'simple_mobile_detector_add_menu');


function simple_mobile_detector_init() {
	include_once('Mobile_Detect.php');
	$detect = new Mobile_Detect;
	//if not a mobile don't check for rules and stuff.
	if(!$detect->isMobile())
		return;

	//die($_SERVER['HTTP_USER_AGENT']);
	$rules = get_option("simple_mobile_detector_rules");
	$rules = (is_array($rules) ? $rules: explode(",",$rules));
	foreach($rules as $rule) {
		$rule = unserialize(get_option("smd_".$rule));
		//var_dump($rule['device_type']);
		switch ($rule['device_type']) {
			case 1:
				//if is mobile but is a tablet then is a smartphone, so don't filter
				if($detect->isTablet())
					continue;
			break;
			case 2:
				//if is not a tablet don't filter
				if(!$detect->isTablet())
					continue;
			break;
		}
		$df = true;
		$os = true;
		$ua = true;
		if(isset($rule['device_filter']))
			$df = simple_mobile_detector_filter($rule['device_filter'], &$detect);
		if($df == false)
			continue;

		if(isset($rule['operating_system']))
			$os = simple_mobile_detector_filter($rule['operating_system'], &$detect);
			if($os == false)
				continue;

		if(isset($rule['user_agent']))
			$ua = simple_mobile_detector_filter($rule['user_agent'], &$detect);
			if($ua == false)
				continue;

		//now fireeee the action.
		switch($rule['action_type']) {
			case '1':
				wp_redirect($rule['action_value'], 301 ); exit;
			break;
			case '2':
			global $smd_mobile_theme;
			$smd_mobile_theme = $rule['action_value'];
			add_filter('template', 'simple_mobile_detector_load_temd',1);	
			add_filter('stylesheet', 'simple_mobile_detector_load_style',1);
			break;
			case '3':
			add_action('wp_head', 'simple_mobile_detector_load_wp_head');
			break;
		}
	}
}	


/* filtering functions */
function simple_mobile_detector_filter($devices, &$detect) {
	$devices = explode(",", $devices);
	foreach($devices as $device):
		$devicefunc = "is".$device;
		if($detect->$devicefunc())
			$return = true;
	endforeach;
	if($return)
		return true;
	else
		return false;
}

function simple_mobile_detector_load_style($cose) {
	global $smd_mobile_theme;
 	$themes = get_themes();
	foreach ($themes as $theme) {
	  if ($theme['Name'] == $smd_mobile_theme) {
	      return $theme['Stylesheet'];
	  }
	}	
}
function simple_mobile_detector_load_temd($cose) {
	global $smd_mobile_theme;
	$themes = get_themes();
	foreach ($themes as $theme) {
	  if ($theme['Name'] == $smd_mobile_theme) {
	      return $theme['Template'];
	  }
	}	
//
}

function simple_mobile_detector_load_wp_head() {
	
}
/* Admin settings page */
function simple_mobile_detector_add_menu() {
add_options_page('Simple Mobile Detector', 'Simple Mobile Detector', 'manage_options', 'simple-mobile-detector', 'simple_mobile_detector_settings_page');
}

function simple_mobile_detector_settings_page() {
	$rulesArray = get_option("simple_mobile_detector_rules");
	if(!isset($_GET['rule']))
		include('simple-mobile-detector-admin-dashboard.php');
	else
		include('simple-mobile-detector-rule-settings.php');
}
/* Admin notices section */
function simple_mobile_detector_name_existing() {
	 echo '<div class="error">
       <p>Rule name already exists. Please, choose a different one.</p>
    </div>';
}
function simple_mobile_detector_rule_added() {
	 echo '<div class="updated">
       <p>New rule added successfully.</p>
    </div>';
}
function simple_mobile_detector_options_saved() {
	 echo '<div class="updated">
       <p>Settings saved</p>
    </div>';
}
//for admin template
function simple_md_t($string) {
	if(is_numeric($string)):
		switch($string) {
			case 1:
			$string = 'Smartphone';
			break;
			case 2:
			$string = 'Tablet';
			break;
			case 3:
			$string = 'Phone & Tablet';
			break;
		}
		return $string;
	endif;

	return 'Not defined';
}

/* saving input data */
function simple_mobile_detector_admin_init() {

	if(isset($_POST['simple_mobile_detector_nonce']) && wp_verify_nonce($_POST['simple_mobile_detector_nonce'],'simple_mobile_detector')):
		//check for new rules added
		if(isset($_POST['simple_mobile_detector_rule_name'])):
			$_POST['simple_mobile_detector_rule_name'] = sanitize_title($_POST['simple_mobile_detector_rule_name']);
			$rulesArray = get_option("simple_mobile_detector_rules");
			if(empty($rulesArray)):
				update_option("simple_mobile_detector_rules", $_POST['simple_mobile_detector_rule_name']);
			else:
				if(is_array($rulesArray))
					$rulesArray = implode(",", $rulesArray);

				$rules = explode(",", $rulesArray);
				if(in_array($_POST['simple_mobile_detector_rule_name'], $rules)):
					add_action('admin_notices', 'simple_mobile_detector_name_existing');
					return;
				else:
					add_action('admin_notices', 'simple_mobile_detector_rule_added');
				endif;
				$newRulesArray = $rulesArray.",".$_POST['simple_mobile_detector_rule_name'];
				update_option("simple_mobile_detector_rules", $newRulesArray);
			endif;
		endif;
		//check for deteling rules
		if(isset($_POST['delete_rule'])):
			$rule_name = $_POST['simple_mobile_detector_rule_delete'];
			$rules     = get_option("simple_mobile_detector_rules");
			if(!is_array($rules))
				$rules = explode(",", $rules);
			
			foreach($rules as $key => $value):
				if($value == $rule_name)
				unset($rules[$key]);
			endforeach;
			$rules = implode(",", $rules);
			update_option('simple_mobile_detector_rules', $rules);
		endif;
		//check if a rules has been configured
		if(isset($_POST['device_type'])):
			//print_r($_POST);
			$rule['device_type'] = $_POST['device_type'];
			if($rule['device_type'] == 1 && isset($_POST['smartdevice'])):
				$rule['device_filter'] = implode(",",$_POST['smartdevice']);
			endif;
			if($rule['device_type'] == 2 && isset($_POST['tabletdevice'])):
				$rule['device_filter'] = implode(",",$_POST['tabletdevice']);
			endif;

			if(isset($_POST['operatingsystem']))
				$rule['operating_system'] = implode(",",$_POST['operatingsystem']);

			if(isset($_POST['useragent']))
				$rule['user_agent'] = implode(",",$_POST['useragent']);

			if(isset($_POST['action_type'])):
				$rule['action_type']  = $_POST['action_type'];
				$rule['action_value'] = $_POST['action_value'];
			endif;
			add_action('admin_notices', 'simple_mobile_detector_options_saved');
			
			update_option("smd_".$_POST['rule_name'], serialize($rule));
		endif;
	endif;
}