<?php namespace MXTranslator\Tests;
use \PHPUnit_Framework_TestCase as PhpUnitTestCase;
use \MXTranslator\Events\Event as Event;

abstract class EventTest extends PhpUnitTestCase {
    protected static $xapi_type = 'http://lrs.learninglocker.net/define/type/moodle/';
    protected static $recipe_name;

    /**
     * Sets up the tests.
     * @override PhpUnitTestCase
     */
    public function setup() {
        $this->event = new Event();
    }

    /**
     * Tests the read method of the Event.
     */
    public function testRead() {
        $input = $this->constructInput();
        $outputs = $this->event->read($input);
        $this->assertOutputs($input, $outputs);
        foreach ($outputs as $output) {
            $this->assertOutput($input, $output);
            $this->createExampleFile($output);
        }
    }

    protected function constructInput() {
        return [
            'user' => $this->constructUser(),
            'relateduser' => $this->constructUser(),
            'course' => $this->constructCourse(),
            'app' => $this->constructApp(),
            'source' => $this->constructSource(),
            'event' => $this->constructEvent('\core\event\course_viewed'),
            'info' => $this->constructInfo()
        ];
    }

    protected function constructInfo() {
        return (object) [
            'https://moodle.org/' => '1.0.0',
            'https://github.com/LearningLocker/Moodle-Log-Expander' => '1.0.0',
        ];
    }

    protected function constructUser() {
        return (object) [
            'id' => 1,
            'url' => 'http://www.example.com/user_url',
            'fullname' => 'Test user_name'
        ];
    }

    private function constructEvent($event_name) {
        return [
            'eventname' => $event_name,
            'timecreated' => 1433946701,
        ];
    }

    protected function constructCourse() {
        return (object) [
            'url' => 'http://www.example.com/course_url',
            'fullname' => 'Test course_fullname',
            'summary' => '<p>Test course_summary</p>',
            'lang' => 'en',
            'type' => 'moodle_course',
        ];
    }

    protected function constructApp() {
        return (object) [
            'url' => 'http://www.example.com',
            'fullname' => 'Test site_fullname',
            'summary' => '<p>Test site_summary</p>',
            'lang' => 'en',
            'type' => 'moodle_site',
        ];
    }

    protected function constructSource() {
        return (object) [
            'url' => 'http://moodle.org',
            'fullname' => 'Moodle',
            'summary' => 'Moodle is a open source learning platform designed to provide educators,'
                .' administrators and learners with a single robust, secure and integrated system'
                .' to create personalised learning environments.',
            'lang' => 'en',
            'type' => 'moodle_source',
        ];
    }

    protected function assertOutputs($input, $output) {
        //output is an associative array
        $this->assertEquals(0, count(array_filter(array_keys($output), 'is_string')));
        //length of output is 1. Overwrite this function if a different value is needed.
        $this->assertEquals(1 , count($output));
    }

    protected function assertOutput($input, $output) {
        $this->assertApp($input['app'], $output, 'app');
        $this->assertEvent($input['event'], $output);
        $this->assertEquals(static::$recipe_name, $output['recipe']);
        $this->assertInfo($input['info'], $output['context_info']);
    }

    protected function assertUser($input, $output, $type) {
        $this->assertEquals($input->id, $output[$type.'_id']);
        $this->assertEquals($input->url, $output[$type.'_url']);
        $this->assertEquals($input->fullname, $output[$type.'_name']);
    }

    protected function assertCourse($input, $output, $type) {
        $ext_key = 'http://lrs.learninglocker.net/define/extensions/moodle_course';
        $this->assertEquals($input->lang, $output['context_lang']);
        $this->assertEquals($input->url, $output[$type.'_url']);
        $this->assertEquals($input->fullname, $output[$type.'_name']);
        $this->assertEquals(strip_tags($input->summary), $output[$type.'_description']);
        $this->assertEquals(static::$xapi_type.$input->type, $output[$type.'_type']);
        $this->assertEquals($input, $output[$type.'_ext']);
        $this->assertEquals($ext_key, $output[$type.'_ext_key']);
    }

    protected function assertApp($input, $output, $type) {
        $ext_key = 'http://lrs.learninglocker.net/define/extensions/moodle_course';
        $app_type = 'http://id.tincanapi.com/activitytype/site';
        $this->assertEquals($input->lang, $output['context_lang']);
        $this->assertEquals($input->url, $output[$type.'_url']);
        $this->assertEquals($input->fullname, $output[$type.'_name']);
        $this->assertEquals(strip_tags($input->summary), $output[$type.'_description']);
        $this->assertEquals($app_type, $output[$type.'_type']);
        $this->assertEquals($input, $output[$type.'_ext']);
        $this->assertEquals($ext_key, $output[$type.'_ext_key']);
    }

    protected function assertSource($input, $output, $type) {
        $app_type = 'http://id.tincanapi.com/activitytype/source';
        $this->assertEquals($input->lang, $output['context_lang']);
        $this->assertEquals($input->url, $output[$type.'_url']);
        $this->assertEquals($input->fullname, $output[$type.'_name']);
        $this->assertEquals(strip_tags($input->summary), $output[$type.'_description']);
        $this->assertEquals($app_type, $output[$type.'_type']);
    }

    private function assertEvent($input, $output) {
        $ext_key = 'http://lrs.learninglocker.net/define/extensions/moodle_logstore_standard_log';
        $this->assertEquals('Moodle', $output['context_platform']);
        $this->assertEquals($input, $output['context_ext']);
        $this->assertEquals($ext_key, $output['context_ext_key']);
        $this->assertEquals(date('c', $input['timecreated']), $output['time']);
    }

    private function assertInfo($input, $output) {
        $version = str_replace("\n", "", str_replace("\r", "", file_get_contents(__DIR__.'/../VERSION')));
        $this->assertEquals(
            $input->{'https://moodle.org/'},
            $output->{'https://moodle.org/'}
        );
        $this->assertEquals(
            $input->{'https://github.com/LearningLocker/Moodle-Log-Expander'},
            $output->{'https://github.com/LearningLocker/Moodle-Log-Expander'}
        );
        $this->assertEquals(
            $version,
            $output->{'https://github.com/LearningLocker/Moodle-xAPI-Translator'}
        );
    }

    protected function createExampleFile($output) {
        $class_array = explode('\\', get_class($this));
        $event_name = str_replace('Test', '', array_pop($class_array));
        $example_file = __DIR__.'/../docs/examples/'.$event_name.'.json';
        file_put_contents($example_file, json_encode($output, JSON_PRETTY_PRINT));
    }
}
