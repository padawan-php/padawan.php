Feature: Parameters Completion
    As a user
    I want to have all parameters completed when typing a T_VARIABLE

    Scenario: Getting native parameter list from function
        Given there is a file with:
        """
        <?php

        function padawan_test($test1, string $test2, DateTime ...$test3)
        {

        }
        """
        When I type "$" on the 5 line
        And I ask for completion
        Then I should get:
            | Name  | Signature  |
            | test1 |            |
            | test2 | string     |
            | test3 | DateTime[] |

    Scenario: Getting phpdoc parameter list from function
        Given there is a file with:
        """
        <?php

        /**
         * @param resource $test1
         * @param string $test2
         * @param DateTime[] $test3
         */
        function padawan_test($test1, $test2, ...$test3)
        {

        }
        """
        When I type "$" on the 10 line
        And I ask for completion
        Then I should get:
            | Name  | Signature  |
            | test1 | resource   |
            | test2 | string     |
            | test3 | DateTime[] |

    Scenario: Getting native parameter list from class method
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public function test(SomeClass $test1, array $test2)
            {

            }
        }
        """
        When I type "$" on the 7 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | this  | SomeClass |
            | test1 | SomeClass |
            | test2 | array     |

    Scenario: Getting phpdoc parameter list from class method
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            /**
             * @param SomeClass $test1
             * @param SomeClass[][] $test2
             */
            public function test(SomeClass $test1, $test2)
            {

            }
        }
        """
        When I type "$" on the 11 line
        And I ask for completion
        Then I should get:
            | Name  | Signature     |
            | this  | SomeClass     |
            | test1 | SomeClass     |
            | test2 | SomeClass[][] |
