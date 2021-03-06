<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Stickyform
 * 
 */
class Stickyform {

    /**
     * The action where the form will be posted
     * @param String $_action
     */
    private $_action;

    /**
     * Html attributes of the form other than action
     * @param array $_attributes
     */
    private $_attributes = array();

    /**
     * Fields Array
     * @param array $_fields
     */
    private $_fields = array();

    /**
     * Array of values of fields
     * @param array $_values
     */
    private $_values = array();

    /**
     * Data persisted in the db
     * @param array $saved_data
     */
    public $saved_data = array();

    /**
     * Data submitted through the form
     * @param array $posted_data
     */
    public $posted_data = array();

    /**
     * Array of default values to be shown
     * in the create action
     * @param array $default_data
     */
    public $default_data = array();

    /**
     * Array of errors to be shown alongside the fields
     * @param array $_errors
     */
    private $_errors;

    /**
     * The starting form tag
     */
    private $_startform;

    /**
     * The ending form tag
     */
    private $_endform;

    /**
     * Whether its a new record or existing record
     */
    private $_is_new = true;

    /**
     * field types. keys are types, values are method names to be called
     * @static
     * @array
     */
    private static $FIELD_TYPES = array(
        'text'     => '_text',
        'password' => '_password',
        'select'   => '_select',
        'radio'    => '_radio',
        //'dual_radio' => '_dual_radio',
        'checkbox' => '_checkbox',
        //'multi_checkbox' => '_multi_checkbox',
        'hidden'   => '_hidden',
        'textarea' => '_textarea',
        'button'   => '_button',
        'submit'   => '_submit',
        'file'     => '_file',
    );

    private static $RESERVED_KEYWORDS = array(
        'append', 'process', 'startform', 'endform', 'saved_data', 'posted_data', 'default_data'
    );

    /**
     * @param $action required 
     * @param $attributes = array()
     * @param $errors = array()
     */
    public function __construct($action, $attributes=array(), $errors=array()) {
        $this->_action = $action;
        $this->_attributes = $attributes;  
        $this->_startform = Form::open($action, $attributes);
        $this->_endform = Form::close();
        $this->_errors = $errors;
    }

    /**
     * Method to process the fields and create instance variables
     * @throws Stickyform_Exception if type of form field not in self::$FIELD_TYPES
     */
    public function process() {
        $this->_merge_values();        
        foreach ($this->_fields as $field) {
            if (!array_key_exists($field['type'], self::$FIELD_TYPES)) {
                throw new Stickyform_Exception('Form field of type ' . $field['type'] . ' is not supported');
            }
            $method = self::$FIELD_TYPES[$field['type']];
            $this->{$field['name']} = $this->{$method}($field['label'], $field['name'], $field['meta']);
        }
    }

    /**
     * @param String $label required  what will be displayed in browser
     * @param String $name required
     * @param String type = 'text'
     * @param String meta = array any other data required.
     * Some form fields will have specific meta data and will have to be passed
     * @return Stickyform current object
     */
    public function append($label, $name, $type='text', $meta = array()) {
        if (in_array($name, self::$RESERVED_KEYWORDS)) {
            throw new Stickyform_Exception($name . ' is a reserved keyword in Stickyform class, please choose something else');
        }
        $error = Arr::get($this->_errors, $name, '');
        $meta = array_merge($meta, array('error' => $error));
        $this->_fields[] = array(
            'label' => $label,
            'name'  => $name,
            'type'  => $type,
            'meta'  => $meta
        );
        return $this;
    }

    /**
     * @return the starting form tag (html markup)
     */
    public function startform() {
        return $this->_startform;
    }

    /**
     * @return the ending form tag (html markup)
     */
    public function endform() {
        return $this->_endform;
    }

    public function is_new($flag=NULL) {
        if ($flag !== NULL) {
            $this->_is_new = $flag;
        }
        return $this->_is_new;
    }

    /**
     * Method to merge the saved, posted and default values
     * default overwritten by saved overwritten by post to make
     * the form sticky
     */
    private function _merge_values() {
        $this->_values = $this->default_data;
        $this->_values = array_merge($this->_values, $this->saved_data);
        $this->_values = array_merge($this->_values, $this->posted_data);
    }
    
    /**
     * @param String $name (key)
     * @return mixed value associated with the key in the $_values array
     * @throws Stickyform_Exception if key not found
     */
    public function get_value($name) {
        if (isset($this->_values[$name])) {
            return $this->_values[$name];
        } else {
            throw new Stickyform_Exception('Form field value not found for ' . $name . '.');
        }
    }

