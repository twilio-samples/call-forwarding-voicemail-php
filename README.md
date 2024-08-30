# Call Forwarding with Voicemail in PHP

This is a small PHP app built using [the Slim Framework][slim-framework-url] that shows how to build a call forwarding app that forwards calls during a specific time window, and records voicemails if calls are unanswered or outside that time window.

Find out more on [Twilio Code Exchange][code-exchange-url].

## Application Overview

The application forwards incoming calls to a specified number during business hours; by default, these are Monday to Friday 8:00-18:00 UTC. Otherwise, it directs the call to voicemail. If the call is directed to voicemail, a message can be recorded and a link of the recording sent via SMS to the configured phone number.

## Requirements

To use the application, you'll need the following:

- [PHP](https://www.php.net) 8.3
- [Composer](https://getcomposer.org/) installed globally
- A Twilio account (free or paid) with a phone number. [Click here to create one](http://www.twilio.com/referral/QlBtVJ), if you don't have already.
- [ngrok](https://ngrok.com/)
- Two phone numbers; one to call the service and another to redirect your call to, if it's between business hours.

## Getting Started

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

[slim-framework-url]: https://www.slimframework.com/
[code-exchange-url]: https://www.twilio.com/code-exchange/call-forwarding-voicemail
[twilio-dev-phone-url]: https://www.twilio.com/docs/labs/dev-phone