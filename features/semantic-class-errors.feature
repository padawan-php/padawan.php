Feature: Semantic errors in classes
    As a user
    I want all semantic invalid classes to be excluded from index
    So that I can have project with broken classes or deps

    Scenario: Class extending itself
        Given there is a file with:
        """
        <?php

        class C extends C {}
        """

    Scenario: Parent extends Child
        Given there is a file with:
        """
        <?php

        class E extends F {}
        class F extends E {}
        """

    Scenario: Parent extends Sub Child
        Given there is a file with:
        """
        <?php

        class G extends H {}
        class H extends I {}
        class I extends G {}
        """

