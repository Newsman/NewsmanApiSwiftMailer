# NewsmanApiSwiftMailer

This is the fastest way to send mails via Newsman secure http API by just chaning the SwiftMailer Transport.

```php
require_once("lib/swift_required.php");

$transport = Swift_Transport_NewsmanApiTransport::newInstance("USER_ID", "API_KEY");
$swift = Swift_Mailer::newInstance($transport);
```

All emails are se via a JSON POST request sent to: [message.send_raw](https://kb.newsmansmtp.com/api/1.0/message.send_raw) API method.
