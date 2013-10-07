<form id="smstorage">
	<fieldset class="personalblock">
		<strong>SMStorage</strong><br />
		<label for="countryCode">Default country code: </label><select name="countryCode" id="countryCode">
<?php
		require dirname(__DIR__) . '/3rdparty/countrycodes.php';
		foreach ($countryCodes as $code => $country) {
			$pos = strpos('||', $code);		// support different regions with same country code
			if ($pos !== false)
				$codeX = substr($code, 0, $pos);
			else
				$codeX = $code;
?>
			<option value="<?php p($code); ?>"<?php if ($code === $_['code']) p(' selected'); ?>><?php p($country); ?> (<?php p($codeX); ?>)</option>
<?php	} ?>
		</select><span class="msg"></span><br />
		<br />
	</fieldset>
</form>