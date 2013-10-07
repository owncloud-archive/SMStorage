$(document).ready(function(){
	$('#countryCode').attr('defaultValue', $('#countryCode :selected').val()).blur(function(event){
		if ($(this).val() == $(this).attr('defaultValue')){
			return;
		}
		event.preventDefault();
		$(this).attr('defaultValue', $(this).val());
		OC.msg.startSaving('#smstorage .msg');
		var post = $('#smstorage').serialize();
		$.post(OC.filePath('smstorage', 'ajax', 'settings.php'), post, function(data){
			OC.msg.finishedSaving('#smstorage .msg', data);
		});
	});
});