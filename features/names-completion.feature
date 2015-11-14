Feature: Names Completion
    As a user
    I want to have all names(functions, classes, interfaces) when typing a T_STRING
    In order to have access to built-in and project names

    Scenario: Getting all functions with prefix
        Given there is a file with:
        """
        <?php

        function padawan_test_1(){}
        function padawan_test_2(){}
        function n_padawan_test_3(){}
        function padawan_other(){}
        """
        When I type "padawan_test" on the 7 line
        And I ask for completion
        Then I should get:
            | Name |
            | padawan_test_1 |
            | padawan_test_2 |
            | n_padawan_test_3 |

    Scenario: Getting core functions with prefix
        Given there is a file with:
        """
        <?php

        function array_pop_custom(){}
        """
        When I type "array_pop" on the 4 line
        And I ask for completion
        Then I should get:
            | Name |
            | array_pop_custom |
            | array_pop |
