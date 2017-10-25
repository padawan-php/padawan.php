Feature: Static completion
    As a user
    I want to have different visibility scopes in class and out of class
    So that I can see private static methods when getting completions for self and static

    Scenario Outline: Accessing all methods and properties for self and static
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public static $aaa;
            private static $bbb;
            protected static $ccc;

            public static function method1()
            {

            }
            private static function somePrivateMethod()
            {

            }
            protected static function someProtectedMethod()
            {

            }

            public function test()
            {

            }
        }
        """
        When I type "<typed>" on the <line> line
        And I ask for completion
        Then I should get:
            | Name |
            | $aaa |
            | $bbb |
            | $ccc |
            | class |
            | method1 |
            | somePrivateMethod |
            | someProtectedMethod |
        Examples:
            | typed    | line |
            | self::   | 19   |
            | self::   | 24   |
            | static:: | 24   |
            | static:: | 19   |

    Scenario: Accessing only public methods and properties out of class
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public static $aaa;
            private static $bbb;
            protected static $ccc;

            public static function method1()
            {

            }
            private static function somePrivateMethod()
            {

            }
            protected static function someProtectedMethod()
            {

            }

            public function test()
            {

            }
        }

        function test() {

        }
        """
        When I type "SomeClass::" on the 29 line
        And I ask for completion
        Then I should get:
            | Name |
            | $aaa |
            | class |
            | method1 |

    Scenario Outline: Accessing non-private methods and properties for child class
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public static $aaa;
            private static $bbb;
            protected static $ccc;

            public static function method1()
            {

            }
            private static function somePrivateMethod()
            {

            }
            protected static function someProtectedMethod()
            {

            }

            public function test()
            {

            }
        }
        class ChildClass extends SomeClass
        {
            public static $childaaa;
            private static $childbbb;
            protected static $childccc;

            public static function childMethod(){}
            private static function childPrivateMethod(){}
            protected static function childProtectedMethod(){}

            public function testChild()
            {

            }
        }
        """
        When I type "<typed>" on the <line> line
        And I ask for completion
        Then I should get:
            | Name |
            | $aaa |
            | $ccc |
            | $childaaa |
            | $childbbb |
            | $childccc |
            | childMethod |
            | childPrivateMethod |
            | childProtectedMethod |
            | class |
            | method1 |
            | someProtectedMethod |
        Examples:
            | typed    | line |
            | self::   | 39   |
            | static:: | 39   |

    Scenario: Accessing non-private methods and properties for parent
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public static $aaa;
            private static $bbb;
            protected static $ccc;

            public static function method1()
            {

            }
            private static function somePrivateMethod()
            {

            }
            protected static function someProtectedMethod()
            {

            }

            public function test()
            {

            }
        }
        class ChildClass extends SomeClass
        {
            public static $childaaa;
            private static $childbbb;
            protected static $childccc;

            public static function childMethod(){}
            private static function childPrivateMethod(){}
            protected static function childProtectedMethod(){}

            public function testChild()
            {

            }
        }
        """
        When I type "parent::" on the 39 line
        And I ask for completion
        Then I should get:
            | Name |
            | test |

    Scenario: Accessing only public static methods and properties for return
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public static $aaa;
            private static $bbb;
            protected static $ccc;
            public $prop;

            public static function method1()
            {

            }
            private static function somePrivateMethod()
            {

            }
            protected static function someProtectedMethod()
            {

            }

            public function test()
            {

            }
        }
        class OtherClass
        {
            public static $xxx;
            private static $yyy;
            protected static $zzz;

            /**
             * @return SomeClass
             */
            public function fetchClass(){}

            public function test()
            {


            }
        }
        """
        When I type "$a = new OtherClass;" on the 41 line
        And I type "$a->fetchClass()->" on the 42 line
        And I ask for completion
        Then I should get:
            | Name |
            | prop |
            | test |
