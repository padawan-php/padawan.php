Feature: Names Completion
    As a user
    I want to have all names(functions, classes, interfaces) when typing a T_STRING
    In order to have access to built-in and project names

    Scenario: Getting all functions with prefix
        Given there is a file with:
        """
        <?php

        function test_1(){}
        function test_2(){}
        function n_test_3(){}
        function other(){}
        """
        When I type "test" on the 7 line
        And ask for completion
        Then I should get:
            | Name |
            | test_1 |
            | test_2 |
            | n_test_3 |
