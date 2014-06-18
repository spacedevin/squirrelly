<?

$bs = new BeerSquirrel;

$bs->controller('ViewController', function() {
	die('view');
});

$bs->router()
	->when('upload', function() {
		die('upload');
	})
	->when('view', ['controller' => 'ViewController'])
	->otherwise(function() {
		die('home');
	});
	
$bs->start();



class BeerSquirrel {
	private $_controllers;
	
	public function __construct() {
		$this->_controllers = [];
	}

	public function start() {
		$this->page = explode('/', $_REQUEST['__url']);
		$route = $this->router()->match(preg_replace('/[^0-9a-z]/i','',$_REQUEST['__url']));

		$route->controller()->init();
	}
	public function router() {
		if (!isset($this->_router)) {
			$this->_router = new Router;
		}
		return $this->_router;
	}
	public function controller($controller, $closure = null) {
		if ($controller && $closure) {
			$this->_controllers[$controler] = new Controller(['closure' => $closure]);
			return $this;
		} else {
			return $this->_controllers[$controler];
		}

	}
	
}


class Router {

	private $_routes;

	public function __contstruct() {
		$this->_routes = [];
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

		$this->_routes[] = new Route($route);
		
		return $this;
	}
	
	public function otherwise($default) {
		$this->_default = new Route([
			'controller' => $default
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
		return $this->_default ? $this->_default : new Route;
	}

}


class Route  {

	public function Route($args) {
		$this->_controller = $args['controller'];
		$this->_caseSensitive = $args['caseSensitive'] ? true : false;
		$this->_view = $args['view'] ? true : false;
		$this->_route = preg_replace('/^\/?(.*?)\/?$/i','\\1',$args['route']);
		$this->_simpleRoute = preg_replace('/:[a-z]+(\/?)/i','',$this->routez());
	}
	
	public function match($page) {

		$this->_routeParams = [];
		
		$pathParams = [];
		$paths = explode('/',$this->routez());

		foreach ($paths as $key => $path) {
			if (strpos($path,':') === 0) {
				$pathParams[$key] = substr($path,1);
			}
		}

		/*
		$r = preg_replace_callback('/:[a-z]+/i',function($matches) {
			$this->_routeMatch[] = substr($matches[0],1);
			return '.*';
		}, $this->route());
		*/

		$r = preg_replace('/:[a-z]+/i','.*',$this->routez());
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

			} elseif (is_string($this->_controller)) {
				
			} else {

				$name = $this->_controller ? str_replace('_','/',$this->_controller) : $this->_simpleRoute;

				// include the file
				if (!Cana::app()->includeFile($name)) {
					return;
				}

				// try to guess the controller based on the route name
				if (!$this->_controller) {
					$pageClass = explode('/',$name);

					foreach ($pageClass as $posiblePage) {
						$posiblePages[] = 'Controller'.$fullPageNext.'_'.str_replace('.','_',$posiblePage);
						$fullPageNext .= '_'.$posiblePage;
					}
					$posiblePages = array_reverse($posiblePages);

					foreach ($posiblePages as $posiblePage) {
						if (class_exists($posiblePage, false)) {
							$controller = new $posiblePage($this->params());

							if (method_exists($posiblePage, 'init')) {
								$this->_controllerRef = $controller;
								break;
							}
						}
					}
				}


				if (!$this->_controllerRef) {
					die('no controller');
					//Cana::displayPage(Cana::config()->defaults->errorPage ? Cana::config()->defaults->errorPage : Cana::config()->defaults->page);
				}
			}
		}
		
		return $this->_controllerRef;
	}

	public function caseSensitive() {
		return $this->_caseSensitive;
	}
	
	public function routez() {
		return $this->_route;
	}
	
	public static function possiblePages($route) {
		
	}
	
	public function simpleRoute() {
		return $this->_simpleRoute;
	}
}


class Controller {
	public function __contstruct($params = []) {
		
	}
	public function init() {
		
	}
	
}