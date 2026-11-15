<?php

namespace leinad4mind\topicpassword\tests\event;

use ArrayObject;
use leinad4mind\topicpassword\event\notification_listener;
use PHPUnit\Framework\TestCase;

class notification_listener_test extends TestCase
{
	public function test_protected_notification_keeps_public_title_but_removes_message_content()
	{
		$listener = new notification_listener(new notification_topics_stub([12]));
		$event = new ArrayObject(['data' => [
			'topic_id' => 12,
			'topic_title' => 'Public topic title',
			'post_subject' => 'Private reply subject',
			'post_text' => 'Private body',
			'message' => 'Private alternate body',
		]]);

		$listener->sanitize($event);

		$this->assertSame('Public topic title', $event['data']['topic_title']);
		$this->assertSame('Public topic title', $event['data']['post_subject']);
		$this->assertSame('', $event['data']['post_text']);
		$this->assertSame('', $event['data']['message']);
	}

	public function test_public_notification_is_not_modified()
	{
		$data = [
			'topic_id' => 13,
			'topic_title' => 'Topic',
			'post_subject' => 'Reply',
			'post_text' => 'Body',
		];
		$listener = new notification_listener(new notification_topics_stub([12]));
		$event = new ArrayObject(['data' => $data]);

		$listener->sanitize($event);

		$this->assertSame($data, $event['data']);
	}
}

class notification_topics_stub
{
	private $protected;

	public function __construct(array $protected)
	{
		$this->protected = $protected;
	}

	public function protected_ids(array $topic_ids)
	{
		return array_fill_keys(array_values(array_intersect($topic_ids, $this->protected)), true);
	}
}
