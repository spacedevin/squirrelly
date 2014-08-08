<?php

date_default_timezone_set('America/Los_Angeles');

require_once __DIR__ . '/../vendor/autoload.php';

$bs = new Tipsy\Tipsy;

$bs->config('../src/config.ini');
if (file_exists('../src/config.db.ini')) {
	$bs->config('../src/config.db.ini');	
}

$bs->model('Tipsy\DBO/Upload', [
	del => function() {
		unlink($this->path());
		$this->delete();
	},
	byUid => function($id) {
		return $this->q('select * from upload where uid=?', $id)->get(0);
	},
	path => function() {
		return $this->tipsy()->config()['data']['path'].'/'.$this->uid;
	},
	exports => function() {
		$ret = [
			'uid' => $this->uid,
			'date' => $this->date,
			'type' => $this->type,
			'ext' => $this->ext
		];

		if ($this->type == 'text') {
			$ret['content'] = file_get_contents($this->path());
		}
		
		return $ret;
	},
	_id => 'id',
	_table => 'upload'
]);
