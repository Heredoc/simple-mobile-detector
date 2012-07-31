<h2>Simple Mobile Detector Rules</h2>
	<p>You can specify as many rules as you like.</p>
	<table class="wp-list-table widefat fixed">
		<thead>
			<th scope="col">Name</th>
			<th scope="col">Device Type</th>
			<th scope="col">Device Name</th>
			<th scope="col">OS</th>
			<th scope="col">Browser</th>
			<th scope="col">Actions</th>
		</thead>
		<tbody>
			<?php
			if($rulesArray == false): ?>
				<tr><td colspan="5">No rules defined. Add one.</td></tr>
			<?php 
			else:
				if(!is_array($rulesArray))
				$rules = explode(",", $rulesArray);
				else
				$rules = $rulesArray;
				foreach($rules as $rule):
					$info = unserialize(get_option("smd_".$rule));
			?>
				<tr>
					<td><?php echo $rule; ?></td>
					<td><?php echo simple_md_t($info['device_type']); ?></td>
					<td><?php echo (isset($info['device_filter']) ? $info['device_filter'] : 'Not defined'); ?></td>
					<td><?php echo (isset($info['operating_system']) ? $info['operating_system'] : 'Not defined'); ?></td>
					<td><?php echo (isset($info['user_agent']) ? $info['user_agent'] : 'Not defined'); ?></td>
					<td><form style="display:inline;" action="options-general.php"><input type="hidden" name="page" value="simple-mobile-detector"/><input type="hidden" name="rule" value="<?php echo $rule; ?>"/><input type="submit" name="action"  class="button-secondary action" value="Configure"></form>
						<form name="delsimple_mobile_detector_rule_delete" style="display:inline;" method="post"><input type="submit" name="delete_rule"  class="button-secondary action" value="Delete"> <input type="hidden" name="simple_mobile_detector_rule_delete" value="<?php echo $rule; ?>"/>
						<?php wp_nonce_field('simple_mobile_detector','simple_mobile_detector_nonce'); ?></form></td>
				</tr>
			<?php 
				endforeach;
			endif;
			?>
		</tbody>
	</table>
	<h2>Add rule</h2>
		<form name="addsimple_mobile_detector_rule" method="post">
			<label for="simple_mobile_detector_rule_name">Rule name</label>: <input type="text" id="simple_mobile_detector_rule_name" name="simple_mobile_detector_rule_name" placeholder="Rule Name" style="border-color: #000;"/> <input type="submit" name=""  class="button-secondary action" value="Add rule">
			<?php wp_nonce_field('simple_mobile_detector','simple_mobile_detector_nonce'); ?>
		</form>