    /**
     * Helper function to get an element from the meta data array
     * and return NULL if key doesn't exist
     * @return mixed
     */
    private static function _get_meta($key, $meta) {
        return isset($meta[$key]) ? $meta[$key] : NULL;
    }

    /**
     * Helper function to get the correct name attribute for the 
     * form field. ie if $meta['attributes']['name'] is explicitly specified,
     * use if other wise use name. 
     * This workaround is used to allow name attributes such as "selected[]"
     * in this case, $name will be class property name friendly eg. list_selected
     * while the actual name attr selected[] will be specified in $meta['attributes']['name']
     * @param String $name 
     * @param Array $meta
     * @return String correct name attribute with which the form is submitted
     */
    private static function _get_name_attr($name, $meta) {
        $attributes = self::_get_meta('attributes', $meta);
        return isset($attributes['name']) ? $attributes['name'] : $name;        
    }    

    /**
     * Helper function to convert all the array keys to the variables of key 
     * This workaround is used to convert the sub array of post to the variables (keys)
     * of the array
     * @param Array $data 
     * @return Array $array
     */
    public static function ungroup_params($data){
        
        if(!is_array($data)) return array();
        
        $array = array();
        foreach($data as $key => $value){
            if(is_array($value)){
                foreach($value as $k => $v){
                    $array[$key . '_' . $k] = $v;
                }
            } else {
                $array[$key] = $value;
            }
        }
        return $array;
    }
    
    /**
     * @return Stickyform_Field for text field
     */
    private function _text($label, $name, $meta=array()) {
        $value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $label = Form::label($name, $label);
        $form_element = Form::input($name, $value, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);
    }

    /**
     * @return Stickyform_Field for password field
     */
    private function _password($label, $name, $meta=array()) {
        $label = Form::label($name, $label);
        $form_element = Form::password($name, '', self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);
    }

    /**
     * @param String $label - is redundant here but required
     * @return Stickyform_Field for hidden field
     */
    private function _hidden($label, $name, $meta=array()) {
        $value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $form_element = Form::hidden($name, $value, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    /**
     * @return Stickyform_Field for radio field
     */
    private function _radio($label, $name, $meta=array()) {
        $value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $label = Form::label($name, $label);
        $form_element = Form::radio($name, $value, (bool)$value, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    /**
     * @return Stickyform_Field for dual radio field (typical yes/no)
     */
    private function _dual_radio($label, $name, $meta=array()) {
        // to be implemented
    }

    /**
     * @return Stickyform_Field for checkbox field
     * checkbox value will be passed in the meta array as value
     */
    private function _checkbox($label, $name, $meta=array()) {
        $checked_value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $label = Form::label($name, $label);
        $attr = self::_get_meta('attributes', $meta);
        $name = isset($attr['name']) ? $attr['name'] : $name;
        $value = $attr['value'];
        $checked = ($checked_value == $value);
        $form_element = Form::checkbox($name, $value, $checked, $attr);
        return new Stickyform_Field($label, $form_element, $meta['error']);
    }

    /**
     * @return Stickyform_Field for multiple checkbox field
     */
    private function _multi_checkbox($label, $name, $meta=array()) {
        $value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $field = Form::label($name, $label);
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    /**
     * @return Stickyform_Field for selectbox/combobox field
     */
    private function _select($label, $name, $meta=array()) {
        $selected = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $label = Form::label($name, $label);
        $options = isset($meta['options']) ? $meta['options'] : NULL;
        $form_element = Form::select($name, $options, $selected, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    /**
     * @return Stickyform_Field for textarea field
     */
    private function _textarea($label, $name, $meta=array()) {
        $value = $this->get_value($name);
        $name = self::_get_name_attr($name, $meta);
        $label = Form::label($name, $label);
        $form_element = Form::textarea($name, $value, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    /**
     * @param String $label will be value attr of the button
     * @return Stickyform_Field for button
     */
    private function _button($label, $name, $meta=array()) {
        $form_element = Form::button($name, $label, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element);        
    }

    /**
     * @param $label will be attr value of the submit button
     * @return Stickyform_Field for submit button
     */
    private function _submit($label, $name, $meta=array()) {
        $form_element = Form::submit($name, $label, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);        
    }

    private function _file($label, $name, $meta=array()) {
        $form_element = Form::file($name, self::_get_meta('attributes', $meta));
        return new Stickyform_Field($label, $form_element, $meta['error']);
    }
}
