<?phpuse \PHPUnit\Framework\TestCase;
use Sy\base\Router;

class RouterTest extends PHPUnit_Framework_TestCase {
	public function testGetMap() {
		Router::$routerType = 'map';
		$_SERVER['REQUEST_URI'] = '/admin/index/welcome.html?id=1';
		$result = [
			'module' => 'admin',
			'controller' => 'index',
			'action' => 'welcome'
		];
		$this->assertEquals($result, Router::getRoute());
		//默认module
		Router::$defaultModule = 'testModule';
		$_SERVER['REQUEST_URI'] = '/index/welcome.html?id=1';
		$result = [
			'module' => 'testModule',
			'controller' => 'index',
			'action' => 'welcome'
		];
		$this->assertEquals($result, Router::getRoute());
	}
	public function testGetSimple() {
		Router::$routerType = 'simple';
		Router::$routeParamM = 'module';
		Router::$routeParamC = 'controller';
		Router::$routeParamA = 'action';
		$result = [
			'module' => 'admin',
			'controller' => 'index',
			'action' => 'welcome'
		];
		$_GET = $result;
		$this->assertEquals($result, Router::getRoute());
		//默认module
		Router::$defaultModule = 'testModule';
		$result = [
			'module' => 'testModule',
			'controller' => 'index',
			'action' => 'welcome'
		];
		unset($_GET['module']);
		$this->assertEquals($result, Router::getRoute());
	}
	public function testGetSupervar() {
		Router::$routerType = 'supervar';
		Router::$routeParam = 'r';
		$result = [
			'module' => 'admin',
			'controller' => 'index',
			'action' => 'welcome'
		];
		$_GET['r'] = 'admin/index/welcome';
		$this->assertEquals($result, Router::getRoute());
		//默认module
		Router::$defaultModule = 'testModule';
		$_GET['r'] = 'index/welcome';
		$result = [
			'module' => 'testModule',
			'controller' => 'index',
			'action' => 'welcome'
		];
		$this->assertEquals($result, Router::getRoute());
	}
}