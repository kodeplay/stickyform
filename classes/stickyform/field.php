<?php defined('SYSPATH') or die('No direct script access.');

class Stickyform_Field {

    private $_label;

    private $_element;

    private $_error;

    private $_tooltip;

    public function __construct($label, $element, $error=NULL, $tooltip=NULL) {
        $this->_label = $label;
        $this->_element = $element;
        $this->_error = $error;
        $this->_tooltip = $tooltip;
    }

    public function label() {
        return $this->_label;
    }    

    public function element() {
        return $this->_element;
    }

    public function error() {
        return $this->_error;
    }

    public function tooltip() {
        return $this->_tooltip;
    }

}