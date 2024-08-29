# Call Forwarding with Voicemail in PHP

This is a small PHP app built using [the Slim Framework][slim-framework-url] that shows how to build a call forwarding app that forwards calls during a specific time window, and records voicemails if calls are unanswered or outside that time window.

Find out more on [Twilio Code Exchange][code-exchange-url].

## Application Overview

The application forwards incoming calls to a specified number during business hours; by default, these are Monday to Friday 8:00-18:00 UTC. Otherwise, it directs the call to voicemail. If the call is directed to voicemail, a message can be recorded and a link of the recording sent via SMS to the configured phone number.

## Requirements

To use the application, you'll need the following:

- PHP 8.3
- Composer installed globally
- A Twilio account (free or paid) with a phone number
- ngrok
- Two phone numbers; one to call the service and another to redirect your call to, if it's between business hours.

## Getting Started

After cloning the code to wherever you store your PHP projects, change into the project directory.
Then, copy _.env.example_ as _.env_, by running the following command:

```bash
cp -v .env.example .env
```

After that, set values for `MY_PHONE_NUMBER`, `TWILIO_ACCOUNT_SID`, `TWILIO_AUTH_TOKEN`, `TWILIO_PHONE_NUMBER`, and as many of the configuration details as possible.
Finally, run the following command to launch the application:

```php
composer serve
```

[slim-framework-url]: https://www.slimframework.com/
[code-exchange-url]: https://www.twilio.com/code-exchange/call-forwarding-voicemail
[twilio-dev-phone-url]: https://www.twilio.com/docs/labs/dev-phone