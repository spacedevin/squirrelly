<?php

require_once __DIR__ . '/beersquirrel.php';


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
				die('blah');
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
		} else {
			$data = $Request->data;
		}
		
		// no data
		if (!$data || !$type) {
			http_response_code(500);
			exit;
		}

		$u = $Upload->create([
			'date' => date('Y-m-d H:i:s'),
			'type' => $type[0],
			'ext' => $type[1]
		])->load();

		file_put_contents($Tipsy->config()['data']['path'].'/'.$u->uid, $data);

		echo $u->json();
	})
	->otherwise(function($View) {
		$View->display('index');
	});

$bs->start();

