<?php
/**
 * FormHelperTest file
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
App::uses('Security', 'Utility');
App::uses('CakeRequest', 'Network');
App::uses('HtmlHelper', 'View/Helper');
App::uses('FormHelper', 'View/Helper');
App::uses('Router', 'Routing');

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
 * Default schema
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'name' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'email' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'phone' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'password' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'published' => array('type' => 'date', 'null' => true, 'default' => null, 'length' => null),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null),
		'age' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => null)
	);

/**
 * validate property
 *
 * @var array
 */
	public $validate = array(
        'required' => 'notEmpty',
		'im_required' => array(
		  'rule' => array('between', 5, 30), 
		  'allowEmpty' => false
        ),
        'im_alphanumeric' => array(
            'rule' => 'alphaNumeric',
        ),
		'im_blank' => array(
		    'rule' => 'blank', 
        ),
        'im_email' => array(
            'rule' => 'email', 
        ),
	);

/**
 * schema method
 *
 * @return void
 */
	public function setSchema($schema) {
		$this->_schema = $schema;
	}

/**
 * hasAndBelongsToMany property
 *
 * @var array
 */
	public $hasAndBelongsToMany = array('ContactTag' => array('with' => 'ContactTagsContact'));

/**
 * hasAndBelongsToMany property
 *
 * @var array
 */
	public $belongsTo = array('User' => array('className' => 'UserForm'));
}

/**
 * ContactTagsContact class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ContactTagsContact extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * Default schema
 *
 * @var array
 */
	protected $_schema = array(
		'contact_id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'contact_tag_id' => array(
			'type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'
		)
	);

/**
 * schema method
 *
 * @return void
 */
	public function setSchema($schema) {
		$this->_schema = $schema;
	}

}

/**
 * ContactNonStandardPk class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ContactNonStandardPk extends Contact {

/**
 * primaryKey property
 *
 * @var string
 */
	public $primaryKey = 'pk';

/**
 * schema method
 *
 * @return void
 */
	public function schema($field = false) {
		$this->_schema = parent::schema();
		$this->_schema['pk'] = $this->_schema['id'];
		unset($this->_schema['id']);
		return $this->_schema;
	}

}

/**
 * ContactTag class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ContactTag extends Model {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * schema definition
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => '', 'length' => '8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => '', 'length' => '255'),
		'created' => array('type' => 'date', 'null' => true, 'default' => '', 'length' => ''),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => '', 'length' => null)
	);
}

/**
 * UserForm class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class UserForm extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * hasMany property
 *
 * @var array
 */
	public $hasMany = array(
		'OpenidUrl' => array('className' => 'OpenidUrl', 'foreignKey' => 'user_form_id'
	));

/**
 * schema definition
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'published' => array('type' => 'date', 'null' => true, 'default' => null, 'length' => null),
		'other' => array('type' => 'text', 'null' => true, 'default' => null, 'length' => null),
		'stuff' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 10),
		'something' => array('type' => 'string', 'null' => true, 'default' => null, 'length' => 255),
		'active' => array('type' => 'boolean', 'null' => false, 'default' => false),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);
}

/**
 * OpenidUrl class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class OpenidUrl extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * belongsTo property
 *
 * @var array
 */
	public $belongsTo = array('UserForm' => array(
		'className' => 'UserForm', 'foreignKey' => 'user_form_id'
	));

/**
 * validate property
 *
 * @var array
 */
	public $validate = array('openid_not_registered' => array());

/**
 * schema method
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'user_form_id' => array(
			'type' => 'user_form_id', 'null' => '', 'default' => '', 'length' => '8'
		),
		'url' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
	);

/**
 * beforeValidate method
 *
 * @return void
 */
	public function beforeValidate($options = array()) {
		$this->invalidate('openid_not_registered');
		return true;
	}

}

/**
 * ValidateUser class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ValidateUser extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * hasOne property
 *
 * @var array
 */
	public $hasOne = array('ValidateProfile' => array(
		'className' => 'ValidateProfile', 'foreignKey' => 'user_id'
	));

/**
 * schema method
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'name' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'email' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'balance' => array('type' => 'float', 'null' => false, 'length' => '5,2'),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);

/**
 * beforeValidate method
 *
 * @return void
 */
	public function beforeValidate($options = array()) {
		$this->invalidate('email');
		return false;
	}

}

/**
 * ValidateProfile class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ValidateProfile extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * schema property
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'user_id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'full_name' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'city' => array('type' => 'string', 'null' => '', 'default' => '', 'length' => '255'),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);

/**
 * hasOne property
 *
 * @var array
 */
	public $hasOne = array('ValidateItem' => array(
		'className' => 'ValidateItem', 'foreignKey' => 'profile_id'
	));

/**
 * belongsTo property
 *
 * @var array
 */
	public $belongsTo = array('ValidateUser' => array(
		'className' => 'ValidateUser', 'foreignKey' => 'user_id'
	));

/**
 * beforeValidate method
 *
 * @return void
 */
	public function beforeValidate($options = array()) {
		$this->invalidate('full_name');
		$this->invalidate('city');
		return false;
	}

}

/**
 * ValidateItem class
 *
 * @package       Cake.Test.Case.View.Helper
 */
