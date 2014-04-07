<?php

App::uses('FormHelper', 'View/Helper');
App::uses('Set', 'Utility');
App::uses('ParsleyProcessor', 'ParsleyHelper.Lib');

class ParsleyFormHelper extends FormHelper {

/**
 * @var array
 */
    public $helpers = array(
        'Html'
    );

/**
 * ParsleyProcessor object
 * 
 * @var object
 */
    protected $_processor = null;
 
/**
 * Runs ParsleyProcessor initialize method with current form.
 * 
 * @param mixed $model
 * @param array $options
 * @return string
 */
    public function create($model = null, $options = array()) {
        $this->_processor = new ParsleyProcessor();
        $options = $this->_processor->initialize($this->_getModel($model), $options);
        $options['escape'] = false;
        return parent::create($model, $options);
    }

/**
 * Adds Parsley data attributes to field options if Parsley is enabled
 * 
 * @param string $field
 * @param array $options
 * @return array
 */
    protected function _initInputField($field, $options = array()) {
        $result = parent::_initInputField($field, $options);
        $result = $this->_processor->processInput($field, $result, $this->requestType === 'put');
        return $result;
    }

/**
 * Calls ParsleyProcessor::processDatetimeInput() to apply data-parsley-multiple on date inputs
 * 
 * @param string $fieldName
 * @param string $dateFormat
 * @param string $timeFormat
 * @param array $attributes
 * @return string
 */
    public function dateTime($fieldName, $dateFormat = 'DMY', $timeFormat = '12', $attributes = array()) {
        $attributes = $this->_processor->processDatetimeInput($fieldName, $attributes);
        return parent::dateTime($fieldName, $dateFormat, $timeFormat, $attributes);
    }
    
/**
 * Unsets Parsley processor.
 * 
 * @param array $options
 * @return string
 */
    public function end($options = null) {
        unset($this->_processor);
        return parent::end($options);
    }
}
