<?php
/**
 * ParsleyFormHelperTest file
 *
 * CakePHP(tm) Tests <http://book.cakephp.org/2.0/en/development/testing.html>
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://book.cakephp.org/2.0/en/development/testing.html CakePHP(tm) Tests
 * @package       Cake.Test.Case.View.Helper
 * @since         CakePHP(tm) v 1.2.0.4206
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */


App::uses('ClassRegistry', 'Utility');
App::uses('Controller', 'Controller');
App::uses('View', 'View');
App::uses('Model', 'Model');
App::uses('FormHelper', 'View/Helper');
App::uses('HtmlHelper', 'View/Helper');

App::uses('ParsleyFormHelper', 'ParsleyJsHelper.View/Helper');


/**
 * ContactTestController class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ContactTestController extends Controller {

/**
 * uses property
 *
 * @var mixed null
 */
    public $uses = null;
}

/**
 * Contact class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class Contact extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * validate property
 *
 * @var array
 */
    public $validate = array(
        'im_not_empty' => array(
            'rule' => 'notEmpty',
            'message' => 'Must not be empty!'
        ),
        
        'im_alphanumeric_required' => array(
            'rule' => 'alphaNumeric',
            'message' => 'Not a valid alphanumeric value!'
        ),
        
        'im_alphanumeric' => array(
            'rule' => 'alphaNumeric',
            'message' => 'Not a valid alphanumeric value!',
            'allowEmpty' => true
        ),
        
        'im_between' => array(
            'rule' => array('between', 5, 10),
            'message' => 'Must be between 5 and 10 chars!',
            'allowEmpty' => true
        ),
        
        'im_blank' => array(
            'rule' => 'blank',
            'message' => 'Not a valid blank value!',
            'allowEmpty' => true, 
        ),
        
        'im_boolean' => array(
            'rule' => 'boolean', 
            'message' => 'Not a valid boolean value!',
            'allowEmpty' => true, 
        ),
        
        'im_decimal' => array(
            'rule' => 'decimal',
            'message' => 'Not a valid decimal value!',
            'allowEmpty' => true
        ),
        
        'im_greater_than' => array(
            'rule' => array('comparison', '>', 10),
            'message' => 'Must be greater than 10!',
            'allowEmpty' => true, 
        ),
        
        'im_less_than' => array(
            'rule' => array('comparison', '<', 5),
            'message' => 'Must be less than 5!',
            'allowEmpty' => true, 
        ),
        
        'im_greater_or_equal_than' => array(
            'rule' => array('comparison', '>=', 10),
            'message' => 'Must be greater or equal to 10!',
            'allowEmpty' => true, 
        ),
        
        'im_less_or_equal_than' => array(
            'rule' => array('comparison', '<=', 5),
            'message' => 'Must be less or equal to 5!',
            'allowEmpty' => true, 
        ),
        
        'im_equal_to' => array(
            'rule' => array('comparison', '==', 'WORD'),
            'message' => 'Must be equal to WORD!',
            'allowEmpty' => true, 
        ),
        
        'im_not_equal_to' => array(
            'rule' => array('comparison', '!=', 'WORD'),
            'message' => 'Must be not equal to WORD!',
            'allowEmpty' => true, 
        ),
        
        'im_custom' => array(
            'rule' => array('custom', '^[A-Z][0-9]$'),
            'message' => 'Must match an uppercase letter followed by a digit!',
            'allowEmpty' => true, 
        ),
        
        'im_date' => array(
            'rule' => array('date'),
            'message' => 'Must be a date!',
            'allowEmpty' => true, 
        ),
        
        'im_time' => array(
            'rule' => array('time'),
            'message' => 'Must be a time!',
            'allowEmpty' => true, 
        ),
        
        'im_datetime' => array(
            'rule' => array('datetime'),
            'message' => 'Must be a datetime!',
            'allowEmpty' => true, 
        ),
        
        'im_email' => array(
            'rule' => 'email',
            'message' => 'Not a valid email value!',
            'allowEmpty' => true, 
        ),
        
        'im_ip' => array(
            'rule' => 'ip', 
            'message' => 'Not a valid ip value!',
            'allowEmpty' => true, 
        ),
        
        'im_money' => array(
            'rule' => 'money', 
            'message' => 'Not a valid money value!',
            'allowEmpty' => true, 
        ),
        
        'im_maxlength' => array(
            'rule' => array('maxLength', 10), 
            'message' => 'Must be less than 10 characters!',
            'allowEmpty' => true, 
        ),
        
        'im_minlength' => array(
            'rule' => array('minLength', 5), 
            'message' => 'Must be more than 5 characters!',
            'allowEmpty' => true, 
        ),
        
        'im_phone' => array(
            'rule' => array('phone'), 
            'message' => 'Not a valid phone value!',
            'allowEmpty' => true, 
        ),
        
        'im_postal' => array(
            'rule' => array('postal'), 
            'message' => 'Not a valid postal value!',
            'allowEmpty' => true, 
        ),
        
        'im_range' => array(
            'rule' => array('range', 5, 10), 
            'message' => 'Not a valid value in the range [5,10]!',
            'allowEmpty' => true, 
        ),
        
        'im_url' => array(
            'rule' => array('url'), 
            'message' => 'Not a valid URL value!',
            'allowEmpty' => true, 
        ),
        
        'im_numeric' => array(
            'rule' => array('numeric'), 
            'message' => 'Not a valid numeric value!',
            'allowEmpty' => true, 
        ),
        
        'im_natural_number' => array(
            'rule' => array('naturalNumber'), 
            'message' => 'Not a valid natural number value!',
            'allowEmpty' => true, 
        ),
        
        'im_uuid' => array(
            'rule' => array('uuid'), 
            'message' => 'Not a valid UUID value!',
            'allowEmpty' => true, 
        ),

        'im_multiple' => array(
            'rule1' => array(
                'rule' => array('numeric'), 
                'message' => 'Not a valid numeric value!',
                'allowEmpty' => true,
            ),
            'rule2' => array(
                'rule' => array('comparison', '>=', 10),
                'message' => 'Must be greater or equal to 10!',
                'allowEmpty' => true,
            ),
        ),

        'im_default_message' => array(
            'rule' => array('notEmpty'), 
        ),

        'im_rule_name_message' => array(
            'my_rule_name' => array(
                'rule' => 'notEmpty'
            ),
        ),

        'im_translated' => array(
            'rule' => array('notEmpty'), 
            'message' => 'Not localized error message.',
        ),
    );
}

