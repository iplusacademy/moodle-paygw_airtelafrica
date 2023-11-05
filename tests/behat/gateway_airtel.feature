@paygw @paygw_airtelafrica
Feature: Airtel Africa payment gateway

  In order to control student access to courses
  I need to be able to add an Airtel Africa payment gateway

  Background:
    Given the following "users" exist:
      | username | phone    | country |
      | student1 | 78901299 | UG      |
      | student2 | 66666666 | UG      |
      | manager1 | 78901300 | UG      |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "activities" exist:
      | activity | name     | course | idnumber |
      | page     | TestPage | C1     | page1    |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | manager1 | C1     | manager |
      | manager1 | C2     | manager |
    And the following "core_payment > payment accounts" exist:
      | name           | gateways     |
      | Account1       | airtelafrica |
    And I log in as "admin"
    And I navigate to "Plugins > Enrolments > Manage enrol plugins" in site administration
    And I click on "Enable" "link" in the "Enrolment on payment" "table_row"
    And I navigate to "Payments > Payment accounts" in site administration
    And I click on "Airtel Africa" "link" in the "Account1" "table_row"
    Then I should see "Brand name"
    And I should see "Client ID"
    And I should see "Secret"
    And I should see "Environment"
    And I set the following fields to these values:
      | Brand name         | Test brand  |
      | Client ID          | Test_id     |
      | Secret             | Test_Secret |
      | Sandbox Client ID  | Test_id     |
      | Sandbox secret     | Test_Secret |
      | Environment        | Sandbox     |
      | Country            | Uganda      |
    And I press "Save changes"
    And I log out
    And I log in as "manager1"
    And I am on the "Course 1" "enrolment methods" page
    And I select "Enrolment on payment" from the "Add method" singleselect
    And I set the following fields to these values:
      | Payment account | Account1        |
      | Enrolment fee   | 123.45          |
      | Currency        | Ugandan Shilling |
    And I press "Add method"
    And I am on the "Course 2" "enrolment methods" page
    And I select "Enrolment on payment" from the "Add method" singleselect
    And I set the following fields to these values:
      | Payment account | Account1         |
      | Enrolment fee   | 123.45           |
      | Currency        | Congolese Franc |
    And I press "Add method"
    And I log out

  @javascript
  Scenario: Student can cancel Airtel Africa payment
    When I log in as "student1"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I press "Select payment type"
    And I should see "Airtel Africa" in the "Select payment type" "dialogue"
    And I click on "Proceed" "button" in the "Select payment type" "dialogue"
    And I should see "profile page"
    And I click on "Cancel" "button" in the "Airtel Africa" "dialogue"
    Then I should see "Airtel Africa" in the "Select payment type" "dialogue"

  @javascript
  Scenario: Student can see the Airtel Africa payment prompt on the course enrolment page
    When I log in as "student1"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I should see "123"
    And I press "Select payment type"
    And I should see "Airtel Africa" in the "Select payment type" "dialogue"
    And I should see "123"
    And I click on "Proceed" "button" in the "Select payment type" "dialogue"
    And I should see "789012"
    And I should see "profile page"
    And I click on "Proceed" "button" in the "Airtel Africa" "dialogue"
    And I click on "Cancel" "button" in the "Airtel Africa" "dialogue"

  @javascript
  Scenario: Student should be logged in automatically after an Airtel Africa payment
    When I log in as "student2"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I should see "123"
    And I press "Select payment type"
    And I should see "Airtel Africa" in the "Select payment type" "dialogue"
    And I should see "123"
    And I click on "Proceed" "button" in the "Select payment type" "dialogue"
    And I should see "66666666"
    And I should see "profile page"
    And I click on "Proceed" "button" in the "Airtel Africa" "dialogue"
    And I should see "FAILED"

  Scenario: Guest can see the login prompt on the Airtel Africa course enrolment page with round price
    When I log in as "guest"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I should see "123"
    And I should see "Log in to the site"

  Scenario: Guest can see the login prompt on the Airtel Africa course enrolment page
    When I log in as "guest"
    And I am on course index
    And I follow "Course 2"
    Then I should see "This course requires a payment for entry."
    And I should see "123"
    And I should see "Log in to the site"
