Parsley.js Helper
===========

CakePHP Form Helper for Parsley.js automatic validation integration - v0.9

This helper will automatically read validation rules from active form model and assign field attributes accordingly.

Compatible with Cake 2.x

[![Build Status](https://travis-ci.org/Codaxis/parsley-helper.svg?branch=master)](https://travis-ci.org/Codaxis/parsley-helper)


Basic usage
----------

1. Enable the helper plugin in your app/Config/bootstrap.php by doing ```CakePlugin::load('BoostCake');``` - or just ```CakePlugin::loadAll();```.

2. Load helper in your ```app/Controller/AppController.php```. You can use the classname option if you want to keep your helper alias as "Form".

	```php
	// In AppController.php

	public $helpers = array('ParsleyHelper.ParsleyForm');
	// or
	public $helpers = array('Form' => array('className' => 'ParsleyHelper.ParsleyForm'));
	```

3. Enable Parsley rules integration in any form by setting ```parsley => true``` or ```data-parsley-validate => true``` in Form->create() options array.

	```php
	echo $this->Form->create('MyModel', array('parsley' => true));
	```
	
4. That's all! When you create an input field, parsley attributes will be set according to the defined validation rules.

Trait usage
-----------

If you are running PHP 5.4 or greater, and already using a custom or vendor form helper, you can make use of provided ```ParsleyFormTrait``` and retain both helper functionalities. You can do so by creating a custom helper in your ```app/View/Helper``` folder like this:

```php
// In app/View/Helper

App::uses('ParsleyFormTrait', 'ParsleyHelper.View/Helper');

class MyFormHelper extends VendorFormHelper {
	use ParsleyFormTrait;
}
```