class ValidateItem extends CakeTestModel {

/**
 * useTable property
 *
 * @var boolean
 */
	public $useTable = false;

/**
 * schema property
 *
 * @var array
 */
	protected $_schema = array(
		'id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'profile_id' => array('type' => 'integer', 'null' => '', 'default' => '', 'length' => '8'),
		'name' => array('type' => 'text', 'null' => '', 'default' => '', 'length' => '255'),
		'description' => array(
			'type' => 'string', 'null' => '', 'default' => '', 'length' => '255'
		),
		'created' => array('type' => 'date', 'null' => '1', 'default' => '', 'length' => ''),
		'updated' => array('type' => 'datetime', 'null' => '1', 'default' => '', 'length' => null)
	);

/**
 * belongsTo property
 *
 * @var array
 */
	public $belongsTo = array('ValidateProfile' => array('foreignKey' => 'profile_id'));

/**
 * beforeValidate method
 *
 * @return void
 */
	public function beforeValidate($options = array()) {
		$this->invalidate('description');
		return false;
	}

}

/**
 * FormHelperTest class
 *
 * @package       Cake.Test.Case.View.Helper
 * @property FormHelper $Form
 */
class FormHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		Configure::write('Config.language', 'eng');
		Configure::write('App.base', '');
		Configure::delete('Asset');
		$this->Controller = new ContactTestController();
		$this->View = new View($this->Controller);

        App::uses('ParsleyFormHelper', 'CakeParsley.View/Helper');
		$this->Form = new ParsleyFormHelper($this->View);
			$this->Form->Html = new HtmlHelper($this->View);
			$this->Form->request = new CakeRequest('contacts/add', false);
			$this->Form->request->here = '/contacts/add';
			$this->Form->request['action'] = 'add';
			$this->Form->request->webroot = '';
			$this->Form->request->base = '';
	
			ClassRegistry::addObject('Contact', new Contact());
		/*ClassRegistry::addObject('ContactNonStandardPk', new ContactNonStandardPk());
		ClassRegistry::addObject('OpenidUrl', new OpenidUrl());
		ClassRegistry::addObject('User', new UserForm());
		ClassRegistry::addObject('ValidateItem', new ValidateItem());
		ClassRegistry::addObject('ValidateUser', new ValidateUser());
		ClassRegistry::addObject('ValidateProfile', new ValidateProfile());

		$this->oldSalt = Configure::read('Security.salt');*/

		$this->dateRegex = array(
			'daysRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
			'monthsRegex' => 'preg:/(?:<option value="[\d]+">[\w]+<\/option>[\r\n]*)*/',
			'yearsRegex' => 'preg:/(?:<option value="([\d]+)">\\1<\/option>[\r\n]*)*/',
			'hoursRegex' => 'preg:/(?:<option value="0?([\d]+)">\\1<\/option>[\r\n]*)*/',
			'minutesRegex' => 'preg:/(?:<option value="([\d]+)">0?\\1<\/option>[\r\n]*)*/',
			'meridianRegex' => 'preg:/(?:<option value="(am|pm)">\\1<\/option>[\r\n]*)*/',
		);
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
 * test the create() method
 *
 * @return void
 */
    public function testInputBlank() {
        //$this->Form->create('Contact', array(
        //    'parsley' => true
        //));
        
		set_time_limit(10);
        $result = $this->Form->text('im_required');
        $expected = array(
            'input' => array(
                '1' => 'A', 
                '2' => 'B', 
                '3' => 'C',
                '4' => 'D',
                '5' => 'E',
                '6' => 'F',
                '7' => 'G', 
                '8' => 'H', 
            ),
            '1' => 'A', 
            '2' => 'A', 
            '3' => 'A', 
            '4' => 'A', 
            '5' => 'A', 
            '6' => 'A', 
            '7' => 'A', 
            '8' => 'A', 
            '9' => 'A', 
        );
        $this->assertTags($result, $expected);
		dd(' ok ');
        $expected = array(
            'input' => array(
                'type' => 'text', 
                'name' => 'data[Contact][im_required]', 
                'id' => 'ContactImRequired',
                'required' => 'required', 
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'required', 
                'data-parsley-range' => '[5,30]',
                'data-parsley-range-message' => 'between',
            ),
        );
		vd($expected);
    }

/**
 * test the create() method
 *
 * @return void
 */
    public function testCreate() {
    	$this->skipIf(true);
        $result = $this->Form->create('Contact', array(
            'data-parsley-validate' => true
        ));
        $encoding = strtolower(Configure::read('App.encoding'));
        $expected = array(
            'form' => array(
                'id' => 'ContactAddForm', 'method' => 'post', 'action' => '/contacts/add',
                'accept-charset' => $encoding,
                'data-parsley-validate' => 1,
                'novalidate' => 'novalidate'
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
                'id' => 'ContactAddForm', 'method' => 'post', 'action' => '/contacts/add',
                'accept-charset' => $encoding,
                'data-parsley-validate' => 1,
                'novalidate' => 'novalidate'
            ),
            'div' => array('style' => 'preg:/display\s*\:\s*none;\s*/'),
            'input' => array('type' => 'hidden', 'name' => '_method', 'value' => 'POST'),
            '/div'
        );
        $this->assertTags($result, $expected);
    }

/**
 * test the create() method
 *
 * @return void
 */
    public function testInputRequired() {
    	$this->skipIf(true);
        $this->Form->create('Contact', array(
            'parsley' => true
        ));
        
        $result = $this->Form->text('im_required');
        $expected = array(
            'input' => array(
                'type' => 'text', 
                'name' => 'data[Contact][imrequired]', 
                'id' => 'ContactImrequired',
                'required' => 'required', 
                'data-parsley-required' => 'true',
                'data-parsley-required-message' => 'required', 
                'data-parsley-range' => '[5,30]',
                'data-parsley-range-message' => 'between'
            ),
        );
        $this->assertTags($result, $expected);
    }
}
