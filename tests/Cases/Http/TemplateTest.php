<?php
namespace SyTest\Http;

use PHPUnit\Framework\TestCase;
use Sy\Http\Template;

class TemplateTest extends TestCase {
	public function testAssignAndRender() {
		$tpl = new Template();
		$tpl->assign('id', 123);
		$this->assertEquals('<p>123</p>', $tpl->render(TEST_APP . 'View/Index.phtml'));
	}

	public function testRenderNotExists() {
		$tpl = new Template();
		$this->assertEquals('', $tpl->render(TEST_APP . 'View/not-exists.phtml'));
	}

	public function testClearAssign() {
		$tpl = new Template();
		$tpl->assign('id', 123);
		$tpl->clearAssign();
		$this->assertEquals('<p></p>', $tpl->render(TEST_APP . 'View/Safe.phtml'));
	}

	public function testCloneClearsAssign() {
		$tpl = new Template();
		$tpl->assign('id', 123);
		$clone = clone $tpl;
		$this->assertEquals('<p></p>', $clone->render(TEST_APP . 'View/Safe.phtml'));
		// original keeps its vars
		$this->assertEquals('<p>123</p>', $tpl->render(TEST_APP . 'View/Index.phtml'));
	}

	public function testReassignOverrides() {
		$tpl = new Template();
		$tpl->assign('id', 123);
		$tpl->assign('id', 456);
		$this->assertEquals('<p>456</p>', $tpl->render(TEST_APP . 'View/Index.phtml'));
	}
}
