<?php

namespace leinad4mind\topicpassword\event
{
	function trigger_error($message, $error_level = E_USER_NOTICE)
	{
		throw new \RuntimeException($message, $error_level);
	}
}

namespace leinad4mind\topicpassword\tests\event
{
	use ArrayObject;
	use leinad4mind\topicpassword\event\access_listener;
	use PHPUnit\Framework\TestCase;

	class access_listener_test extends TestCase
	{
		public function test_pm_quote_is_guarded_before_core_loads_the_post_body()
		{
			$topics = new access_listener_topics_spy();
			$listener = new access_listener(
				new access_listener_helper_stub(),
				new access_listener_language_stub(),
				new access_listener_policy_stub(),
				new access_listener_access_stub(),
				$topics
			);

			$this->expectException(\RuntimeException::class);
			$this->expectExceptionMessage('/topic-password/42');

			try
			{
				$listener->guard_pm_quote(new ArrayObject([
					'action' => 'quotepost',
					'msg_id' => 91,
				]));
			}
			finally
			{
				$this->assertSame([91], $topics->post_ids);
			}
		}

		public function test_pm_quote_subscribes_to_the_pre_query_event()
		{
			$events = access_listener::getSubscribedEvents();

			$this->assertSame(
				'guard_pm_quote',
				$events['core.ucp_pm_compose_compose_pm_basic_info_query_before']
			);
			$this->assertArrayNotHasKey('core.ucp_pm_compose_quotepost_query_after', $events);
		}
	}

	class access_listener_helper_stub
	{
		public function route($name, array $parameters)
		{
			return '/topic-password/' . (int) $parameters['topic_id'];
		}
	}

	class access_listener_language_stub
	{
		public function lang($key, ...$arguments)
		{
			return $arguments[0] . 'unlock' . $arguments[1];
		}
	}

	class access_listener_policy_stub
	{
		public function native_can_read(array $topic)
		{
			return true;
		}
	}

	class access_listener_access_stub
	{
		public function is_allowed(array $topic)
		{
			return false;
		}
	}

	class access_listener_topics_spy
	{
		public $post_ids = [];

		public function find_by_post($post_id)
		{
			$this->post_ids[] = (int) $post_id;
			return [
				'topic_id' => 42,
				'forum_id' => 2,
				'topic_visibility' => 1,
				'topic_password' => 'hash',
			];
		}
	}
}
