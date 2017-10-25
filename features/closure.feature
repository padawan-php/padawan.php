Feature: Completion in closure
    As a user
    I want to have closure's arguments and uses as variables in scope
    So that I can see them when type $

    Scenario: Getting closure argument
        Given there is a file with:
        """
        <?php

        $a = function (Test1 $test1, Test2 $test2) {

        };
        """
        When I type "$" on the 4 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | test1     | Test1     |
            | test2     | Test2     |

    Scenario: Getting closure uses
        Given there is a file with:
        """
        <?php

        $test1 = new Test1();
        $test2 = new Test2();
        $test3 = [1,2,3,4];
        $a = function () use ($test1, $test2, &$test3) {

        };
        """
        When I type "$" on the 7 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |

    Scenario: Getting closure uses
        Given there is a file with:
        """
        <?php

        $test1 = new Test1();
        $test2 = new Test2();
        $test3 = [1,2,3,4];
        $a = function (Argument1 $arg1, Argument2 $arg2) use ($test1, $test2, &$test3) {

        };
        """
        When I type "$" on the 7 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | arg1      | Argument1 |
            | arg2      | Argument2 |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |

    Scenario: Getting closure argument in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $a = function (Test1 $test1, Test2 $test2) {

                };
            }
        }
        """
        When I type "$" on the 6 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | this      | Test1     |
            | test1     | Test1     |
            | test2     | Test2     |

    Scenario: Getting closure uses in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $test1 = new Test1();
                $test2 = new Test2();
                $test3 = [1,2,3,4];
                $a = function (Test1 $test1, Test2 $test2) use ($test1, $test2, &$test3) {

                };
            }
        }
        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | this      | Test1     |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |

    Scenario: Getting closure uses in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $test1 = new Test1();
                $test2 = new Test2();
                $test3 = [1,2,3,4];
                $a = function (Argument1 $arg1, Argument2 $arg2) use ($test1, $test2, &$test3) {

                };
            }
        }
        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | this      | Test1     |
            | arg1      | Argument1 |
            | arg2      | Argument2 |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |

    Scenario: Getting static closure argument in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $a = static function (Test1 $test1, Test2 $test2) {

                };
            }
        }
        """
        When I type "$" on the 6 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | test1     | Test1     |
            | test2     | Test2     |

    Scenario: Getting static closure uses in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $test1 = new Test1();
                $test2 = new Test2();
                $test3 = [1,2,3,4];
                $a = static function (Test1 $test1, Test2 $test2) use ($test1, $test2, &$test3) {

                };
            }
        }
        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |

    Scenario: Getting static closure uses in class method
        Given there is a file with:
        """
        <?php

        class Test1 {
            public function foo() {
                $test1 = new Test1();
                $test2 = new Test2();
                $test3 = [1,2,3,4];
                $a = static function (Argument1 $arg1, Argument2 $arg2) use ($test1, $test2, &$test3) {

                };
            }
        }
        """
        When I type "$" on the 9 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | arg1      | Argument1 |
            | arg2      | Argument2 |
            | test1     | Test1     |
            | test2     | Test2     |
            | test3     | array     |
