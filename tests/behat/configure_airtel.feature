@paygw @paygw_airtelafrica
Feature: Configure Airtel Africa payment gateway

  In order to control student access to courses
  I need to be able to configure an Airtel Africa payment gateway

  Background:
    Given the following "core_payment > payment accounts" exist:
      | name           | gateways     |
      | Account1       | airtelafrica |

  @javascript
  Scenario: Admin can configure Airtel Africa payment plugin
    Given I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Enrolment on payment" "table_row"
    And I navigate to "Payments > Payment accounts" in site administration
    When I click on "Airtel Africa" "link" in the "Account1" "table_row"
    Then I should see "Brand name"
    And I should see "Client ID"
    And I should see "Secret"
    And I should see "Environment"
    And I set the following fields to these values:
      | Brand name        | Test brand |
      | Client ID         | tst client |
      | Secret            | tst secret |
      | Sandbox Client ID | tst secret |
      | Sandbox secret    | tst secret |
      | Environment       | sandbox    |
      | Country           | Uganda     |
    And I press "Save changes"
    And I log out
