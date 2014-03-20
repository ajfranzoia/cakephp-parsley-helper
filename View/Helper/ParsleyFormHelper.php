<?php

App::uses('FormHelper', 'View/Helper');
App::uses('Set', 'Utility');

class ParsleyFormHelper extends FormHelper {

/**
 * @var array
 */
    public $helpers = array(
        'Html'
    );

/**
 * @var boolean
 */
    protected $_processor = null;
    
/**
 * Redefine el metodo padre para aceptar configuraciones globales de forms, definidas en 
 * bootstrap.php o por ej. en beforeRender de helper de app.
 * Configs. disponibles:
 * - Form.formDefaults
 * - Form.inputDefaults
 *
 * TODO: configs separadas por tipo de form
 * 
 * @param mixed $model
 * @param array $options An array of html attributes and options.
 * @return string An formatted opening FORM tag.
 */
    public function create($model = null, $options = array()) {
        //$this->_processor = new ParsleyProcessor($this, $model, $options);
        //App::uses('ParsleyProcessor', 'CakeParsley.Lib');
        //$this->_processor = new ParsleyProcessor();
        
        //$EventManager = CakeEventManager::instance();
        CakeEventManager::instance()->attach($this->_processor);
        
        $event = new CakeEvent('FormHelper.beforeFormCreate', $this, array(
            'model' => $model,
            'options' => $options
        ));
        $this->getEventManager()->dispatch($event);
        
        $options = $event->data['options'];
        return parent::create($model, $options);
    }

/**
 * Adds Parsley data attributes to field options if Parsley is enabled
 * 
 * @param string $field Name of the field to initialize options for.
 * @param array $options Array of options to append options into.
 * @return array Array of options for the input.
 */
    protected function _initInputField($field, $options = array()) {
        $result = parent::_initInputField($field, $options);
        if ($this->_parsleyEnabled) {
            $result = $this->_applyParsley($field, $result);
        }
        return $result;
    }

/**
 * Adds Parsley data attributes to field options if Parsley is enabled
 * 
 * @param string $field Name of the field to initialize options for.
 * @param array $options Array of options to append options into.
 * @return array Array of options for the input.
 */
    protected function _applyParsley($field, $options = array()) {
        $modelName = $this->model();
        $field = $this->field();
        
        if (empty($modelName) || empty($field)) {
            return $options;
        }
        
        $model = $this->_getModel($modelName);
        $validator = $model->validator();
        
        if (!isset($validator[$field])) {
            return $options;
        }
        $fieldRules = $validator[$field];
        
        $parsleyRules = array();
        if ($this->_isRequiredField($fieldRules)) {
            $parsleyRules[] = $this->_addParsleyRequired(null, $options);
        }
        
        foreach ($fieldRules as $rule) {
            if (is_array($rule->rule)) {
                $ruleName = $rule->rule[0];
            } else {
                $ruleName = $rule->rule;
            }
            
            $methodName = '_addParsley' . ucfirst($ruleName);
            if (method_exists($this, $methodName)) {
                $parsleyRule = $this->$methodName($rule, $options);
                
                $message = $originalMessage = empty($rule->message) ? $ruleName : $rule->message;
                if (!empty($model->validationDomain)) {
                    $message = __d($model->validationDomain, $message);
                }
                if (empty($model->validationDomain) || $message == $originalMessage) {
                    $message = __($message);
                }
                
                $parsleyRule['message'] = $message;
            	$parsleyRules[] = $parsleyRule;
            }
        }
        
        foreach ($parsleyRules as $rule) {
            $attr = $this->_parsleyNamespace . '-' . $rule['rule'];
            $options[$attr] = $rule['value'];
            $options[$attr . '-message'] = isset($rule['message']) ? $rule['message'] : $rule['rule'];
        }
        
        return $options;
    }

    public function _addParsleyRequired($rule, $options) {
        return array(
            'rule' => 'required',
            'value' => 'true',
        );
    }

    public function _addParsleyNotEmpty($rule, $options) {
        return $this->_addParsleyRequired($rule, $options);
    }
    
    public function _addParsleyAlphaNumeric($rule, $options) {
        return array(
            'rule' => 'type',
            'value' => 'alphanum',
        );
    }
    
    public function _addParsleyBetween($rule, $options) {
        return array(
            'rule' => 'length',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
    public function _addParsleyBlank($rule, $options) {
        return array(
            'rule' => 'pattern',
            'value' => '^\s*$',
        );
    }
    
    public function _addParsleyBoolean($rule, $options) {
        return array(
            'rule' => 'pattern',
            'value' => '^(false|true|0|1)$',
        );
    }
    
    public function _addParsleyComparison($rule, $options) {
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
    
    public function _addParsleyEqualTo($rule, $options) {
        return array(
            'rule' => 'pattern',
            'value' => '^' . $check2 . '$',
        );
    }
    
    public function _addParsleyCompareFields($rule, $options) {
        return array(
            'rule' => 'equalto',
            'value' => sprintf('[name=\'data[%s][%s]\']', $modelName, $rule->rule[1]),
        );
    }
    
    public function _addParsleyCustom($rule, $options) {
        return array(
            'rule' => 'pattern',
            'value' => $rule->rule[1],
        );
    }
    public function _addParsleyDate($rule, $options) {
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . '$',
        );
    }
    
    public function _addParsleyTime($rule, $options) {
        return array(
            'rule' => 'pattern',
            'value' => '^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$',
        );
        
    }

    public function _addParsleyDatetime($rule, $options) {
        $timeRegex = '(((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))|([01]\d|2[0-3])(:[0-5]\d){0,2})';
        $format = empty($rule->rule[1]) ? 'ymd' : $rule->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => '^' . $this->_getDateRegex($format) . ' ' . $timeRegex . '$',
        );
        
    }
    
    public function _addParsleyEmail($rule, $options) {
        return array(
            'rule' => 'type',
            'value' => 'email',
        );
    }
    
    public function _addParsleyIp($rule, $options) {
        $type = $rule->rule[1];
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

    public function _addParsleyMoney($rule, $options) {
        $symbolPosition = $rule->rule[1];
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
    
    public function _addParsleyMaxLength($rule, $options) {
        return array(
            'rule' => 'maxlength',
            'value' => $rule->rule[1],
        );
    }
    
    public function _addParsleyMinLength($rule, $options) {
        return array(
            'rule' => 'minlength',
            'value' => $rule->rule[1],
        );
    }
    
    public function _addParsleyPhone($rule, $options) {
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
    
    public function _addParsleyPostal($rule, $options) {
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
    
    public function _addParsleyRange($rule, $options) {
        return array(
            'rule' => 'range',
            'value' => sprintf('[%s,%s]', $rule->rule[1], $rule->rule[2]),
        );
    }
    
    public function _addParsleySsn($rule, $options) {
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
    
    public function _addParsleyUrl($rule, $options) {
        return array(
            'rule' => 'type',
            'value' => 'url',
        );
    }
    
    public function _addParsleyNumeric($rule, $options) {
        return array(
            'rule' => 'type',
            'value' => 'number',
        );
    }
    
    public function _addParsleyNaturalNumber($rule, $options) {
        $allowZero = $this->rule[1];
        return array(
            'rule' => 'pattern',
            'value' => $allowZero ? '^(?:0|[1-9][0-9]*)$' : '^[1-9][0-9]*$',
        );
    }
    
    public function _addParsleyUuid($rule, $options) {
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
}