/**
 * FormHelperTest class
 */
class ParsleyFormHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		Configure::write('Config.language', 'eng');
		Configure::write('App.base', '');
		$this->Controller = new ContactTestController();
		$this->View = new View($this->Controller);

		$this->Form = new ParsleyFormHelper($this->View);
		$this->Form->Html = new HtmlHelper($this->View);
		$this->Form->request = new CakeRequest('contacts/add', false);
		$this->Form->request->here = '/contacts/add';
		$this->Form->request['action'] = 'add';
		$this->Form->request->webroot = '';
		$this->Form->request->base = '';

        $this->dateRegex = array(
            'daysRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
            'monthsRegex' => 'preg:/(?:<option value="[\d]+">[\w]+<\/option>[\r\n]*)*/',
            'yearsRegex' => 'preg:/(?:<option value="([\d]+)">\\1<\/option>[\r\n]*)*/',
            'hoursRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
            'minutesRegex' => 'preg:/(?:<option value="([\d]+)">0?\\1<\/option>[\r\n]*)*/',
            'meridianRegex' => 'preg:/(?:<option value="(am|pm)">\\1<\/option>[\r\n]*)*/',
        );
	
		ClassRegistry::addObject('Contact', new Contact());
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Form->Html, $this->Form, $this->Controller, $this->View);
		//Configure::write('Security.salt', $this->oldSalt);
	}

