SMStorage
=========

This is an owncloud app to store messages. Currently it only supports backup files from "SMS Backup and Restore" but with your help I will be able to support more formats.

Maintainer
----------
- [Jonathan Stump](https://github.com/Jonny007-MKD)

Remote
------
####Upload data:
POST the file with id 'file' to<br />
https:/ /oc-server.tld/remote.php/smstorage/**%username%**/**upload**/<br />
Optionally you can pass the type of this file to increase the performance as 'type'
- SMSBackupAndRestore

####Download data:
https:/ /oc-server.tld/remote.php/smstorage/**%username%**/**download**/?*min*=100&*max*=900&*minConv*=20&*date*=1234567890<br />
This will return a *SMS Backup and Restore* XML file with following parameters:
- **minConv**: Minimum number of messages per conversation (so you will not have only the messages of one person)
- **min**: Minimum number of messages that will be queried (if possible, we will not generate new messages ;) )
- **max**: Maximum number of messages, has absolute priority
- **date**: Minimum unix timestamp of the messages

Hooks
-----
namespace: OCA\SMStorage
####index.php

```
pre_index_output
	['tmpl' => &$tmpl]
```

####ajax/messages.php

```
pre_ajax_parseMessages
	['messages' => &$messages]
					$messages is an array of *Messages*
```

```
post_ajax_parseMessages
	['messages' => &$data]
					$data is the array with the JSON data with following keys:
						'body', 'type', 'date' => string with datetime
```

####ajax/addresses.php

```
pre_ajax_parseAddresses
	['addresses' => &$addresses]
					 $addresses is an array of strings of telephone numbers
```

```
post_ajax_parseAddresses
	['addresses' => &$data]
					 $data is the JSON data with following keys:
						'address' => number, 'count' => message count, 'name' => contact name
```

####appinfo/remote.php

```
pre_xml_outputMessages
	['messages' => &$messages]
					$messages is an array of Messages
```

####lib/message.php
`$this` will always be the current instance of *Message*

```
pre_insertMessage
	['run' => &$run, 'message' => &$this]
			   $run = false will abort the insertion of this Message
```

```
post_insertMessage
	['message' => &$this, 'inserted']
```

```
pre_exportMessage
	['run' => &$run, 'xml' => &$xml, 'message' => &$this
			   $run = false will skip this Message
							   $xml is the complete SimpleXMLElement
	Hook before this Message will be added to the output $xml
```

```
post_exportMessage
	['message' => &$this, 'child' => &$xmlChild, 'xml' => &$xml
									  $xmlChild is the SimpleXMLElement instance of the newly added *Message*
```

####js/app.js
Add a method to these arrays to hook it into the methods.

```
OC.SMStorage.Hooks['addAddress']
	First and only parameter is the JSON data of the address:
		['address' => number, 'count' => message count, 'name' => contact name]
	Return `false` to remove the address from the screen or return the (modfied) address parameter
```

```
OC.SMStorage.Hooks['addMessage']
	First and only parameter is the JSON data of a single message:
		['body', 'type', 'date' => string with datetime]
	Return `false` to remove the message from the screen or return the (modified) message parameter
```