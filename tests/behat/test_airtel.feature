@paygw @paygw_airtelafrica @secrets
Feature: Airtel Africa payment gateway test

  In order to control student access to courses
  I need to be able to add an Airtel Africa payment gateway

  Background:
    Given the following "users" exist:
      | username | phone2    | country |
      | student1 | 789012994 | UG      |
      | student2 | 666666666 | UG      |
      | manager1 | 789013004 | UG      |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "activities" exist:
      | activity | name      | course | idnumber |
      | page     | TestPage1 | C1     | page1    |
      | page     | TestPage2 | C2     | page2    |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | manager1 | C1     | manager |
      | manager1 | C2     | manager |
    And the following "core_payment > payment accounts" exist:
      | name           | gateways     |
      | Account1       | airtelafrica |
    And I log in as "admin"
    And I configure airtel
    And I add "Enrolment on payment" enrolment method in "Course 1" with:
      | Payment account | Account1         |
      | Enrolment fee   | 5000             |
      | Currency        | Ugandan Shilling |
    And I add "Enrolment on payment" enrolment method in "Course 2" with:
      | Payment account | Account1         |
      | Enrolment fee   | 5000             |
      | Currency        | Ugandan Shilling |
    And I log out

  @javascript
  Scenario: Student can cancel a Airtel Africa payment
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
    And I click on "Cancel" "button" in the "Select payment type" "dialogue"
    Then I should see "This course requires a payment for entry."

  @javascript
  Scenario: Student can see the Airtel Africa payment prompt
    When I log in as "student1"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I should see "5,000"
    And I press "Select payment type"
    And I should see "Airtel Africa" in the "Select payment type" "dialogue"
    And I should see "5,000"
    And I wait until the page is ready
    And I click on "Proceed" "button" in the "Select payment type" "dialogue"
    And I wait until the page is ready
    And I should see "789012"
    And I should see "profile page"
    And I click on "Proceed" "button" in the "Airtel Africa" "dialogue"
    # And I wait until the page is ready

  @javascript
  Scenario: Student is enrolled in course after an Airtel Africa payment
    When I log in as "student2"
    And I am on course index
    And I follow "Course 2"
    Then I should see "This course requires a payment for entry."
    And I should see "5,000"
    When I press "Select payment type"
    And I should see "Airtel Africa" in the "Select payment type" "dialogue"
    And I should see "5,000"
    And I click on "Proceed" "button" in the "Select payment type" "dialogue"
    And I should see "6666666"
    And I should see "profile page"
    And I click on "Proceed" "button" in the "Airtel Africa" "dialogue"
    And I should see "succeeded"
    And I click on "Proceed" "button" in the "Airtel Africa" "dialogue"
    And I wait until the page is ready
    # We are in.
    Then I should see "Course 2"
    And I should see "TestPage"

  Scenario: Guest can see the login prompt on the Airtel course enrolment page with round price
    When I log in as "guest"
    And I am on course index
    And I follow "Course 1"
    Then I should see "This course requires a payment for entry."
    And I should see "5,000"
    And I should see "Log in to the site"

  Scenario: Guest can see the login prompt on the Airtel course enrolment page
    When I log in as "guest"
    And I am on course index
    And I follow "Course 2"
    Then I should see "This course requires a payment for entry."
    And I should see "5,000"
    And I should see "Log in to the site"
