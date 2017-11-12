Feature: Names Completion
    As a user
    I want to have all names (functions, classes, interfaces, namespaces) when typing a T_STRING
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

    Scenario: Getting all classes with prefix by extends
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
        }

        class SomeOtherClass {}
        """
        When I type " extends SomeO" on the 3 line
        And I ask for completion
        Then I should get:
            | Menu           |
            | SomeOtherClass |

    Scenario: Getting all interfaces with prefix by implements
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
        }

        interface SomeInterface {}
        """
        When I type " implements SomeI" on the 3 line
        And I ask for completion
        Then I should get:
            | Menu          |
            | SomeInterface |

    Scenario: Getting all classes with prefix by new
        Given there is a file with:
        """
        <?php

        class SomeClass {}
        """
        When I type "new Some" on the 4 line
        And I ask for completion
        Then I should get:
            | Menu      |
            | SomeClass |

    Scenario: Getting all namespaces with prefix by namespace
        Given there is a file with:
        """
        <?php

        namespace Test\Test1
        {
            class SomeClass {}
        }

        namespace Test\Test2 {}
        """
        When I type "namespace Test" on the 9 line
        And I ask for completion
        Then I should get:
            | Menu       |
            | Test\Test1 |

    Scenario: Getting all namespaces with prefix by use
        Given there is a file with:
        """
        <?php

        namespace Test\Test1
        {
            class SomeClass {}
        }

        namespace Test\Test2 {}
        """
        When I type "use Test" on the 9 line
        And I ask for completion
        Then I should get:
            | Menu                 |
            | Test\Test1\SomeClass |
