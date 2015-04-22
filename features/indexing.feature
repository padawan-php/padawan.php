Feature: Index generating
    In order to have fast completion
    As a user
    I need to be able manually start index generating

    Scenario: Indexing small composer project
        Given I have composer project
        And I have PSR root in "src"
        And I have interface "Some\ISpec" with two methods
        And I have class "Some\Spec" that implements "Some\ISpec"
        And I have class "Another\Spec" that implements "Some\ISpec"
        When I generate index for this project
        Then I should get valid index
