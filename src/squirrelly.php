<?php

date_default_timezone_set('America/Los_Angeles');

require_once __DIR__ . '/../vendor/autoload.php';

$bs = new Tipsy\Tipsy;

$bs->config('../src/config.ini');

if (getenv('HEROKU')) {
	$bs->config('../src/config.heroku.ini');
}
$envdb = getenv('CLEARDB_DATABASE_URL') ? getenv('CLEARDB_DATABASE_URL') : getenv('DATABASE_URL');

if ($envdb) {
	$bs->config(['db' => ['url' => $envdb]]);
} elseif (file_exists('../src/config.db.ini')) {
	$bs->config('../src/config.db.ini');
}

// transforms mysql queries to pgsql (kinda)
class Db extends \Tipsy\Db {
	public static function mysqlToPgsql($query) {
		// replace backticks
		$query = str_replace('`','"', $query);

		// replace add single quotes to interval statements
		$query = preg_replace('/(interval) ([0-9]+) ([a-z]+)/i','\\1 \'\\2 \\3\'', $query);

		return $query;
	}

	public function query($query, $args = []) {
		if (!$query) {
			throw new Exception('Query is emtpy');
		}
		$query = self::mysqlToPgsql($query);
		if (!$query) {
			throw new Exception('mysqlToPgsql Query is emtpy');
		}
		return parent::query($query, $args);
	}

	public function exec($query) {
		return parent::exec(self::mysqlToPgsql($query));
	}
}

if (strpos($envdb, 'postgres') !== false) {
	$bs->service('Db');
}

$bs->service('Tipsy\Resource/Upload', [
	put => function($file, $data) {
		$count = $this->tipsy()->db()->get('select count(*) as c from `upload`')[0]->c;

		if ($count > $this->tipsy()->config()['data']['max']) {
			if ($this->tipsy()->db()->driver() == 'pgsql') {
				$q = 'delete from "upload" where ctid in (select ctid FROM "upload" order by date limit '.($count - $this->tipsy()->config()['data']['max']).')';
			} else {
				$q = 'delete from `upload` order by id asc limit '.($count - $this->tipsy()->config()['data']['max']);
			}
			$this->tipsy()->db()->exec($q);
		}

		$u = $this->load($u->id);

		if ($this->tipsy()->config()['data']['type'] == 'local') {
			$filename = $this->tipsy()->config()['data']['path'].'/'.$this->uid;

			if ($data) {
				file_put_contents($filename, $data);
			} else {
				move_uploaded_file($file, $filename);
			}
			$this->size = filesize($filename);
		} else {

			if ($data) {
				$this->data = $data;
			} else {
				$this->data = file_get_contents($file);
			}

			$this->size = strlen($this->data);

			if ($this->type == 'image') {
				$this->data = base64_encode($this->data);
			}
		}

		$sql = 'UPDATE upload SET data=:data, size=:size WHERE id=:id';
		$stmt = $this->tipsy()->db()->db()->prepare($sql);

		$stmt->bindParam(':id', $this->id);
		$stmt->bindParam(':size', $this->size);
		$stmt->bindParam(':data', $this->data, PDO::PARAM_LOB);
		$stmt->execute();
	},
	display => function() {
		if ($this->tipsy()->config()['data']['type'] == 'local') {
			readfile($this->path());
		} else {
			if ($this->type == 'image') {
				echo base64_decode($this->data);
			} else {
				echo $this->data;
			}
		}
	},
	valid => function() {
		if (!$this->uid) {
			return false;
		}
		if ($this->tipsy()->config()['data']['type'] == 'local' && file_exists($this->path())) {
			return true;
		} elseif ($this->tipsy()->config()['data']['type'] == 'sql' && $this->data) {
			return true;
		} else {
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
				if (is_string($this->data)) {
					$ret['content'] = $this->data;
				} else {
					$ret['content'] = stream_get_contents($this->data);
				}
			}
		}

		return $ret;
	},
	_id => 'id',
	_table => 'upload'
]);
