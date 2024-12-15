# Call Forwarding with Voicemail in PHP

<!-- markdownlint-disable MD013 -->
This is a small PHP app built using [the Slim Framework][slim-framework-url] that shows how to build a call forwarding app that forwards calls during a specific time window, and records voicemails if calls are unanswered or outside that time window.

Find out more on [Twilio Code Exchange][code-exchange-url].

## Application Overview

The application forwards incoming calls to a specified number during business hours; by default, these are Monday to Friday 8:00-18:00 UTC. Otherwise, it directs the call to voicemail. If the call is directed to voicemail, a message can be recorded and a link of the recording sent via SMS to the configured phone number.

## Prerequisites

To use the application, you'll need the following:

- [PHP][php-docs-url] 8.3
- [Composer][composer-url] installed globally
- A Twilio account (free or paid) with a phone number. [Click here to create one][twilio-referral-url], if you don't have already.
- [ngrok][ngrok-url]
- Two phone numbers; one to call the service and another to redirect your call to, if it's between business hours.

## ⚡️ Quick Start

After cloning the code to wherever you store your PHP projects, and change into the project directory.
Then, copy _.env.example_ as _.env_, by running the following command:

```bash
cp -v .env.example .env
```

After that, set values for `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_PHONE_NUMBER`.
You can retrieve these details from the **Account Info** panel of your [Twilio Console](https://console.twilio.com/) dashboard.

![A screenshot of the Account Info panel in the Twilio Console dashboard. It shows three fields: Account SID, Auth Token, and "My Twilio phone number", where Account SID and "My Twilio phone number" are redacted.](docs/images/twilio-console-account-info-panel.png)

Then, set `MY_PHONE_NUMBER` to the phone number that you want to receive SMS notifications to.
Ideally, also set as many of the commented out configuration details as possible.

When that's done, run the following command to launch the application:

```php
composer serve
```

Then, use ngrok to create a secure tunnel between port 8080 on your local development machine and the public internet, making the application publicly accessible, by running the following command.

```php
ngrok http 8080
```

With the application ready to go, make a call to your Twilio phone number.

## Contributing

If you want to contribute to the project, whether you have found issues with it or just want to improve it, here's how:

- [Issues][issues_url]: ask questions and submit your feature requests, bug reports, etc
- [Pull requests][pull_requests_url]: send your improvements

## Did You Find The Project Useful?

If the project was useful, and you want to say thank you and/or support its active development, here's how:

- Add a GitHub Star to the project
- Write an interesting article about the project wherever you blog

## License

[MIT][mit-license-url]

## Disclaimer

No warranty expressed or implied. Software is as is.

[issues_url]: https://github.com/settermjd/call-forwarding-voicemail-php/issues
[pull_requests_url]: https://github.com/settermjd/call-forwarding-voicemail-php/pulls
[slim-framework-url]: https://www.slimframework.com/
[code-exchange-url]: https://www.twilio.com/code-exchange/call-forwarding-voicemail
[ngrok-url]: https://ngrok.com/
[mit-license-url]: http://www.opensource.org/licenses/mit-license.html
[twilio-referral-url]: https://login.twilio.com/u/signup?state=hKFo2SA5Qlp2bThzaGh4T0RnUDJMU0c4VWxhZ0lYRUZrQlMxMqFur3VuaXZlcnNhbC1sb2dpbqN0aWTZIDVKUmh0dFM4ZTV0cmt2QkdKeVp6R212Z2JiMlE2U0R6o2NpZNkgTW05M1lTTDVSclpmNzdobUlKZFI3QktZYjZPOXV1cks
[php-docs-url]: https://www.php.net
[composer-url]: https://getcomposer.org/
<!-- markdownlint-enable -->
