Feature: Object Completion
    As a user
    I want to have class properties and methods completion after -> or ::
    So that I can quickly choose name I need

    Scenario: Gettings all methods and properties for $this
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public function method1()
            {

            }
            public function method2()
            {

            }
            private function somePrivateMethod()
            {

            }
            private $someDep;
            public $someApi;
        }
        """
        When I type "$this->" on the 7 line
        And ask for completion
        Then I should get:
            | Name |
            | method1 |
            | method2 |
            | someApi |
            | someDep |
            | somePrivateMethod |

    Scenario: Accessing only public methods and properties out of class
        Given there is a file with:
        """
        <?php

        class SomeClass
        {
            public function method3()
            {

            }
            public function method4()
            {

            }
            private function somePrivateMethod()
            {

            }
            private $someDep;
            public $someApi;
        }
        """
        When I type "$a = new SomeClass;" on the 20 line
        And I type "$a->" on the 21 line
        And ask for completion
        Then I should get:
            | Name |
            | method3 |
            | method4 |
            | someApi |

    Scenario: Accessing only public methods and properties for return
        Given there is a file with:
        """
        <?php

        class SomeOtherClass
        {
            public function methodOfOtherClass()
            {

            }
            public function otherMethodOfOtherClass()
            {

            }
            private function privateMethodOfOtherClass()
            {

            }
            public $aPublicProperty;
        }
        class SomeClass
        {
            /**
             * @return SomeOtherClass
             */
            public function method1()
            {

            }
            public function method2()
            {

            }
            private function somePrivateMethod()
            {

            }
            private $someDep;
            public $someApi;
        }
        """
        When I type "$a = new SomeClass;" on the 39 line
        And I type "$a->method1()->" on the 40 line
        And ask for completion
        Then I should get:
            | Name |
            | aPublicProperty |
            | methodOfOtherClass |
            | otherMethodOfOtherClass |
