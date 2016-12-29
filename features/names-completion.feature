Feature: Names Completion
    As a user
    I want to have all names(functions, classes, interfaces) when typing a T_STRING
    In order to have access to built-in and project names

    Scenario: Getting all global functions with prefix
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
            | Menu |
            | padawan_test_1 |
            | padawan_test_2 |

    Scenario: Getting core functions with prefix
        Given there is a file with:
        """
        <?php

        function array_pop_custom(){}
        """
        When I type "array_pop" on the 4 line
        And I ask for completion
        Then I should get:
            | Menu |
            | array_pop_custom |
            | array_pop |

    Scenario: Getting core functions with prefix after assignment
        Given there is a file with:
        """
        <?php

        function array_pop_custom(){}
        """
        When I type "$someVar = array_pop" on the 4 line
        And I ask for completion
        Then I should get:
            | Menu |
            | array_pop_custom |
            | array_pop |

    Scenario: Getting core classes with prefix
        Given there is a file with:
        """
        <?php

        class DateTimeImmutableMine {
        }
        """
        When I type "DateTimeImm" on the 5 line
        And I ask for completion
        Then I should get:
            | Menu                  |
            | DateTimeImmutableMine |
            | DateTimeImmutable     |

    Scenario: FQCN completion
        Given there is a file with:
        """
        <?php
        namespace Padawan\Feature;

        class One {
        }

        class Two{
            public function test() {


            }
        }
        """
        When I type "$a = \Padawan\" on the 9 line
        And I ask for completion
        Then I should get:
            | Menu                |
            | Padawan\Feature\One |
            | Padawan\Feature\Two |

