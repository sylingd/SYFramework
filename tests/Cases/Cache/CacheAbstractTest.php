<?php
namespace SyTest\Cache;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Sy\Cache\File;

class CacheAbstractTest extends TestCase {
	public function testGetOrQuery() {
		$handler = new File();
		$key = uniqid('abstract_');
		$count = 0;
		$query = function () use (&$count) {
			$count++;
			return 'result-' . $count;
		};
		// first call queries and caches
		$this->assertEquals('result-1', $handler->getOrQuery($key, $query));
		$this->assertEquals(1, $count);
		// second call returns cached, callable not invoked
		$this->assertEquals('result-1', $handler->getOrQuery($key, $query));
		$this->assertEquals(1, $count);
		$handler->delete($key);
		// with ttl, queries again
		$this->assertEquals('result-2', $handler->getOrQuery($key, $query, 60));
		$this->assertEquals(2, $count);
		$this->assertTrue($handler->has($key));
		$handler->delete($key);
	}

	public function testSetTtlDateInterval() {
		$handler = new File();
		$key = uniqid('ttl_');
		// positive interval keeps the value readable
		$this->assertTrue($handler->set($key, 'v', new DateInterval('PT60S')));
		$this->assertEquals('v', $handler->get($key));
		$handler->delete($key);
		// zero interval expires immediately
		$this->assertTrue($handler->set($key, 'expired', new DateInterval('PT0S')));
		$this->assertNull($handler->get($key));
	}

	public function testGetSecondsWithDateInterval() {
		// getSeconds is protected; verify indirectly through ttl handling
		$handler = new File();
		$key = uniqid('seconds_');
		$this->assertTrue($handler->set($key, 'v', new DateInterval('P1DT1H1M1S')));
		$this->assertEquals('v', $handler->get($key));
		$handler->delete($key);
	}
}
