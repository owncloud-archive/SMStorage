(function($, OC){
	var SMStorage = function() {
		this.Hooks = {'addMessage': [], 'addAddress': [] };
		this.Plugins = {};
	}

	SMStorage.prototype.init = function() {
		this.bindEvents();
		this.loadAddresses();

		if (window.location.hash.length > 3) {
			this.loadMessages(window.location.hash.substr(1));
		}
	};

	SMStorage.prototype.loading = function(obj, state) {
		$(obj).toggleClass('loading', state);
	};

	SMStorage.prototype.bindEvents = function() {
		if (!this.importStatusProgress) {
			this.importStatusProgress = $('#import-status-progress');
		}
		if (!this.importStatusText) {
			this.importStatusText = $('#import-status-text');
		}

		var tnis = this;
		// Should fix Opera check for delayed delete.
		$(window).unload(function (){
			$(window).trigger('beforeunload');
		});

		$(window).bind('hashchange', function() {
			tnis.loadMessages(window.location.hash.substr(1));
		});

		$('#smstorage-settings .settings').bind('click', function() {
			$(this).parent().toggleClass('open');
		});
		$('#import-upload-input').fileupload({
			url: OC.filePath('smstorage', 'ajax', 'import.php'),
            dataType: 'json',
            start: function(e, data) {
                tnis.importStatusProgress.progressbar({value:false});
                $('.import-upload').hide();
                $('.import-status').show();
                tnis.importStatusProgress.fadeIn();
            },
            done: function (e, data) {
                console.log('Upload done:', data.result);
                $('.import-upload').show();
                $('.import-status').hide();
                if (data.result && data.result.status == 'success') {
					alert('Imported ' + data.result.inserted + ' of ' + data.result.total + ' messages.');
				} else {
					alert(data.result.message || 'Nothing returned');
				}
				$(this).parents('#smstorage-settings').removeClass('open');
				if (data.result.inserted > 0) {
					tnis.loadAddresses();
					tnis.loadMessages();
				}
            },
            fail: function(e, data) {
                console.log('fail', data);
                $('.import-upload').show();
                $('.import-status').hide();
            }
        });

	};

	SMStorage.prototype.loadAddresses = function() {
		var tnis = this;
		this.removeAllAddresses();
		this.loading('#leftcontent', true);
		$.getJSON(OC.filePath('smstorage', 'ajax', 'addresses.php'), null, function(data) {
				if (!data || data.status != "success") {
					alert('Error!');
					return;
				}
				if (data.addresses.length == 0) {
					tnis.noAddresses();
				} else {
					for (var i = 0; i < data.addresses.length; i++) {
						tnis.addAddress(data.addresses[i]);
					}
				}
				tnis.loading('#leftcontent', false);
		});
	};

	SMStorage.prototype.addAddress = function(addressData) {
		var tnis = this;
		if (!this.addressTemplate) {
			this.addressTemplate = $('#addressListItemTemplate');
		}
		if (!this.addressNav) {
			this.addressNav = $('#grouplist');
		}

		if (this.Hooks['addAddress']) {
			for (var i = 0; addressData && i < this.Hooks['addAddress'].length; i++) {
				addressData = this.Hooks['addAddress'][i].call(this, addressData);
			}
		}
		
		if (!addressData) {
			return;
		}

		var number = addressData.address;
		var template = this.addressTemplate.octemplate({
				'name' : (addressData.name ? addressData.name : number),
				'idAddress' : number.substr(1),
				'address' : (addressData.name ? number : ''),
				'count' : addressData.count});
		template.on('click', function(){document.location.hash = number});
		this.addressNav.append(template);
	};
	
	SMStorage.prototype.noAddresses = function() {
		if (!this.addressTemplate) {
			this.addressTemplate = $('#addressListItemTemplate');
		}
		if (!this.addressNav) {
			this.addressNav = $('#grouplist');
		}

		var template = this.addressTemplate.octemplate({
				'name' : '',
				'idAddress' : '',
				'address' : '',
				'count' : ''});
		this.addressNav.append(template);
		template = this.addressTemplate.octemplate({
				'name' : 'No addresses yet',
				'idAddress' : '',
				'address' : '',
				'count' : ''});
		this.addressNav.append(template);
	}

	SMStorage.prototype.removeAllAddresses = function() {
		if (!this.addressNav) {
			this.addressNav = $('#grouplist');
		}
		this.addressNav.empty();
	}

	SMStorage.prototype.loadMessages = function(address) {
		if (address == this.currentAddress) {
			return;
		}
		if (!address) {
			if (!this.currentAddress)
				return;
			address = this.currentAddress;
		}
		var tnis = this;
		if (this.currentAddress) {
			this.removeAllMessages();
			$('#address' + this.currentAddress.substr(1)).removeClass('active');
		}
		this.loading('#rightcontent', true);
		$.getJSON(OC.filePath('smstorage', 'ajax', 'messages.php'), {address: address}, function(data) {
				if (!data || data.status != "success") {
					alert('Error!');
					return;
				}
				for (var i = 0; i < data.messages.length; i++) {
					tnis.addMessage(data.messages[i]);
				}
				tnis.currentAddress = address;
				$('#address' + address.substr(1)).addClass('active');
				tnis.loading('#rightcontent', false);
		});
	};

	SMStorage.prototype.removeAllMessages = function() {
		if (!this.rightContent) {
			this.rightContent = $('#rightcontent');
		}
		this.rightContent.empty();
	};

	SMStorage.prototype.addMessage = function(message) {
		if (!this.messageTemplate) {
			this.messageTemplate = $('#messageItemTemplate');
		}
		if (!this.rightContent) {
			this.rightContent = $('#rightcontent');
		}
		if (message.body == '') {		// skip empty messages. Why should they be in the DB anyway?
			return;
		}

		if (this.Hooks['addMessage']) {
			for (var i = 0; message && i < this.Hooks['addMessage'].length; i++) {
				message = this.Hooks['addMessage'][i].call(this, message);
			}
		}
		
		if (!message || message.body == '') {
			return;
		}

		var template = this.messageTemplate.octemplate({
				'direction': (message['type'] == 1 ? 'received' : 'sent'),
				'date': message.date,
				'body': message.body});
		this.rightContent.append(template);
	};

	OC.SMStorage = new SMStorage();
})(jQuery, OC);


$(document).ready(function() {
	OC.SMStorage.init();
});
