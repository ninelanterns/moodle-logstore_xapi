<?php namespace MXTranslator\Tests;
use \MXTranslator\Events\UserRegistered as Event;

class UserRegisteredTest extends EventTest {
    protected static $recipe_name = 'user_registered';
    
    /**
     * Sets up the tests.
     * @override TestCase
     */
    public function setup() {
        $this->event = new Event($this->repo);
    }

    protected function assertOutput($input, $output) {
        parent::assertOutput($input, $output);
        $this->assertUser($input['relateduser'], $output, 'user');
    }
}
