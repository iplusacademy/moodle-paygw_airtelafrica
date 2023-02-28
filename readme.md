# Airtel  #

This plugin was developed thanks to funding from Medical Access Uganda (https://e-learning.medical-access.org)

The plugin allows a site to connect to Airgel Africa to process payments.

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

## Setup Airtel account ##

To set up access within Moodle you will need to:
* Register a new application (Airtel Africa have their [own docs](https://developers.airtel.africa/developer) on this.)
* Enable the Collection-APIs and in the settings select the countries where you want to accept money from.
* Configure the callback url in your Application settings. The URL is in the format "https://example.com/payment/gateway/airtelafrica/callback.php".
* Make a phone call to your local Airtel representative so your submitted resquest is accepted, yes, somebody at Airtel Africa needs to turn a switch before your changes take effect. Hope for the best, I never made a phone call and I'm already waiting more than 4 months...
* For every change, callback url, enable extra APIs, ... see previous line. 

## Dependencies ##

* Currently this plugin is using the [Amazon's SDK for PHP plugin](https://moodle.org/plugins/local_aws).
* From Moodle_402_STABLE on this will not be necessary any more as Guzzle will be part of Moodle.

## Configure Moodle ##

* Go to site administration / Plugins / Manage payment gateways and enable the Airtel payment gateway.
* Go to site administration / Payments / Payment accounts
* Click the button 'Create payment account' then enter an account name for identifying it when setting up enrolment on payment, then save changes.
* On the Payment accounts page, click the payment gateway link to configure Airtel.
* In the configuration page, 
    * Enter your clientid from the application you have created in the Airtel developer centre
    * Paste your own private key value.

## Add Enrolment on payment. ##

* Go to Go to Site administration > Plugins > Enrolments > Manage enrol plugins and click the eye icon opposite Enrolment on payment.
* Click the settings link, configure as required then click the 'Save changes' button.
* Go to the course you wish to enable payment for, and add the 'Enrolment on payment' enrolment method to the course.
* Select a payment account, amend the enrolment fee as necessary then click the button 'Add method'.

see also:  
[moodledocs: Payment Gateways](https://docs.moodle.org/en/Payment_gateways)  
[moodledocs: Enrolment on Payment](https://docs.moodle.org/en/Enrolment_on_payment)

## License ##

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

