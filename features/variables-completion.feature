Feature: Variables Completion
    As a user
    I want to have all variables completed when typing a T_VARIABLE

    Scenario: Getting all variables from the result of cast
        Given there is a file with:
        """
        <?php

        $test1 = (array)0;
        $test2 = (bool)0;
        $test3 = (double)0;
        $test4 = (int)0;
        $test5 = (object)0;
        $test6 = (string)0;

        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 | array     |
            | test2 | bool      |
            | test3 | float     |
            | test4 | int       |
            | test5 | object    |
            | test6 | string    |

    Scenario: Getting all variables from the result of literal values
        Given there is a file with:
        """
        <?php

        $test1 = array();
        $test2 = [];
        $test3 = true;
        $test4 = false;
        $test5 = 0.0;
        $test6 = 0;
        $test7 = '';
        $test8 = "{$test7}";
        $test9 = `ls -a`;

        """
        When I type "$" on the 12 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 | array     |
            | test2 | array     |
            | test3 | bool      |
            | test4 | bool      |
            | test5 | float     |
            | test6 | int       |
            | test7 | string    |
            | test8 | string    |
            | test9 | string    |

    Scenario: Getting all variables from the result of operators
        Given there is a file with:
        """
        <?php

        $test1 = !!0;
        $test2 = isset($test1);
        $test3 = empty($test1);
        $test4 = print '';

        """
        When I type "$" on the 7 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 | bool      |
            | test2 | bool      |
            | test3 | bool      |
            | test4 | int       |

    Scenario: Getting all variables from the result of magic constants
        Given there is a file with:
        """
        <?php

        $test1 = __LINE__;
        $test2 = __FILE__;
        $test3 = __DIR__;
        $test4 = __FUNCTION__;
        $test5 = __CLASS__;
        $test6 = __TRAIT__;
        $test7 = __METHOD__;
        $test8 = __NAMESPACE__;

        """
        When I type "$" on the 11 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 | int       |
            | test2 | string    |
            | test3 | string    |
            | test4 | string    |
            | test5 | string    |
            | test6 | string    |
            | test7 | string    |
            | test8 | string    |

    Scenario: Getting all variables from the language structures
        Given there is a file with:
        """
        <?php

        $test1;
        $test2 = new DateTime();
        $test3 = clone $test2;
        $test4 = $test2;
        $test5 = $test3;
        unset($test4, $test5);

        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 |           |
            | test2 | DateTime  |
            | test3 | DateTime  |

    Scenario: Getting global variables inside a function
        Given there is a file with:
        """
        <?php

        $test = [];

        function padawan_test() {

        }
        """
        When I type "global $" on the 6 line
        And I ask for completion
        Then I should get:
            | Name | Signature |
            | test | array     |

    Scenario: Getting global/static variables inside a function
        Given there is a file with:
        """
        <?php

        $test1 = [];
        $test2 = [];
        $test3 = [];

        function padawan_test() {
            global $test1, $test2;
            static $test4 = 0;

        }
        """
        When I type "$" on the 10 line
        And I ask for completion
        Then I should get:
            | Name  | Signature |
            | test1 | array     |
            | test2 | array     |
            | test4 | int       |

    Scenario: Getting a variable inside a list expression
        Given there is a file with:
        """
        <?php

        /** @var string[] */
        $test1 = [];

        list($test2, $test3) = $test1;
        [$test4, $test5] = $test1;

        /** @var string[][] */
        $test6 = [];

        list($test7, , list($test8)) = $test6;
        [$test9, , [$test10]] = $test6;

        """
        When I type "$" on the 14 line
        And I ask for completion
        Then I should get:
            | Name   | Signature  |
            | test1  | string[]   |
            | test2  | string     |
            | test3  | string     |
            | test4  | string     |
            | test5  | string     |
            | test6  | string[][] |
            | test7  | string[]   |
            | test8  | string     |
            | test9  | string[]   |
            | test10 | string     |

    Scenario: Getting a variable inside a foreach statement
        Given there is a file with:
        """
        <?php

        /** @var string[][] */
        $test1 = [];

        foreach ($test1 as $test2) {
            foreach ($test2 as list($test3, $test4)) {

            }
        }
        """
        When I type "$" on the 8 line
        And I ask for completion
        Then I should get:
            | Name  | Signature  |
            | test1 | string[][] |
            | test2 | string[]   |
            | test3 | string     |
            | test4 | string     |

    Scenario: Getting a variable inside a catch statement
        Given there is a file with:
        """
        <?php

        try {
            throw new RuntimeException();
        } catch (LogicException $e) {
        } catch (RuntimeException $e) {

        }
        """
        When I type "$" on the 7 line
        And I ask for completion
        Then I should get:
            | Name | Signature        |
            | e    | RuntimeException |
