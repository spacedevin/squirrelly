<?


require_once('../Tipsy/Tipsy.php');

$bs = new Tipsy\Tipsy;

$bs->config('../config.ini');
/*
$bs->config([
	'db' => [
		'host' => 'blah'
	]
], true);
*/
$bs->controller('ViewController', function() {
	$this->scope->test = 'asd';
});

$bs->model('DBO/TestModel', function() {
	$model = [
		'testmodel' => function() {
			die('testing');
		}
	];
	return $model;
});

$bs->model('DBO/FileModel', function() {
	$model = [
		'filemodel' => function() {
			die('testing');
		},
		/*
		'construct' => function() {
			$this->_id_var = 'id';
			$this->_table = 'file';
		},
		*/
		'id' => 'id',
		'table' => 'upload'
	];


	return $model;
});


class LibraryController extends Tipsy\Controller {
	public function init() {
		die('library');
	}
}

class InstanceController extends Tipsy\Controller {
	public function init() {
		die('instance');
	}
}


$test = new InstanceController;

$bs->router()
	->when('upload', function() {
		die('upload');
	})
	->when('file/:id/edit', function($params) {
		die('edit - ' . $params['id']);
	})
	->when('file/:id', function($db, $FileModel) {
		/*
		$res = $db->fetch('select * from upload');
		foreach ($res as $r) {
			print_r($r);
		}
		*/
	
		// get a new instance of the filemodel by id
	
		
		$test = $FileModel->create([
			'uid' => 'bacon'	
		]);
		$test = $FileModel->get(1);
		
		echo $test->uid;
		$test->uid = rand(1,2345454);
		$test->save();
		echo $test->uid;
		
		$FileModel->q('select * from upload where uid=?','bacon')->delete();
	
	exit;
		$File->fetch(1);
		$this->model('File')->fetch(1);
		$this->model('File')->query('select * from file where id=1');
		$file = File::o($this->route()->param('id'));
		print_r($file);
	})
	->when('view', [
		'controller' => 'ViewController',
		'view' => 'test.phtml'
	])
	->when('instance', [
		'controller' => $test
	])
	->when('library', [
		'controller' => 'LibraryController'
	])
	->otherwise(function() {
		// @todo: add redirect to
		die('home');
	});
$bs->start();

