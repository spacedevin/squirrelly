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
		
		//$this->tip->config('../config.ini');
		
		
/*
$this->tip->config([
	'db' => [
		'host' => 'blah'
	]
], true);
*/

		
		/*

		
		$this->tip->model('DBO/TestModel', function() {
			$model = [
				'testmodel' => function() {
					die('testing');
				}
			];
			return $model;
		});
		
		$this->tip->model('DBO/FileModel', function() {
			$model = [
				'filemodel' => function() {
					die('testing');
				},

//				'construct' => function() {
//					$this->_id_var = 'id';
//					$this->_table = 'file';
//				},

				'id' => 'id',
				'table' => 'upload'
			];

		
			return $model;
		});
		*/
		
		


	}

	public function testRouterBasic() {
		$_REQUEST['__url'] = 'router/basic';
		
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
