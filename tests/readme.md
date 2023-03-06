# Payment gateway Airtel Africa testing #

This plugin can be tested using PHPUnit and Behat. However, a lot of the standard tests do work with mock answers.

It is possible to test with real data, just add some environment variables before you do some tests:

    env phone=750000035
    env login=13300000-aaaa-bbbb-cccc-000000000f93
    env secret=61e00000-dddd-eeee-ffff-000000000157
    
    moodle-plugin-ci phpunit --coverage-text --coverage-clover payment/gateway/airtelafrica
    moodle-plugin-ci behat --coverage-text --coverage-clover payment/gateway/airtelafrica

You can also use repository secrets in GitHub actions (when using a private repository):

    gh secret set phone --body "750000035"
    gh secret set login --body "13300000-aaaa-bbbb-cccc-000000000f93"
    gh secret set secret --body "61e00000-dddd-eeee-ffff-000000000157"

Of course, you will need to provide your own number, login and secret.
