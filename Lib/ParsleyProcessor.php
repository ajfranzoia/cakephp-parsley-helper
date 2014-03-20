<?php

class ParsleyProcessor implements CakeEventListener {

/**
 * @var boolean
 */
    protected $_enabled = false;

/**
 * @var boolean
 */
    protected $_namespace = null;

/**
 * @var boolean
 */
    protected $_priorityEnabled = null;

/**
 * @var boolean
 */
    protected $_excluded = null;
    
    public function implementedEvents() {
        return array(
            'FormHelper.beforeFormCreate' => 'initialize',
            'FormHelper.afterInitInput' => 'processInput',
        );
    }

    public function initialize($event) {
        $FormHelper = $event->subject();

        $options = $event->data['options'];
        
        $shortcut = $this->_extractOption('parsley', $options);
        $attr = $this->_extractOption('data-parsley-validate', $options);
        $enabled = !empty($attr);
        
        if ($shortcut !== false) {
            $options['data-parsley-validate'] = true;
            if (!is_array($shortcut)) {
                $shortcut = array();
            }
                
            $options['data-parsley-error-class'] = $this->_extractOption('errorClass', $shortcut, 'has-error');
            $options['data-parsley-success-class'] = $this->_extractOption('successClass', $shortcut, 'has-success');
            $options['data-parsley-errors-wrapper'] = $this->_extractOption('errorsWrapper', $shortcut, '<ul class="error-list help-block"></ul>');
            
            $options['data-parsley-validate'] = true;
            $enabled = true;
        }
        unset($options['parsley']);
        
        if ($enabled) {
            $this->_enabled = true;
            $options['novalidate'] = true;
            $this->_namespace = $this->_extractOption('data-parsley-namespace', $options, 'data-parsley');
            $this->_priorityEnabled = $this->_extractOption('data-parsley-priority-enabled', $options, null);
            $this->_excluded = $this->_extractOption('data-parsley-excluded', $options, null);
        }
        
        $event->data['options'] = $options;
    }
    
    public function processInput($event) {
        if ($this->_enabled) {
            $this->_isUpdate = $event->subject()->requestType === 'put';
            if (empty($event->data['model']) || empty($event->data['field'])) {
                return;
            }
            
            $field = $event->data['field'];
            $validator = $event->data['model']->validator();
            if (!isset($validator[$field])) {
                return ;
            }
            $rules = $validator[$field];
            
            $result = $this->_applyParsley($field, $rules, $event->data['result'], $event->data['model']->validationDomain);
            $event->data['result'] = $result;
        }
    }

/**
 * Adds Parsley data attributes to field options if Parsley is enabled
 * 
 * @param string $field Name of the field to initialize options for.
 * @param array $options Array of options to append options into.
 * @return array Array of options for the input.
 */
    protected function _applyParsley($field, $rules, $options = array(), $validationDomain) {
        $parsleyRules = array();
        if ($this->_isRequiredField($rules)) {
            $parsleyRule = $this->_addRequiredValidation(null, $options);
            $parsleyRule['message'] = 'required';
            $parsleyRules[] = $parsleyRule;
        }
        
        foreach ($rules as $rule) {
            $rule->rule = (array) $rule->rule;
            $ruleName = $rule->rule[0];
            
            $methodName = '_add' . ucfirst($ruleName) . 'Validation';
            if (method_exists($this, $methodName)) {
                $parsleyRule = $this->$methodName($rule, $options);
                $parsleyRule['message'] = isset($rule->message) ? $rule->message : $ruleName;
                $parsleyRules[] = $parsleyRule;
            }
        }
        
        foreach ($parsleyRules as $rule) {
            $attr = $this->_namespace . '-' . $rule['rule'];
            $options[$attr] = $rule['value'];
            
            $message = $originalMessage = $rule['message'];
            if ($validationDomain != null) {
                $message = __d($model->validationDomain, $message);
            }
            if (empty($model->validationDomain) || $message == $originalMessage) {
                $message = __($message);
            }
            
            $options[$attr . '-message'] = $message;
        }
        
        return $options;
    }

    public function _addRequiredValidation($rule) {
        return array(
            'rule' => 'required',
            'value' => 'true',
        );
    }

