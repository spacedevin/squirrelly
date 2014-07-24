<?php


class ConfigTest extends Tipsy_Test {

	public static function setUpBeforeClass() {
		// create a config file


	}

	public static function tearDownAfterClass() {
		// delete a config file
	}
	
	public function setUp() {
		$this->tip = new Tipsy\Tipsy;
		$this->useOb = true; // for debug use
	}

	public function testConfigInternal() {
		$_REQUEST['__url'] = 'router/basic';
		
		$this->tip->config([
			'db' => [
				'host' => 'blah'
			]
		], true);
		
		$this->ob();

		$this->tip->router()
			->when('router/basic', function() {
				echo 'YES';
			});
		$this->tip->start();
		
		$check = $this->ob(false);
		
		$this->assertTrue($check == 'YES');
	}
	
	public function testConfigFile() {
		$_REQUEST['__url'] = 'router/basic';
		
		$this->tip->config('config.ini');
		print_r($this->tip->config());
		exit;

		
		$this->ob();

		$this->tip->router()
			->when('router/basic', function() {
				echo 'YES';
			});
		$this->tip->start();
		
		$check = $this->ob(false);
		
		$this->assertTrue($check == 'YES');
	}

}
