Feature: Return Type Completion
    As a user
    I want to have return types completed from function or method calls

    @php70
    Scenario: Getting native return type from function call
        Given there is a file with:
        """
        <?php

        function padawan_test(): array {}
        $test = padawan_test();

        """
        When I type "$" on the 5 line
        And I ask for completion
        Then I should get:
            | Name | Signature  |
            | test | array      |

    Scenario: Getting phpdoc return type from function call
        Given there is a file with:
        """
        <?php

        /**
         * @return string[][]
         */
        function padawan_test() {}
        $test = padawan_test();

        """
        When I type "$" on the 8 line
        And I ask for completion
        Then I should get:
            | Name | Signature  |
            | test | string[][] |

    @php70
    Scenario: Getting native return type from class method call
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public function test(): array
            {
                $test = $this->test();

            }
        }
        """
        When I type "$" on the 8 line
        And I ask for completion
        Then I should get:
            | Name | Signature |
            | this | SomeClass |
            | test | array     |

    Scenario: Getting phpdoc return type from class method call
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            /**
             * @return SomeClass[]
             */
            public function test()
            {
                $test = $this->test();

            }
        }
        """
        When I type "$" on the 11 line
        And I ask for completion
        Then I should get:
            | Name | Signature   |
            | this | SomeClass   |
            | test | SomeClass[] |