    public function _addNotEmptyValidation($rule) {
        return $this->_addRequiredValidation($rule);
    }
    
    public function _addAlphaNumericValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'alphanum',
        );
    }
    
    public function _addDecimalValidation($rule) {
        $places = isset($rule->rule[1]) ? $rule->rule[1] : null;
        $regex = isset($rule->rule[2]) ? $rule->rule[2] : null;
        
        if ($regex === null) {
            $lnum = '[0-9]+';
            $dnum = "[0-9]*[\.]{$lnum}";
            $sign = '[+-]?';
            $exp = "(?:[eE]{$sign}{$lnum})?";

            if ($places === null) {
                $regex = "/^{$sign}(?:{$lnum}|{$dnum}){$exp}$/";

            } elseif ($places === true) {
                if (is_float($check) && floor($check) === $check) {
                    $check = sprintf("%.1f", $check);
                }
                $regex = "/^{$sign}{$dnum}{$exp}$/";

            } elseif (is_numeric($places)) {
                $places = '[0-9]{' . $places . '}';
                $dnum = "(?:[0-9]*[\.]{$places}|{$lnum}[\.]{$places})";
                $regex = "/^{$sign}{$dnum}{$exp}$/";
            }
        }
        
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }
    
    public function _addBetweenValidation($rule) {
        return array(
            'rule' => 'length',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
    public function _addBlankValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^\s*$',
        );
    }
    
    public function _addBooleanValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^(false|true|0|1)$',
        );
    }
    
    public function _addComparisonValidation($rule) {
        $operator = $rule->rule[1];
        $check2 = $rule->rule[2];
        
        switch ($operator) {
            case 'isgreater':
            case '>':
                $rule = 'min';
                $value = $check2 + 1;
                break;
            case 'isless':
            case '<':
                $rule = 'max';
                $value = $check2 - 1;
                break;
            case 'greaterorequal':
            case '>=':
                $rule = 'min';
                $value = $check2;
                break;
            case 'lessorequal':
            case '<=':
                $rule = 'max';
                $value = $check2;
                break;
            case 'equalto':
            case '==':
                $rule = 'pattern';
                $value = '^' . $check2 . '$';
                break;
            case 'notequal':
            case '!=':
                $rule = 'pattern';
                $value = '^(?!^' . $check2 . '$).*';
                break;
        }
        
        return array(
            'rule' => $rule,
            'value' => $value,
        );
    }
    
    public function _addEqualToValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^' . $check2 . '$',
        );
    }
    
    public function _addCompareFieldsValidation($rule) {
        return array(
            'rule' => 'equalto',
            'value' => sprintf('[name=\'data[%s][%s]\']', $modelName, $rule->rule[1]),
        );
    }
    
    public function _addCustomValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => $rule->rule[1],
        );
    }
    public function _addDateValidation($rule) {
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . '$',
        );
    }
    
    public function _addTimeValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$',
        );
        
    }

    public function _addDatetimeValidation($rule) {
        $timeRegex = '(((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))|([01]\d|2[0-3])(:[0-5]\d){0,2})';
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . ' ' . $timeRegex . '$',
        );
        
    }
    
    public function _addEmailValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'email',
        );
    }
    
    public function _addIpValidation($rule) {
        $type = isset($rule->rule[1]) ? $rule->rule[1] : 'both';
        $ipv4Regex = '((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)';
        $ipv6Regex = '((?=.*::)(?!.*::.+::)(::)?([\dA-F]{1,4}:(:|\b)|){5}|([\dA-F]{1,4}:){6})((([\dA-F]{1,4}((?!\3)::|:\b|$))|(?!\2\3)){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4})';
        if ($type == 'ipv4') {
            $regex = '^' . $ipv4Regex . '$';
        } elseif ($type == 'ipv6') {
            $regex = '^' . $ipv4Regex . '$/i';
        } else {
            $regex = '^(' . $ipv4Regex . '|' . $ipv6Regex . ')$/i';
        }
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }

    public function _addMoneyValidation($rule) {
        $symbolPosition = isset($rule->rule[1]) ? $rule->rule[1] : 'left';
        $money = '(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{1,2})?';
        if ($symbolPosition === 'right') {
            $regex = '/^' . $money . '(?<!\u00a2)\$?$/';
        } else {
            $regex = '/^(?!\u00a2)\$?' . $money . '$/';
        }
        
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }
    
    public function _addMaxLengthValidation($rule) {
        return array(
            'rule' => 'maxlength',
            'value' => $rule->rule[1],
        );
    }
    
    public function _addMinLengthValidation($rule) {
        return array(
            'rule' => 'minlength',
            'value' => $rule->rule[1],
        );
    }
    
    public function _addPhoneValidation($rule) {
        $regex = empty($rule->rule[1]) ? null : $rule->rule[1];
        $country = empty($rule->rule[2]) ? 'all' : $rule->rule[2];
        
        if ($regex === null) {
            switch ($country) {
                case 'us':
                case 'ca':
                case 'can': // deprecated three-letter-code
                case 'all':
                    // includes all NANPA members.
                    // see http://en.wikipedia.org/wiki/North_American_Numbering_Plan#List_of_NANPA_countries_and_territories
                    $regex = '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?';

                    // Area code 555, X11 is not allowed.
                    $areaCode = '(?![2-9]11)(?!555)([2-9][0-8][0-9])';
                    $regex .= '(?:\(\s*' . $areaCode . '\s*\)|' . $areaCode . ')';
                    $regex .= '\s*(?:[.-]\s*)?)';

                    // Exchange and 555-XXXX numbers
                    $regex .= '(?!(555(?:\s*(?:[.\-\s]\s*))(01([0-9][0-9])|1212)))';
                    $regex .= '(?!(555(01([0-9][0-9])|1212)))';
                    $regex .= '([2-9]1[02-9]|[2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)';

                    // Local number and extension
                    $regex .= '?([0-9]{4})';
                    $regex .= '(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/';
                break;
            }
        }
        
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }
    
    public function _addPostalValidation($rule) {
        $regex = empty($rule->rule[1]) ? null : $rule->rule[1];
        $country = empty($rule->rule[2]) ? 'us' : $rule->rule[2];
        
        if ($regex === null) {
            switch ($country) {
                case 'uk':
                    $regex = '/\\A\\b[A-Z]{1,2}[0-9][A-Z0-9]? [0-9][ABD-HJLNP-UW-Z]{2}\\b\\z/i';
                    break;
                case 'ca':
                    $district = '[ABCEGHJKLMNPRSTVYX]';
                    $letters = '[ABCEGHJKLMNPRSTVWXYZ]';
                    $regex = "/\\A\\b{$district}[0-9]{$letters} [0-9]{$letters}[0-9]\\b\\z/i";
                    break;
                case 'it':
                case 'de':
                    $regex = '/^[0-9]{5}$/i';
                    break;
                case 'be':
                    $regex = '/^[1-9]{1}[0-9]{3}$/i';
                    break;
                case 'us':
                    $regex = '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i';
                    break;
            }
        }
        
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }
    
    public function _addRangeValidation($rule) {
        return array(
            'rule' => 'range',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
    public function _addSsnValidation($rule) {
        $regex = $rule->rule[1];
        $country = $rule->rule[2];
        
        if ($regex === null) {
            switch ($country) {
                case 'dk':
                    $regex = '/\\A\\b[0-9]{6}-[0-9]{4}\\b\\z/i';
                    break;
                case 'nl':
                    $regex = '/\\A\\b[0-9]{9}\\b\\z/i';
                    break;
                case 'us':
                    $regex = '/\\A\\b[0-9]{3}-[0-9]{2}-[0-9]{4}\\b\\z/i';
                    break;
            }
        }
        
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }
    
    public function _addUrlValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'url',
        );
    }
    
    public function _addNumericValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'number',
        );
    }
    
    public function _addNaturalNumberValidation($rule) {
        $allowZero = isset($rule->rule[1]) ? $rule->rule[1] : false;
        return array(
            'rule' => 'pattern',
            'value' => $allowZero ? '^(?:0|[1-9][0-9]*)$' : '^[1-9][0-9]*$',
        );
    }
    
    public function _addUuidValidation($rule) {
        $regex = '^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[0-5][a-fA-F0-9]{3}-[089aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}$';
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }

    public function _getDateRegex($format) {
        $month = '(0[123456789]|10|11|12)';
        $separator = '([- /.])';
        $fourDigitYear = '(([1][9][0-9][0-9])|([2][0-9][0-9][0-9]))';
        $twoDigitYear = '([0-9]{2})';
        $year = '(?:' . $fourDigitYear . '|' . $twoDigitYear . ')';

        $regex['dmy'] = '(?:(?:31(\\/|-|\\.|\\x20)(?:0?[13578]|1[02]))\\1|(?:(?:29|30)' .
            $separator . '(?:0?[1,3-9]|1[0-2])\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:29' .
            $separator . '0?2\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\\d|2[0-8])' .
            $separator . '(?:(?:0?[1-9])|(?:1[0-2]))\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})';

        $regex['mdy'] = '(?:(?:(?:0?[13578]|1[02])(\\/|-|\\.|\\x20)31)\\1|(?:(?:0?[13-9]|1[0-2])' .
            $separator . '(?:29|30)\\2))(?:(?:1[6-9]|[2-9]\\d)?\\d{2})$|^(?:0?2' . $separator . '29\\3(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:(?:0?[1-9])|(?:1[0-2]))' .
            $separator . '(?:0?[1-9]|1\\d|2[0-8])\\4(?:(?:1[6-9]|[2-9]\\d)?\\d{2})';

        $regex['ymd'] = '(?:(?:(?:(?:(?:1[6-9]|[2-9]\\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))' .
            $separator . '(?:0?2\\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\\d)?\\d{2})' .
            $separator . '(?:(?:(?:0?[13578]|1[02])\\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\\2(?:0?[1-9]|1\\d|2[0-8]))))';

        $regex['dMy'] = '((31(?!\\ (Feb(ruary)?|Apr(il)?|June?|(Sep(?=\\b|t)t?|Nov)(ember)?)))|((30|29)(?!\\ Feb(ruary)?))|(29(?=\\ Feb(ruary)?\\ (((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))|(0?[1-9])|1\\d|2[0-8])\\ (Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)\\ ((1[6-9]|[2-9]\\d)\\d{2})';

        $regex['Mdy'] = '(?:(((Jan(uary)?|Ma(r(ch)?|y)|Jul(y)?|Aug(ust)?|Oct(ober)?|Dec(ember)?)\\ 31)|((Jan(uary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep)(tember)?|(Nov|Dec)(ember)?)\\ (0?[1-9]|([12]\\d)|30))|(Feb(ruary)?\\ (0?[1-9]|1\\d|2[0-8]|(29(?=,?\\ ((1[6-9]|[2-9]\\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)))))))\\,?\\ ((1[6-9]|[2-9]\\d)\\d{2}))';

        $regex['My'] = '(Jan(uary)?|Feb(ruary)?|Ma(r(ch)?|y)|Apr(il)?|Ju((ly?)|(ne?))|Aug(ust)?|Oct(ober)?|(Sep(?=\\b|t)t?|Nov|Dec)(ember)?)' .
            $separator . '((1[6-9]|[2-9]\\d)\\d{2})';

        $regex['my'] = '(' . $month . $separator . $year . ')';
        $regex['ym'] = '(' . $year . $separator . $month . ')';
        $regex['y'] = '(' . $fourDigitYear . ')';
        
        return $regex[$format];
    }

/**
 * Returns if a field is required to be filled based on validation properties from the validating object.
 *
 * @param CakeValidationSet $validationRules
 * @return boolean true if field is required to be filled, false otherwise
 */
    protected function _isRequiredField($validationRules) {
        if (empty($validationRules) || count($validationRules) === 0) {
            return false;
        }

        foreach ($validationRules as $rule) {
            $rule->isUpdate($this->_isUpdate);
            if ($rule->skip()) {
                continue;
            }

            return !$rule->allowEmpty;
        }
        return false;
    }

/**
 * Extracts a single option from an options array.
 *
 * @param string $name The name of the option to pull out.
 * @param array $options The array of options you want to extract.
 * @param mixed $default The default option value
 * @return mixed the contents of the option or default
 */
    protected function _extractOption($name, $options, $default = null) {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }
        return $default;
    }
}
