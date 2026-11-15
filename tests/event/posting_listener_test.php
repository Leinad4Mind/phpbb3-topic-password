<?php

namespace leinad4mind\topicpassword\tests\event;

use ArrayObject;
use leinad4mind\topicpassword\event\posting_listener;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class posting_listener_test extends TestCase
{
	public function test_password_change_revokes_grants_and_rotates_search_cache_revision()
	{
		$grants = new posting_grants_spy();
		$config = new posting_config_spy();
		$listener = new posting_listener(new \stdClass(), new \stdClass(), new \stdClass(), $grants, $config);
		$pending = new ReflectionProperty($listener, 'pending');
		$pending->setValue($listener, ['action' => 'remove']);

		$listener->after_submit(new ArrayObject(['topic_id' => 42, 'data' => []]));

		$this->assertSame([42], $grants->revoked);
		$this->assertSame([['topic_password_search_revision', 1]], $config->increments);
		$this->assertNull($pending->getValue($listener));
	}
}

class posting_grants_spy
{
	public $revoked = [];

	public function revoke_topic($topic_id)
	{
		$this->revoked[] = (int) $topic_id;
	}
}

class posting_config_spy
{
	public $increments = [];

	public function increment($key, $amount)
	{
		$this->increments[] = [$key, $amount];
	}
}
