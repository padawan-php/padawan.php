Feature: Processing constructor assignments
    As a user
    I want to have class properties types based on assignments in constructor
    So that I can not write doc comment to all properties

    Scenario: Getting properties list
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public function __construct(SomeClass $a)
            {
                $this->prop = $a;

            }

            private $prop;
        }
        """
        When I type "$this->" on the 8 line
        And I ask for completion
        Then I should get:
            | Name | Signature |
            | prop | SomeClass |
