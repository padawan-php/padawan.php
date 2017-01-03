Feature: Function Completion
    As a user
    I want to have all functions when using functions' return value in argument list

    Scenario: Getting all global functions with prefix
        Given there is a file with:
        """
        <?php

        function padawan_test_1(){}
        function padawan_test_2(){}
        function n_padawan_test_3(){}
        function padawan_other(){}
        """
        When I type "$a = new DateTime(padawan_test" on the 7 line
        And I ask for completion
        Then I should get:
            | Menu |
            | padawan_test_1 |
            | padawan_test_2 |

    Scenario: Getting core functions with prefix
        Given there is a file with:
        """
        <?php

        function array_pop_custom(){}
        """
        When I type "$a = new DateTime(array_pop" on the 4 line
        And I ask for completion
        Then I should get:
            | Menu |
            | array_pop_custom |
            | array_pop |
