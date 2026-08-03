## Stripe setup 

1. Run `composer install`.
2. Copy `stripe_config.example.php` to `stripe_config.php`.
3. Add Stripe sandbox keys and Price IDs.
4. Start the local webhook listener:

   stripe listen --events checkout.session.completed --forward-to http://localhost/Fitness-Buddy-master/stripe_webhook.php

5. Copy the listener's `whsec_...` value into `stripe_config.php`.
