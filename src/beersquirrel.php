<?


//require_once('../Tipsy/Tipsy.php');
require_once('/Users/arzynik/Sites/Tipsy/src/Tipsy/Tipsy.php');

$bs = new Tipsy\Tipsy;

$bs->config('../src/config.ini');

$bs->model('Tipsy\DBO/Upload', [
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
	id => 'id',
	table => 'upload'
]);



$bs->router()
	->when('get/:id', function($Params, $Upload, $Tipsy) {
		$id = explode('.',$Params->id)[0];
		$u = $Upload->byUid($id);

		if (!$u->uid) {
			http_response_code(404);
			exit;
		}
		
		echo $u->json();
	})
	->when('file/:id', function($Params, $Upload, $Tipsy) {
		$id = explode('.',$Params->id)[0];
		$u = $Upload->byUid($id);

		if (!$u->uid) {
			http_response_code(404);
			exit;
		}
		
		$file = $u->path();

		http_response_code(200);
		header('Date: '.date('r'));
		header('Last-Modified: '.date('r',filemtime($file)));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.filesize($file));

		switch ($u->type) {
			case 'text':
				header('Content-type: text/plain');
				break;

			case 'image':
				header('Content-type: image/'.$u->ext);
				break;

			default:
				http_response_code(500);
				exit;
				break;
		}

		readfile($file);
		exit;		
	})
	->post('upload', function($Request, $Upload, $Tipsy) {
		
		if ($Request->type == 'image' && substr($Request->data, 0, 11) == 'data:image/') {
			// image
			$ext = preg_replace('/^data:image\/([a-z]{3});base64,(.*)$/','\\1',$Request->data);
			$data = preg_replace('/^data:image\/([a-z]{3});base64,(.*)$/','\\2',$Request->data);

			switch ($ext) {
				case 'png':
				case 'jpg':
				case 'jpeg':
				case 'gif':
					$type = 'image';
					$data = base64_decode($data);
					break;
			}
			
			if ($ext == 'jpeg') {
				$ext = 'jpg';
			}

		} else {
			// text
			$type = 'text';
			$ext = 'txt';
			$data = $Request->data;
		}

		if (!$data) {
			http_response_code(500);
			exit;
		}

		$u = $Upload->create([
			'date' => date('Y-m-d H:i:s'),
			'type' => $type
		])->load();

		file_put_contents($Tipsy->config()['data']['path'].'/'.$u->uid, $data);

		echo $u->json();
	})
	->otherwise(function($View) {
		$View->display('index');
	});

$bs->start();

