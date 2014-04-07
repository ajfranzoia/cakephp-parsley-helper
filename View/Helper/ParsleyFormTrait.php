<?php
/**
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Codaxis (http://codaxis.com)
 * @author        augusto-cdxs (https://github.com/augusto-cdxs/
 * @link          https://github.com/Codaxis/parsley-helper ParsleyHelper
 * @package       ParsleyHelper.View.Helper
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('ParsleyProcessor', 'ParsleyHelper.Lib');

trait ParsleyFormTrait {
    
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
