Feature: Processing phpdoc
    As a user
    I want to have class property type hints from phpdoc
    for magic properties

    Scenario: Get properties list from classdoc
        Given there is a file with:
        """
        <?php

        /**
         * @property SomeClass $stdProp
         * @property-read string $roProp
         * @property-write string $woProp
         * @property bool $boolProp
         * @property int $intProp
         * @property int|bool|null $mixedProp
         */
        class SomeClass
        {
            public function __construct(SomeClass $a)
            {
                $this->prop = $a;

            }

            private $prop;
        }
        """
        When I type "$this->" on the 16 line
        And I ask for completion
        Then I should get:
            | Name      | Signature |
            | boolProp  | bool      |
            | intProp   | int       |
            | mixedProp | int       |
            | prop      | SomeClass |
            | roProp    | string    |
            | stdProp   | SomeClass |
            | woProp    | string    |
