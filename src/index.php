<?php

require_once __DIR__ . '/beersquirrel.php';

function getId($file) {
	$id = explode('.',$file)[0];
	return preg_replace('/[^0-9a-z]/i', '', $id);
}

if (preg_match('/facebookexternalhit/', $_SERVER['HTTP_USER_AGENT'])) {
	$bs->router()->when('view/:id', function($View, $Params, $Upload, $Scope) {
		$u = $Upload->byUid(getId($Params->id));
		$Scope->image = $u;
		echo $View->render('view-facebook');
		exit;
	});
}


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
		$u = $Upload->byUid(getId($Params->id));
		
		$file = $u->path();

		if (!$u->uid || !file_exists($file)) {
			http_response_code(404);
			exit;
		}

		http_response_code(200);
		header('Date: '.date('r'));
		header('Last-Modified: '.date('r',filemtime($file)));
		header('Accept-Ranges: bytes');
		header('Content-Length: '.filesize($file));

		switch ($u->type) {
			case 'text':
			case 'image':
				header('Content-type: '.$u->type.'/'.$u->ext);
				break;

			default:
				// it shouldnt ever get here, but just in case
				http_response_code(500);
				exit;
				break;
		}

		readfile($file);
		exit;		
	})
	->post('upload', function($Request, $Upload, $Tipsy) {
		$type = explode('/',$Request->type);

		$types = [
			'text' => null,
			'image' => ['png','gif','jpg','jpeg','svg']
		];
		
		// unsupported type
		if (!array_key_exists($type[0], $types)) {
			if ($types[$type[0]] && !$types[$type[0]][$type[1]]) {
				http_response_code(500);
				echo 'Invalid file type';
				exit;
			}
		// || ($types[$type[0]] && !$types[$type[0]][$type[1]])
			http_response_code(500);
			echo 'Invalid file format';
			exit;
		}

		// decode any data
		$search = '/^data:[a-z]+\/[a-z]+;base64,(.*)$/';
		if (preg_match($search,$Request->data)) {
			$data = base64_decode(preg_replace($search,'\\1',$Request->data));

		} elseif ($_FILES['data']) {
			$file = $_FILES['data']['tmp_name'];

		} else {
			$data = $Request->data;
		}
		
		// no data
		if ((!$data && !$file) || !$type) {
			http_response_code(500);
			exit;
		}

		$u = $Upload->create([
			'date' => date('Y-m-d H:i:s'),
			'type' => $type[0],
			'ext' => $type[1]
		])->load();
		
		$filename = $Tipsy->config()['data']['path'].'/'.$u->uid;
		
		if ($data) {
			file_put_contents($filename, $data);
		} else {
			move_uploaded_file($file, $filename);
		}

		echo $u->json();
	})
	->otherwise(function($View) {
		$View->display('index');
	});

$bs->start();

