<?php defined('SYSPATH') OR die('No direct access allowed.');

class StickyformTest extends Kohana_UnitTest_TestCase {

    protected $form;

    protected $action;

    protected $attributes;

    protected $errors;

    public function setUp() {
        $this->action = 'some/dummy/action';
        $this->attributes = array('class' => 'aform', 'id' => 'theform');
        $this->errors = array();
        $this->form = new Stickyform($this->action, $this->attributes, $this->errors);
    }

    public function tearDown() {
        unset($this->form);
    }

    public function test_startform() {
        $starttag = Form::open($this->action, $this->attributes);
        $this->assertEquals($starttag, $this->form->startform());
    }
    
    public function test_endform() {
        $endtag = Form::close();
        $this->assertEquals($endtag, $this->form->endform());
    }

    /**
     * @expectedException Stickyform_Exception
     */
    public function test_append() {
        // test that it returns the instance
        $instance = $this->form->append('My field', 'my_field', 'text', array());
        $this->assertInstanceOf('Stickyform', $instance);
        // test that it throws a Stickyform_Exception upon using a reserved name
        $this->form->append('append', 'append', 'text', array());        
    }

    /**
     * Test the merge values method which is actually private
     * To check that,
     * Posted > Saved > Default
     * 
     */
    public function test_merge_values() {
        $this->form->default_data = array(
            'name' => '',
        );
        $this->form->saved_data = array(
            'name' => 'jdoe',
        );
        $this->form->posted_data = array(
            'name' => 'john doe',
        );
        $this->form->append('Name:', 'name', 'text')
            ->process();
        $element = Form::input('name', 'john doe');
        $this->assertEquals($element, $this->form->name->element());
    }

    public function test_append_text() {
        $this->form->default_data = array(
            'name' => '',
        );        
        $this->form->append('Name:', 'name', 'text', array('attributes' => array('readonly' => 'readonly')))
            ->process();
        // check name property
        $this->assertFalse($this->form->name === null);
        $this->assertInstanceOf('Stickyform_Field', $this->form->name);
        $element = Form::input('name', '', array('readonly' => 'readonly'));
        $this->assertEquals($element, $this->form->name->element());        
    }

    public function test_append_password() {
        $this->form->append('Password:', 'password', 'password')
            ->process();
        $element = Form::password('password', '');
        $this->assertEquals($element, $this->form->password->element());
    }

    public function test_append_file() {
        $this->form->append('CSV File:', 'csv_file', 'file')
            ->process();
        $element = Form::file('csv_file');
        $this->assertEquals($element, $this->form->csv_file->element());
        $this->assertEquals('CSV File:', $this->form->csv_file->label());
    }

    /**
     * Test the pseudo name ie actual name attr specified through the attributes array 
     * and name passed in the method is a class property friendly key 
     */
    public function test_pseudo_name_attr() {
        $this->form->default_data = array(
            'config_allow_registration' => 1
        );
        $this->form->append('Membership:', 
                            'config_allow_registration', 
                            'checkbox', 
                            array(
                                'attributes' => array(
                                    'name' => 'config[allow_registration]',
                                    'value' => 1
                                )
                            )
        )->process();
        $element = Form::checkbox('config[allow_registration]', 1, true);
        $this->assertEquals($element, $this->form->config_allow_registration->element());                            
    }

}