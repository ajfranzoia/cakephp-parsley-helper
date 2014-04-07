<?php

class ParsleyProcessor {

/**
 * Parsley state on current form
 *
 * @var boolean
 */
    protected $_enabled = false;

/**
 * Namespace
 *
 * @var string
 */
    protected $_namespace = null;

/**
 * Priority enabled
 *
 * @var boolean
 */
    protected $_priorityEnabled = null;

/**
 * Excluded fields from validation
 *
 * @var string
 */
    protected $_excluded = null;

/**
 * Current form model
 *
 * @var object
 */
    protected $_model = null;

/**
 * Checks if Parsley is enabled and initializes current form options.
 * Return array with options for Form::create().
 *
 * @param string $model
 * @param array $options
 * @return array
 */
    public function initialize($model, $options) {
        $this->_model  = $model;

        $shortcut = $this->_extractOption('parsley', $options);
        $attr = $this->_extractOption('data-parsley-validate', $options);
        $enabled = !empty($attr);
        
        if ($shortcut !== false) {
            $options['data-parsley-validate'] = true;
            if (!is_array($shortcut)) {
                $shortcut = array();
            }
            $options['data-parsley-error-class'] = $this->_extractOption('data-parsley-namespace', $options);
            $options['data-parsley-error-class'] = $this->_extractOption('errorClass', $shortcut);
            $options['data-parsley-success-class'] = $this->_extractOption('successClass', $shortcut);
            $options['data-parsley-errors-wrapper'] = $this->_extractOption('errorsWrapper', $shortcut);
            $options['data-parsley-validate'] = true;
            $enabled = true;
        }
        unset($options['parsley']);
        
        if ($enabled) {
            $this->_enabled = true;
            $options['novalidate'] = true;
            $this->_namespace = $this->_extractOption('data-parsley-namespace', $options, 'data-parsley');
            $this->_priorityEnabled = $this->_extractOption('data-parsley-priority-enabled', $options);
            $this->_excluded = $this->_extractOption('data-parsley-excluded', $options);
        }
        return $options;
    }
    
/**
 * Processes input field and adds attributes according to set validation rules.
 * Return array with processed attributes.
 *
 * @param string $field
 * @param array $options
 * @param boolean $isUpdate
 * @return array
 */
    public function processInput($field, $options, $isUpdate) {
        if ($this->_enabled) {
            $this->_isUpdate = $isUpdate;
            if (empty($this->_model)) {
                return $options;
            }
            
            $validator = $this->_model->validator();
            if (!isset($validator[$field])) {
                return $options;
            }
            $rules = $validator[$field];
            
            $result = $this->_applyParsley($field, $rules, $options);
            return $result;
        }
        return $options;
    }

/**
 * Adds data-parsley-multiple attribute to date/time inputs.
 * 
 * @param string $fieldName
 * @param array $attributes
 * @return string
 */
    public function processDatetimeInput($fieldName, $attributes) {
        if ($this->_enabled) {
            $attributes[$this->_namespace . '-multiple'] = strtolower(Inflector::slug($this->_model->name . ' ' . $fieldName));
        }
        return $attributes;
    }

/**
 * Inspects field rules and adds proper Parsley attributes.
 * 
 * @param string $field
 * @param array $rules
 * @param array $options
 * @return array
 */
    protected function _applyParsley($field, $rules, $options = array()) {
        $parsleyRules = array();
        if ($this->_isRequiredField($rules)) {
            $parsleyRule = $this->_addRequiredValidation(null, $options);
            $parsleyRule['message'] = 'required';
            $parsleyRules[] = $parsleyRule;
        }
        
        foreach ($rules as $name => $rule) {
            $rule->rule = (array) $rule->rule;
            $ruleName = $rule->rule[0];
            
            $methodName = '_add' . ucfirst($ruleName) . 'Validation';
            if (method_exists($this, $methodName)) {
                $parsleyRule = $this->$methodName($rule, $options);
                $parsleyRule['message'] = $this->_getValidationMessage($name, $rule);
                $parsleyRules[] = $parsleyRule;
            }
        }
        
        foreach ($parsleyRules as $rule) {
            $attr = $this->_namespace . '-' . $rule['rule'];
            $options[$attr] = $rule['value'];            
            $options[$attr . '-message'] = $rule['message'];
        }
        
        return $options;
    }

/**
 * Adds required validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addRequiredValidation($rule) {
        return array(
            'rule' => 'required',
            'value' => 'true',
        );
    }

/**
 * Adds not empty validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addNotEmptyValidation($rule) {
        return $this->_addRequiredValidation($rule);
    }
    
/**
 * Adds alphanumeric validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addAlphaNumericValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'alphanum',
        );
    }
     
/**
 * Adds decimal validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds between validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addBetweenValidation($rule) {
        return array(
            'rule' => 'length',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
/**
 * Adds blank validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addBlankValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^\s*$',
        );
    }
    
/**
 * Adds boolean validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addBooleanValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^(false|true|0|1)$',
        );
    }
    
/**
 * Adds comparison validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds equal to validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addEqualToValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^' . $check2 . '$',
        );
    }
    
/**
 * Adds compare fields validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addCompareFieldsValidation($rule) {
        return array(
            'rule' => 'equalto',
            'value' => sprintf('[name=\'data[%s][%s]\']', $modelName, $rule->rule[1]),
        );
    }
    
/**
 * Adds custom regex validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addCustomValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => $rule->rule[1],
        );
    }

/**
 * Adds date validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addDateValidation($rule) {
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . '$',
        );
    }
    
/**
 * Adds time validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addTimeValidation($rule) {
        return array(
            'rule' => 'pattern',
            'value' => '^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$',
        );
        
    }

/**
 * Adds datetime validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addDatetimeValidation($rule) {
        $timeRegex = '(((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))|([01]\d|2[0-3])(:[0-5]\d){0,2})';
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . ' ' . $timeRegex . '$',
        );
        
    }
    
/**
 * Adds email validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addEmailValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'email',
        );
    }
    
/**
 * Adds ip validation.
 * 
 * @param object $rule
 * @return array
 */
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

/**
 * Adds money validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds max length validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addMaxLengthValidation($rule) {
        return array(
            'rule' => 'maxlength',
            'value' => $rule->rule[1],
        );
    }
    
/**
 * Adds required validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addMinLengthValidation($rule) {
        return array(
            'rule' => 'minlength',
            'value' => $rule->rule[1],
        );
    }
    
/**
 * Adds phone number validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds postal code validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds range validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addRangeValidation($rule) {
        return array(
            'rule' => 'range',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
/**
 * Adds ssn validation.
 * 
 * @param object $rule
 * @return array
 */
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
    
/**
 * Adds url validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addUrlValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'url',
        );
    }
    
/**
 * Adds numeric validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addNumericValidation($rule) {
        return array(
            'rule' => 'type',
            'value' => 'number',
        );
    }
    
/**
 * Adds natural number validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addNaturalNumberValidation($rule) {
        $allowZero = isset($rule->rule[1]) ? $rule->rule[1] : false;
        return array(
            'rule' => 'pattern',
            'value' => $allowZero ? '^(?:0|[1-9][0-9]*)$' : '^[1-9][0-9]*$',
        );
    }
    
/**
 * Adds uuid validation.
 * 
 * @param object $rule
 * @return array
 */
    public function _addUuidValidation($rule) {
        $regex = '^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[0-5][a-fA-F0-9]{3}-[089aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}$';
        return array(
            'rule' => 'pattern',
            'value' => $regex,
        );
    }

/**
 * Returns date regex based on format.
 * 
 * @param string $format
 * @return string
 */
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
 * Returns rule validation message.
 * 
 * @param string $name
 * @param object $rule
 * @return string
 */
    protected function _getValidationMessage($name, $rule) {
        $validationDomain = $this->_model->validationDomain;
        $message = $rule->message;

        if ($message !== null) {
            $args = null;
            if (is_array($message)) {
                $result = $message[0];
                $args = array_slice($message, 1);
            } else {
                $result = $message;
            }
            if (is_array($rule->rule) && $args === null) {
                $args = array_slice($rule->rule, 1);
            }

            foreach ((array)$args as $k => $arg) {
                if (is_string($arg)) {
                    $args[$k] = __d($validationDomain, $arg);
                }
            }

            $message = __d($validationDomain, $result, $args);
        } elseif (is_string($name)) {
            if (is_array($rule->rule)) {
                $args = array_slice($rule->rule, 1);

                foreach ((array)$args as $k => $arg) {
                    if (is_string($arg)) {
                        $args[$k] = __d($validationDomain, $arg);
                    }
                }

                $message = __d($validationDomain, $name, $args);
            } else {
                $message = __d($validationDomain, $name);
            }
        } else {
            $message = __d('cake', 'This field cannot be left blank');
        }

        return $message;
    }

/**
 * Returns if a field is required.
 *
 * @param CakeValidationSet $validationRules
 * @return boolean
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
 * @param string $name
 * @param array $options
 * @param mixed $default
 * @return mixed
 */
    protected function _extractOption($name, $options, $default = null) {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }
        return $default;
    }
}
