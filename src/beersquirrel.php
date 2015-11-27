<?php

date_default_timezone_set('America/Los_Angeles');

require_once __DIR__ . '/../vendor/autoload.php';

$bs = new Tipsy\Tipsy;

$bs->config('../src/config.ini');
if (file_exists('../src/config.db.ini')) {
	$bs->config('../src/config.db.ini');
}
if (getenv('HEROKU')) {
	$bs->config('../src/config.heroku.ini');
}

$bs->service('Tipsy\Resource/Upload', [
	put => function($file, $data) {
		if ($this->tipsy()->config()['data']['type'] == 'local') {
			$filename = $this->tipsy()->config()['data']['path'].'/'.$this->uid;

			if ($data) {
				file_put_contents($filename, $data);
			} else {
				move_uploaded_file($file, $filename);
			}
		} else {
			if ($data) {
				$this->data = $data;
			} else {
				$this->data = file_get_contents($file);
			}
		}
		$this->save();
	},
	size => function() {
		if ($this->tipsy()->config()['data']['type'] == 'local') {
			return filesize($file);
		} else {
			return strlen($this->data);
		}
	},
	display => function() {
		if ($this->tipsy()->config()['data']['type'] == 'local') {
			readfile($file);
		} else {
			echo $this->data;
		}
	},
	valid => function() {
		return true;
		if ($u->uid && (($this->tipsy()->config()['data']['type'] == 'local' && file_exists($file)) || ($this->tipsy()->config()['data']['type'] == 'sql' && $this->data))) {
			return true;
		} else {
			die('x');
			return false;
		}
	},
	del => function() {
		if ($this->tipsy()->config()['data']['type'] == 'local') {
			unlink($this->path());
		}
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
			if ($this->tipsy()->config()['data']['type'] == 'local') {
				$ret['content'] = file_get_contents($this->path());
			} else {
				$ret['content'] = $this->data;
			}
		}

		return $ret;
	},
	_id => 'id',
	_table => 'upload'
]);
