<?php
/**
 * ParsleyFormTraitTest file
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice
 *
 * @copyright     Copyright (c) Codaxis (http://codaxis.com)
 * @author        augusto-cdxs (https://github.com/augusto-cdxs/
 * @link          https://github.com/Codaxis/parsley-helper
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('FormHelper', 'View/Helper');
App::uses('ParsleyFormHelperTest', 'ParsleyHelper.Test/Case/View/Helper');
App::uses('ParsleyFormTrait', 'ParsleyHelper.View/Helper');

/**
 * Form helper with trait for testing
 */
class ParsleyFormHelperWithTrait extends FormHelper {
    use ParsleyFormTrait;
}

/**
 * FormHelperTest class
 * Extends ParsleyFormHelperTest to run same tests.
 */
class ParsleyFormTraitTest extends ParsleyFormHelperTest {

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

		$this->Form = new ParsleyFormHelperWithTrait($this->View);
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
}
