# Call Forwarding with Voicemail in PHP

This is a small PHP app built using [the Slim Framework][slim-framework-url] that shows how to build a call forwarding app that forwards calls during a specific time window, and records voicemails if calls are unanswered or outside that time window.

Find out more on [Twilio Code Exchange][code-exchange-url].

## Application Overview

The application forwards incoming calls to a specified number during business hours; by default, these are Monday to Friday 8:00-18:00 UTC. Otherwise, it directs the call to voicemail. If the call is directed to voicemail, a message can be recorded and a link of the recording sent via SMS to the configured phone number.

[slim-framework-url]: https://www.slimframework.com/
[code-exchange-url]: https://www.twilio.com/code-exchange/call-forwarding-voicemail