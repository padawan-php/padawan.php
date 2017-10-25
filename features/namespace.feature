Feature: Namespace Completion
    As a user
    I want to have namespaced completions when in namespace

    Scenario: Getting namespaced completions inside of it
        Given there is a file with:
        """
        <?php

        namespace Test\Test1;

        class SomeClass
        {
            /**
             * @return SomeClass[]
             */
            public static function test()
            {
                return [];
            }
        }

        $test = SomeClass::test();

        """
        When I type "$" on the 17 line
        And I ask for completion
        Then I should get:
            | Name | Signature              |
            | test | Test\Test1\SomeClass[] |

    Scenario: Getting namespaced completions out of it
        Given there is a file with:
        """
        <?php

        namespace Test\Test1
        {
            class SomeClass
            {
                /**
                 * @return SomeClass[]
                 */
                public static function test()
                {
                    return [];
                }
            }
        }

        namespace
        {
            use Test\Test1;

            $test = SomeClass::test();

        }
        """
        When I type "$" on the 22 line
        And I ask for completion
        Then I should get:
            | Name | Signature              |
            | test | Test\Test1\SomeClass[] |

    Scenario: Getting namespaced completions from alias
        Given there is a file with:
        """
        <?php

        namespace Test\Test1
        {
            class SomeClass
            {
                public static function test()
                {
                    return [];
                }
            }
        }

        namespace
        {
            use Test\Test1 as Test2;

            /** @var Test2\SomeClass[] */
            $test = [];

        }
        """
        When I type "$" on the 20 line
        And I ask for completion
        Then I should get:
            | Name | Signature              |
            | test | Test\Test1\SomeClass[] |

    Scenario: Getting unqualified-namespaced completions
        Given there is a file with:
        """
        <?php

        namespace Test\Test1
        {
            class SomeClass
            {
                public static function test()
                {
                    return [];
                }
            }
        }

        namespace
        {
            use Test as Test2;

            /** @var Test2\Test1\SomeClass[] */
            $test = [];

        }
        """
        When I type "$" on the 20 line
        And I ask for completion
        Then I should get:
            | Name | Signature              |
            | test | Test\Test1\SomeClass[] |
