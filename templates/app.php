<div id="leftcontent" class="loading">
	<nav id="grouplist">
	</nav>
	<div id="smstorage-settings">
			<h3 class="settings action text" tabindex="0" role="button" title="<?php p($l->t('Settings')); ?>"></h3>
			<h2 data-id="import" tabindex="0" role="button"><?php p($l->t('Import')); ?></h2>
				<ul>
					<li class="import-upload">
						<input id="import-upload-input" class="tooltipped rightwards" title="<?php p($l->t('Select file...')); ?>" type="file" name="file" />
					</li>
					<li class="import-status">
						<label id="import-status-text">Uploading...</label>
						<div id="import-status-progress"></div>
					</li>
				</ul>
	</div>
</div>
<div id="rightcontent">
</div>


<script id="addressListItemTemplate" type="text/template">
<div class="address" id="address{idAddress}">
	<h3>{name}</h3>
	<div class="addressNum">{count}</div>
	<div class="addressNumber">{address}</div>
</div>
</script>

<script id="messageItemTemplate" type="text/template">
<div class="bubble {direction}">
	<h2>{date}</h2>
	<p>{body}</p>
</div>
</script>
