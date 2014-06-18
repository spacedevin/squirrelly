<?

//namespace Tipsy;

$bs = new Tipsy;

$bs->controller('ViewController', function() {
	$this->scope->test = 'asd';
});


class LibraryController extends Controller {
	public function init() {
		die('library');
	}
}

class InstanceController extends Controller {
	public function init() {
		die('instance');
	}
}


$test = new InstanceController;

$bs->router()
	->when('upload', function() {
		die('upload');
	})
	->when('file/:id/edit', function() {
		die('edit - ' . $this->route()->param('id'));
	})
	->when('file/:blob', function() {
		die('file - '.$this->route()->param('blob'));
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
		die('home');
	});
	
$bs->start();


/**
 * Tipsy
 * An MVW PHP Framework
 */


/**
 * Main class
 */
class Tipsy {
	private $_controllers;
	
	public function __construct() {
		$this->_controllers = [];
	}

	public function start() {
		$this->page = explode('/', $_REQUEST['__url']);

		$route = $this->router()->match($_REQUEST['__url']);
		//preg_replace('/[^0-9a-z]/i','',$_REQUEST['__url'])

		$route->controller()->init();
	}
	public function router() {
		if (!isset($this->_router)) {
			$this->_router = new Router(['tipsy' => $this]);
		}
		return $this->_router;
	}
	public function controller($controller, $closure = null) {
		if ($controller && is_callable($closure)) {
			$this->_controllers[$controler] = new Controller(['closure' => $closure]);
			return $this;
		} elseif ($controler) {
			return $this->_controllers[$controler];
		} else {
			return null;
		}
	}

	
}


/**
 * Handles definition and resolution of routes to controllers
 */
class Router {

	private $_routes;
	private $_tipsy;

	public function __construct($args = []) {
		$this->_routes = [];
		$this->_tipsy = $args['tipsy'];
	}

	public function when($r, $args = null) {
		if (is_array($r)) {
			$route = $r;
		} else {
			if (is_array($args)) {
				$route = $args;			
			} else {
				$route = ['controller' => $args];
			}
			$route['route'] = $r;
		}
		$route['tipsy'] = $this->_tipsy;

		$this->_routes[] = new Route($route);
		
		return $this;
	}
	
	public function otherwise($default) {
		$this->_default = new Route([
			'controller' => $default,
			'tipsy' => $this->_tipsy
		]);
	}
	
	public function match($page) {
		foreach ($this->routes() as $route) {
			if ($route->match($page)) {
				return $route;
			}
		}

		return $this->defaultRoute();
	}
	
	public function routes($routes = null) {
		if (isset($$routes)) {
			$this->_routes = $routes;
		}
		return $this->_routes;
	}
	
	public function defaultRoute() {
		return $this->_default ? $this->_default : new Route(['tipsy' => $this->_tipsy]);
	}

}

/**
 * Route object
 */
class Route  {

	private $_tipsy;

	public function __construct($args) {
		$this->_controller = $args['controller'];
		$this->_caseSensitive = $args['caseSensitive'] ? true : false;
		$this->_view = $args['view'] ? true : false;
		$this->_route = preg_replace('/^\/?(.*?)\/?$/i','\\1',$args['route']);
		$this->_simpleRoute = preg_replace('/:[a-z]+(\/?)/i','',$this->_route);
		$this->_tipsy = $args['tipsy'];
	}
	
	public function match($page) {

		$this->_routeParams = [];
		
		$pathParams = [];
		$paths = explode('/',$this->_route);

		foreach ($paths as $key => $path) {
			if (strpos($path,':') === 0) {
				$pathParams[$key] = substr($path,1);
			}
		}

		$r = preg_replace('/:[a-z]+/i','.*',$this->_route);
		$r = preg_replace('/\//','\/',$r);

		if (preg_match('/^'.$r.'$/'.($this->caseSensitive() ? '' : 'i'),$page)) {
			$paths = explode('/',$page);

			foreach ($pathParams as $key => $path) {
				$this->_routeParams[$path] = $paths[$key];
			}
			
			return $this;
		}
		return false;
	}

	public function param($param) {
		return $this->_routeParams[$param];
	}
	
	public function params() {
		return $this->_routeParams;
	}
	
	public function controller() {

		if (!isset($this->_controllerRef)) {

			if (is_callable($this->_controller)) {

				$controller = new Controller([
					'closure' => $this->_controller
				]);
				$this->_controllerRef = $controller;

			} elseif(is_object($this->_controller)) {
				$this->_controllerRef = $this->_controller;

			} elseif (is_string($this->_controller) && $this->_tipsy->controller($this->_controller)) {
				$this->_controllerRef = $this->_tipsy->controller($this->_controller);

			} elseif (is_string($this->_controller) && class_exists($this->_controller)) {
				$this->_controllerRef = new $this->_controller;

			}
			
			if ($this->_controllerRef) {
				$this->_controllerRef->route($this);
			}
		}
		
		if (!$this->_controllerRef) {
			die('No controller attached to route.');
		}
		
		return $this->_controllerRef;
	}

	public function caseSensitive() {
		return $this->_caseSensitive;
	}

	public static function possiblePages($route) {
		
	}
	
	public function simpleRoute() {
		return $this->_simpleRoute;
	}
}

/**
 * Controller object
 */
class Controller {
	private $_closure;
	private $_route;
	public $scope;

	public function __construct($args = []) {
		if (isset($args['closure'])) {
			$this->_closure = Closure::bind($args['closure'], $this, get_class());
		}
		if (isset($args['route'])) {
			$this->_route = $args['route'];
		}
		$this->scope = new Scope;
	}
	public function init() {
		if ($this->closure()) {
			call_user_func_array($this->_closure, []);
		}
	}
	public function closure() {
		return $this->_closure;
	}
	
	public function route($route = null) {
		if ($route) {
			$this->_route = $route;
		}
		return $this->_route;
	}
	
}

/**
 * Scope object
 */
class Scope {
	
}