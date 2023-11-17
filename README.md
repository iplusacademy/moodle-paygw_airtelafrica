# Payment gateway Airtel Africa #

This plugin was developed thanks to funding from [Medical Access Uganda](https://e-learning.medical-access.org) and [i+academy](https://iplusacademy.org)

The plugin allows a site to connect to Airtel Africa to process payments.

Currently this plugin supports payment on following currencies:

| Country | Country Code | Currency | Currency Code | 
| :---- | :----: | :---- | :----: | 
| Uganda | UG | Ugandan shilling | UGX | 
| Nigeria | NG | Nigerian naira | NGN | 
| Tanzania | TZ | Tanzanian shilling | TZS | 
| Kenya | KE | Kenyan shilling | KES | 
| Rwanda | RW | Rwandan franc | RWF | 
| Zambia | ZM | Zambian kwacha | ZMW | 
| Gabon | GA | CFA franc BEAC | CFA | 
| Niger | NE | CFA franc BCEAO | XOF | 
| Congo-Brazzaville | CG | CFA franc BCEA | XAF | 
| DR Congo | CD | Congolese franc | CDF | 
| DR Congo | CD | United States dollar | USD | 
| Chad | TD | CFA franc BEAC | XAF | 
| Seychelles | SC | Seychelles rupee | SCR | 
| Madagascar | MG | Malagasy ariary | MGA | 
| Malawi | MW | Malawian kwacha | MWK | 


## Setup Airtel Africa account

To set up access within Moodle you will need to:

- Register a new application:  Airtel Africa have their [own docs](https://developers.airtel.africa/developer) on this.
- Enable the Collection-APIs and in the settings select the countries where you want to accept money from.
- Configure the callback url in your Application settings. The URL is in the format "example.com/payment/gateway/airtelafrica/callback.php". Make a phone call to your local Airtel representative so your submitted request is accepted. Yes, somebody at Airtel Africa needs to turn a switch before your changes take effect. Hope for the best. I never made my phone call, my dashboard tells me the APIs are enabled, but after more than 5 months none of the APIs work ...
- For every change, callback url, going live, enabling extra APIs, ... see previous line.
- If you want to test this plugin in the sandbox environment, contact your local Airtel representative. He/she will *manually* confirm your test payment.
- Do __NOT__ rely on the sandbox environment, sometimes this service just stops working. If you are lucky, everything returns to normal after a week

## Requirements

- This plugin requires Moodle 4.2.0+

## Configure Moodle

- Go to site administration / Plugins / Manage payment gateways and enable the Airtel Africa payment gateway.
- Go to site administration / Payments / Payment accounts
- Click the button 'Create payment account' then enter an account name for identifying it when setting up enrolment on payment, then save changes.
- On the Payment accounts page, click the payment gateway link to configure Airtel Africa.
- In the configuration page, 
    - Enter your clientid from the application you have created in the Airtel developer centre
    - Paste your own private key value.

## Add Enrolment on payment

- Go to Go to Site administration > Plugins > Enrolments > Manage enrol plugins and click the eye icon opposite Enrolment on payment.
- Click the settings link, configure as required then click the 'Save changes' button.
- Go to the course you wish to enable payment for, and add the 'Enrolment on payment' enrolment method to the course.
- Select a payment account, amend the enrolment fee as necessary then click the button 'Add method'.

see also:  
[moodledocs: Payment gateways](https://docs.moodle.org/en/Payment_gateways)  
[moodledocs: Enrolment on payment](https://docs.moodle.org/en/Enrolment_on_payment)

## Theme support

This plugin is developed and tested on Moodle Core's Boost theme and Boost child themes, including Moodle Core's Classic theme.

## Database support

This plugin is developed and tested using

* MYSQL
* MariaDB
* PostgreSQL

## Testing

This plugin can be tested in PHPUnit and Behat, but you need to add your phone - login - secret key as an environment variable.

* env phone=???? login=???? secret=???? vendor/bin/phpunit --coverage-text payment/gateway/airtelafrica/
* env phone=???? login=???? secret=???? vendor/bin/behat --tags='paygw_airtelafrica'

Or you can use secrets in Github actions:

* gh secret set phone -b"?????"
* gh secret set login -b"?????"
* gh secret set secret -b"?????"

## Plugin repositories

This plugin will be published and regularly updated on [Github](https://github.com/iplusacademy/moodle-paygw_airtelafrica)

## Challenges

The main problem is that we can't use a reliable callback. The transition from sandbox to production environment in Moodle is immediate,
the transition from sandbox to production environment in Airtel Africa can take days. So a sandbox callback can end up in a production
environment and vice versa. We addressed this issue by:

- using the callback only as a backup tool, in case a successful payment was missed.
- pinging the server for three minutes to see if the payment was successful.

## Bug and problem reports / Support requests

This plugin is carefully developed and only thoroughly tested in Uganda, but bugs and problems can always appear.
Please report bugs and problems on [Github](https://github.com/iplusacademy/moodle-paygw_airtelafrica/issues)
We will do our best to solve your problems, but please note that we can't provide per-case support.
Please contact you Airtel Africa representative in case you get invalid transactionids or timeouts.

## Feature proposals

- Please issue feature proposals on [Github](https://github.com/iplusacademy/moodle-paygw_airtelafrica/issues)
- Please create pull requests on [Github](https://github.com/iplusacademy/moodle-paygw_airtelafrica/pulls)
- We are always interested to read about your feature proposals or even get a pull request from you, but please accept that we can handle your issues only as feature proposals and not as feature requests.

## Status

[![Build Status](https://github.com/iplusacademy/moodle-paygw_airtelafrica/actions/workflows/main.yml/badge.svg)](https://github.com/iplusacademy/moodle-paygw_airtelafrica/actions)

## License

2023 Medical Access Uganda

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program.  If not, see <https://www.gnu.org/licenses/>.
