<?php
$rules = explode(",", $rulesArray);
$rule = $_GET['rule']; 
if(!in_array($rule, $rules)):
	echo 'Rule not found.';
else:
	include_once('Mobile_Detect.php');
	$detect = new Mobile_Detect;

	//get previous settings
	$settings = get_option("smd_".$rule);
	if(!is_array($settings))
	$settings = unserialize($settings);

$theme_list = wp_get_themes();
//select build for javascript. not that elegant but i haven't much time to spend on it :)
$js_select = '<select name="action_value" id="actionv">';
foreach($theme_list as $theme):
	$js_select.= '<option value="'.$theme['Name'].'"';
	if($theme['Name'] == $settings['action_value'])
		$js_select.= ' selected';
 	$js_select.= '>'.$theme['Name'].'</option>';
endforeach;
$js_select.= '</select>';
?>
<script type="text/javascript">
	function hideSmartphone() {
		document.getElementById('smartphone').style.display = 'none';
		document.getElementById('tablet').style.display = 'block';
		document.getElementById('else').style.display = 'block';

	}
	function hideTablet() {
		document.getElementById('smartphone').style.display = 'block';
		document.getElementById('tablet').style.display = 'none';
		document.getElementById('else').style.display = 'block';

	}
	function hideBoth() {
		document.getElementById('smartphone').style.display = 'none';
		document.getElementById('tablet').style.display = 'none';
		document.getElementById('else').style.display = 'block';

	}
	function fillActionWrapper(selAction) {
		switch(selAction) {
			case '1':
				document.getElementById('action_wrapper').innerHTML = '<label for="actionv">Redirect url: (es http://m.example.com) </label><input type="text" id="actionv" name="action_value" value="<?php echo $settings['action_value']; ?>" placeholder="redirect url" style="border-color: #000;"/>';
			break;
			case '2':
				document.getElementById('action_wrapper').innerHTML = '<label for="actionv">Select a theme </label><?php echo $js_select; ?>';
			break;
			case '3':
				document.getElementById('action_wrapper').innerHTML = '<label for="actionv">Any html tag </label><textarea id="actionv" name="action_value" style="border-color: #000;"><?php echo $settings['action_value']; ?></textarea>';
			break;
		}
	}
	<?php
	echo 'function wpOnload() {';
	if(isset($settings['action_type'])) 
		echo ' fillActionWrapper("'.$settings["action_type"].'");'; 
	if(isset($settings['device_type'])):
		switch($settings['device_type']) {
			case '1':
				echo 'hideTablet();';
			break;
			case '2':
				echo 'hideSmartphone();';
			break;
			case '3':
				echo 'hideBoth();';
			break;
		}
	endif;
	echo '}';
	?>
</script>
<h2> Settings for rule <?php echo $rule; ?></h2>
<p>Fast guide:<br/>
If you choose to filter by smartphone or tablet, you will be able to define one or more brand/manifacturer (iphone, samsung). <br/>
Leaving each device checkbox blank, your rule will apply to every smartphone or tablet - otherwise, the rule will be applied only to the specified ones. <br/>
Same thing happens when you specify OS or Browser. For istance, selecting Smartphone, then iPhone, then iOS, then Chrome will match only iphone user using iOS (:P) and Chrome.</br>
While this may seems useless, it can be handy when you have to make some JS/CSS hacks for some certain browser/OS. <br/>
After selecting all your filter criteria, you can specify one action for each rule. An action is, for example, a redirect, a theme switching or an include of some CSS/js code.</br>

</p>
<form name="simple_mobile_detector_rule_settings" method="post">
Target device type:<br/>
<input type="radio" name="device_type" value="1" id="smartphonerd" onClick="hideTablet();" <?php if($settings['device_type'] == 1) echo 'checked'; ?>> <label for="smartphonerd">Smartphone</label><br/>
<input type="radio" name="device_type" value="2" id="tabletrd" onClick="hideSmartphone();" <?php if($settings['device_type'] == 2) echo 'checked'; ?>> <label for="tabletrd">Tablet</label><br/>
<input type="radio" name="device_type" value="3" id="bothrd" onClick="hideBoth();" <?php if($settings['device_type'] == 3) echo 'checked'; ?>> <label for="bothrd">Both</label><br/>

<div id="smartphone" style="display:hidden;margin-top: 14px;">
	Select one or more device (<strong>leave blank: every phone</strong>)<br/><br/>
	<?php foreach($detect->phoneDevices as $device => $regex): 
		echo '<input type="checkbox" name="smartdevice[]" id="'.$device.'" value="'.$device.'"';
		if(in_array($device, explode(",",$settings['device_filter'])))
			echo 'checked';

		echo '> <label for="'.$device.'">'.$device.'</label><br/>';
	 endforeach; ?>
</div>
<div id="tablet" style="display:hidden;margin-top: 14px;">
	Select one or more device (<strong>leave blank: every tablet</strong>)<br/><br/>
	<?php foreach($detect->tabletDevices as $device => $regex): 
		echo '<input type="checkbox" name="tabletdevice[]" id="'.$device.'" value="'.$device.'"';
		if(in_array($device, explode(",",$settings['device_filter'])))
			echo 'checked';
		
		echo '> <label for="'.$device.'">'.$device.'</label><br/>';
	 endforeach; ?>
</div>

<div id="else" style="display:hidden;margin-top: 14px;">
	Select one or more mobile OS (<strong>leave blank: every OS</strong>)<br/><br/>
	<?php foreach($detect->operatingSystems as $os => $regex): 
		echo '<input type="checkbox" name="operatingsystem[]" id="'.$os.'" value="'.$os.'"';
		if(in_array($os, explode(",",$settings['operating_system'])))
			echo 'checked';
		
		echo '> <label for="'.$os.'">'.$os.'</label><br/>';
	 endforeach; ?>
	 <br/><br/>
	 Select one or more mobile browser (<strong>leave blank: every browser</strong>)<br/><br/>
	<?php foreach($detect->userAgents as $user_agent => $regex): 
		echo '<input type="checkbox" name="useragent[]" id="'.$user_agent.'" value="'.$user_agent.'"';
		if(in_array($user_agent, explode(",",$settings['user_agent'])))
			echo 'checked';
		
		echo '> <label for="'.$user_agent.'">'.$user_agent.'</label><br/>';
	 endforeach; ?>

</div>
<h2> Actions for rule <?php echo $rule; ?></h2>
<p>You may specify one action for each rule between the followings:</p>

<input type="radio" name="action_type" value="1" id="redirectrd" onClick="fillActionWrapper(this.value);" <?php if($settings['action_type'] == 1) echo 'checked';?>> <label for="redirectrd"> Redirecting user</label> <br/>
<input type="radio" name="action_type" value="2" id="switchingrd" onClick="fillActionWrapper(this.value);" <?php if($settings['action_type'] == 2) echo 'checked';?>> <label for="switchingrd"> Switching Theme</label> <br/>
<input type="radio" name="action_type" value="3" id="inlinerd" onClick="fillActionWrapper(this.value);" <?php if($settings['action_type'] == 3) echo 'checked';?>> <label for="inlinerd"> Adding some inline js/css/html (will be attached at wp_head)</label> <br/>
<p id="action_wrapper"></p>
	 <br/>
	 <input type="submit" value="Save" />
<input type="hidden" name="rule_name" value="<?php echo $rule; ?>" />
<?php wp_nonce_field('simple_mobile_detector','simple_mobile_detector_nonce'); ?>
</form>
<?php
endif;
?>
