<?php defined('SYSPATH') or die('No direct script access.');

class Stickyform_Field {

    private $_label;

    private $_form_element;

    private $_error;

    private $_tooltip;

    public function __construct($label, $form_element, $error=NULL, $tooltip=NULL) {
        $this->_label = $label;
        $this->_form_element = $form_element;
        $this->_error = $error;
        $this->_tooltip = $tooltip;
    }

    public function label() {
        return $this->_label;
    }    

    public function form_element() {
        return $this->_form_element;
    }

    public function error() {
        return $this->_error;
    }

    public function tooltip() {
        return $this->_tooltip;
    }

}