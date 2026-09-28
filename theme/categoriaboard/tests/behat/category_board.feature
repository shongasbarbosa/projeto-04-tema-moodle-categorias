@theme @theme_categoriaboard
Feature: Category board
  In order to find my courses quickly
  As a student
  I need to see my enrolled courses grouped by category, with my progress

  Background:
    Given the following "categories" exist:
      | name         | category | idnumber |
      | Programação  | 0        | PROG     |
      | Design       | 0        | DESIGN   |
    And the following "courses" exist:
      | fullname       | shortname | category | enablecompletion |
      | PHP para EaD    | C1        | PROG     | 1                |
      | UI para EaD     | C2        | DESIGN   | 1                |
    And the following "users" exist:
      | username   | firstname | lastname |
      | student1   | Ana       | Aluna    |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | student1 | C2     | student |
    And the following config values are set as admin:
      | config          | value | plugin              |
      | enablegrouping  | 1     | theme_categoriaboard |

  @javascript
  Scenario: Student sees courses grouped by category
    Given I log in as "student1"
    When I follow "Meus cursos por categoria"
    Then I should see "Design"
    And I should see "Programação"
    And I should see "PHP para EaD"
    And I should see "UI para EaD"