/**
 * @return void
 */
    public function testCreate() {
        $result = $this->Form->create('Contact', array(
            'data-parsley-validate' => true
        ));
        $encoding = strtolower(Configure::read('App.encoding'));
        $expected = array(
            'form' => array(
                'action' => '/contacts/add', 'data-parsley-validate' => 1, 'novalidate' => 'novalidate',
                'id' => 'ContactAddForm', 'method' => 'post', 'accept-charset' => $encoding,
            ),
            'div' => array('style' => 'preg:/display\s*\:\s*none;\s*/'),
            'input' => array('type' => 'hidden', 'name' => '_method', 'value' => 'POST'),
            '/div'
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->create('Contact', array(
            'parsley' => true
        ));
        $encoding = strtolower(Configure::read('App.encoding'));
        $expected = array(
            'form' => array(
                'action' => '/contacts/add', 'data-parsley-validate' => 1, 'novalidate' => 'novalidate',
                'id' => 'ContactAddForm', 'method' => 'post', 'accept-charset' => $encoding,
            ),
            'div' => array('style' => 'preg:/display\s*\:\s*none;\s*/'),
            'input' => array('type' => 'hidden', 'name' => '_method', 'value' => 'POST'),
            '/div'
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testNotEmpty() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_not_empty');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_not_empty]',
                'type' => 'text',
                'id' => 'ContactImNotEmpty',
                'required' => 'required', 
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'Must not be empty!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testAlphanumericRequired() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_alphanumeric_required');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_alphanumeric_required]',
                'type' => 'text',
                'id' => 'ContactImAlphanumericRequired',
                'required' => 'required',
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'required',
                'data-parsley-type' => 'alphanum',
                'data-parsley-type-message' => 'Not a valid alphanumeric value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testAlphanumeric() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_alphanumeric');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_alphanumeric]',
                'type' => 'text',
                'id' => 'ContactImAlphanumeric',
                'data-parsley-type' => 'alphanum',
                'data-parsley-type-message' => 'Not a valid alphanumeric value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testBetween() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_between');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_between]',
                'type' => 'text',
                'id' => 'ContactImBetween',
                'data-parsley-length' => '[5,10]',
                'data-parsley-length-message' => 'Must be between 5 and 10 chars!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testBlank() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_blank');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_blank]',
                'type' => 'text',
                'id' => 'ContactImBlank',
                'data-parsley-pattern' => '^\s*$',
                'data-parsley-pattern-message' => 'Not a valid blank value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testBoolean() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_boolean');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_boolean]',
                'type' => 'text',
                'id' => 'ContactImBoolean',
                'data-parsley-pattern' => '^(false|true|0|1)$',
                'data-parsley-pattern-message' => 'Not a valid boolean value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testDecimal() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_decimal');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_decimal]',
                'type' => 'text',
                'id' => 'ContactImDecimal',
                'data-parsley-pattern' => '/^[+-]?(?:[0-9]+|[0-9]*[\.][0-9]+)(?:[eE][+-]?[0-9]+)?$/',
                'data-parsley-pattern-message' => 'Not a valid decimal value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testComparison() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_greater_than');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_greater_than]',
                'type' => 'text',
                'id' => 'ContactImGreaterThan',
                'data-parsley-min' => '11',
                'data-parsley-min-message' => 'Must be greater than 10!',
            ),
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->text('im_less_than');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_less_than]',
                'type' => 'text',
                'id' => 'ContactImLessThan',
                'data-parsley-max' => '4',
                'data-parsley-max-message' => 'Must be less than 5!',
            ),
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->text('im_greater_or_equal_than');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_greater_or_equal_than]',
                'type' => 'text',
                'id' => 'ContactImGreaterOrEqualThan',
                'data-parsley-min' => '10',
                'data-parsley-min-message' => 'Must be greater or equal to 10!',
            ),
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->text('im_less_or_equal_than');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_less_or_equal_than]',
                'type' => 'text',
                'id' => 'ContactImLessOrEqualThan',
                'data-parsley-max' => '5',
                'data-parsley-max-message' => 'Must be less or equal to 5!',
            ),
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->text('im_equal_to');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_equal_to]',
                'type' => 'text',
                'id' => 'ContactImEqualTo',
                'data-parsley-pattern' => '^WORD$',
                'data-parsley-pattern-message' => 'Must be equal to WORD!',
            ),
        );
        $this->assertTags($result, $expected);
        
        $result = $this->Form->text('im_not_equal_to');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_not_equal_to]',
                'type' => 'text',
                'id' => 'ContactImNotEqualTo',
                'data-parsley-pattern' => '^(?!^WORD$).*',
                'data-parsley-pattern-message' => 'Must be not equal to WORD!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testCustom() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_custom');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_custom]',
                'type' => 'text',
                'id' => 'ContactImCustom',
                'data-parsley-pattern' => '^[A-Z][0-9]$',
                'data-parsley-pattern-message' => 'Must match an uppercase letter followed by a digit!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testDate() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_date');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_date]',
                'type' => 'text',
                'id' => 'ContactImDate',
                'data-parsley-pattern' => '^(?:(?:(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))([- /.])(?:0?2\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\d)?\d{2})([- /.])(?:(?:(?:0?[13578]|1[02])\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\2(?:0?[1-9]|1\d|2[0-8]))))$',
                'data-parsley-pattern-message' => 'Must be a date!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testTime() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_time');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_time]',
                'type' => 'text',
                'id' => 'ContactImTime',
                'data-parsley-pattern' => '^((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))$|^([01]\d|2[0-3])(:[0-5]\d){0,2}$',
                'data-parsley-pattern-message' => 'Must be a time!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testDatetime() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_datetime');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_datetime]',
                'type' => 'text',
                'id' => 'ContactImDatetime',
                'data-parsley-pattern' => '^(?:(?:(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))([- /.])(?:0?2\1(?:29)))|(?:(?:(?:1[6-9]|[2-9]\d)?\d{2})([- /.])(?:(?:(?:0?[13578]|1[02])\2(?:31))|(?:(?:0?[1,3-9]|1[0-2])\2(29|30))|(?:(?:0?[1-9])|(?:1[0-2]))\2(?:0?[1-9]|1\d|2[0-8])))) (((0?[1-9]|1[012])(:[0-5]\d){0,2} ?([AP]M|[ap]m))|([01]\d|2[0-3])(:[0-5]\d){0,2})$',
                'data-parsley-pattern-message' => 'Must be a datetime!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testEmail() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_email');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_email]',
                'type' => 'text',
                'id' => 'ContactImEmail',
                'data-parsley-type' => 'email',
                'data-parsley-type-message' => 'Not a valid email value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testIp() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_ip');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_ip]',
                'type' => 'text',
                'id' => 'ContactImIp',
                'data-parsley-pattern' => '^(((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)|((?=.*::)(?!.*::.+::)(::)?([\dA-F]{1,4}:(:|\b)|){5}|([\dA-F]{1,4}:){6})((([\dA-F]{1,4}((?!\3)::|:\b|$))|(?!\2\3)){2}|(((2[0-4]|1\d|[1-9])?\d|25[0-5])\.?\b){4}))$/i',
                'data-parsley-pattern-message' => 'Not a valid ip value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testMoney() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_money');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_money]',
                'type' => 'text',
                'id' => 'ContactImMoney',
                'data-parsley-pattern' => '/^(?!\u00a2)\$?(?!0,?\d)(?:\d{1,3}(?:([, .])\d{3})?(?:\1\d{3})*|(?:\d+))((?!\1)[,.]\d{1,2})?$/',
                'data-parsley-pattern-message' => 'Not a valid money value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testLength() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_maxlength');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_maxlength]',
                'type' => 'text',
                'id' => 'ContactImMaxlength',
                'data-parsley-maxlength' => '10',
                'data-parsley-maxlength-message' => 'Must be less than 10 characters!',
            ),
        );
        $this->assertTags($result, $expected);

        $result = $this->Form->text('im_minlength');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_minlength]',
                'type' => 'text',
                'id' => 'ContactImMinlength',
                'data-parsley-minlength' => '5',
                'data-parsley-minlength-message' => 'Must be more than 5 characters!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testPhone() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_phone');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_phone]',
                'type' => 'text',
                'id' => 'ContactImPhone',
                'data-parsley-pattern' => '/^(?:(?:\+?1\s*(?:[.-]\s*)?)?(?:\(\s*(?![2-9]11)(?!555)([2-9][0-8][0-9])\s*\)|(?![2-9]11)(?!555)([2-9][0-8][0-9]))\s*(?:[.-]\s*)?)(?!(555(?:\s*(?:[.\-\s]\s*))(01([0-9][0-9])|1212)))(?!(555(01([0-9][0-9])|1212)))([2-9]1[02-9]|[2-9]1[02-9]|[2-9][02-9]1|[2-9][02-9]{2})\s*(?:[.-]\s*)?([0-9]{4})(?:\s*(?:#|x\.?|ext\.?|extension)\s*(\d+))?$/',
                'data-parsley-pattern-message' => 'Not a valid phone value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testPostal() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_postal');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_postal]',
                'type' => 'text',
                'id' => 'ContactImPostal',
                'data-parsley-pattern' => '/\\A\\b[0-9]{5}(?:-[0-9]{4})?\\b\\z/i',
                'data-parsley-pattern-message' => 'Not a valid postal value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testUrl() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_url');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_url]',
                'type' => 'text',
                'id' => 'ContactImUrl',
                'data-parsley-type' => 'url',
                'data-parsley-type-message' => 'Not a valid URL value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testUuid() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_uuid');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_uuid]',
                'type' => 'text',
                'id' => 'ContactImUuid',
                'data-parsley-pattern' => '^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[0-5][a-fA-F0-9]{3}-[089aAbB][a-fA-F0-9]{3}-[a-fA-F0-9]{12}$',
                'data-parsley-pattern-message' => 'Not a valid UUID value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testNumeric() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_numeric');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_numeric]',
                'type' => 'text',
                'id' => 'ContactImNumeric',
                'data-parsley-type' => 'number',
                'data-parsley-type-message' => 'Not a valid numeric value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testNaturalNumber() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_natural_number');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_natural_number]',
                'type' => 'text',
                'id' => 'ContactImNaturalNumber',
                'data-parsley-pattern' => '^[1-9][0-9]*$',
                'data-parsley-pattern-message' => 'Not a valid natural number value!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * @return void
 */
    public function testRange() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_range');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_range]',
                'type' => 'text',
                'id' => 'ContactImRange',
                'data-parsley-range' => '[5,10]',
                'data-parsley-range-message' => 'Not a valid value in the range [5,10]!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test data-parsley-multiple attr is added to date/time inputs
 *
 * @return void
 */
    public function testDateInput() {
        extract($this->dateRegex);

        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->dateTime('im_date', 'DMY', null, array('empty' => false));
        $now = strtotime('now');

        $expected = array(
            array('select' => array('name' => 'data[Contact][im_date][day]', 'data-parsley-multiple' => 'contact_im_date', 'id' => 'ContactImDateDay')),
            $daysRegex,
            array('option' => array('value' => date('d', $now), 'selected' => 'selected')),
            date('j', $now),
            '/option',
            '*/select',
            '-',
            array('select' => array('name' => 'data[Contact][im_date][month]', 'data-parsley-multiple' => 'contact_im_date', 'id' => 'ContactImDateMonth')),
            $monthsRegex,
            array('option' => array('value' => date('m', $now), 'selected' => 'selected')),
            date('F', $now),
            '/option',
            '*/select',
            '-',
            array('select' => array('name' => 'data[Contact][im_date][year]', 'data-parsley-multiple' => 'contact_im_date', 'id' => 'ContactImDateYear')),
            $yearsRegex,
            array('option' => array('value' => date('Y', $now), 'selected' => 'selected')),
            date('Y', $now),
            '/option',
            '*/select',
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test field with multiple rules
 *
 * @return void
 */
    public function testMultipleRules() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_multiple');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_multiple]',
                'type' => 'text',
                'id' => 'ContactImMultiple',
                'data-parsley-type' => 'number',
                'data-parsley-type-message' => 'Not a valid numeric value!',
                'data-parsley-min' => '10',
                'data-parsley-min-message' => 'Must be greater or equal to 10!',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test field with default error message
 *
 * @return void
 */
    public function testDefaultMessage() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_default_message');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_default_message]',
                'type' => 'text',
                'id' => 'ContactImDefaultMessage',
                'required' => 'required',
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'This field cannot be left blank',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test field with no error message that shows rule name by default
 *
 * @return void
 */
    public function testRuleNameMessage() {
        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_rule_name_message');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_rule_name_message]',
                'type' => 'text',
                'id' => 'ContactImRuleNameMessage',
                'required' => 'required',
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'my_rule_name',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test error message translation
 *
 * @return void
 */
    public function testMessageTranslation() {
        Configure::write('Config.language', 'lang');
        App::build(array(
            'Locale' => array(CakePlugin::path('ParsleyJsHelper') . 'Test' . DS . 'test_files' . DS . 'Locale' . DS)
        ));

        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_translated');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_translated]',
                'type' => 'text',
                'id' => 'ContactImTranslated',
                'required' => 'required',
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'Localized error message.',
            ),
        );
        $this->assertTags($result, $expected);
    }

/**
 * Test error message translation with domain
 *
 * @return void
 */
    public function testMessageTranslationWithDomain() {
        Configure::write('Config.language', 'lang');
        App::build(array(
            'Locale' => array(CakePlugin::path('ParsleyJsHelper') . 'Test' . DS . 'test_files' . DS . 'Locale' . DS)
        ));

        $Contact = ClassRegistry::getObject('Contact');
        $Contact->validationDomain = 'mydomain';

        $this->Form->create('Contact', array(
            'parsley' => true
        ));

        $result = $this->Form->text('im_translated');
        $expected = array(
            'input' => array(
                'name' => 'data[Contact][im_translated]',
                'type' => 'text',
                'id' => 'ContactImTranslated',
                'required' => 'required', 
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'Localized error message with validation domain.',
            ),
        );
        $this->assertTags($result, $expected);
    }
}
