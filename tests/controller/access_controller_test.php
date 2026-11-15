<?php

namespace leinad4mind\topicpassword\controller
{
	function add_form_key($form_name)
	{
	}

	function check_form_key($form_name)
	{
		return true;
	}
}

namespace leinad4mind\topicpassword\tests\controller
{
	use leinad4mind\topicpassword\controller\access_controller;
	use leinad4mind\topicpassword\core\access_manager;
	use PHPUnit\Framework\TestCase;

	class access_controller_test extends TestCase
	{
		public function test_blocked_attempt_returns_http_429()
		{
			[$status, $template] = $this->handle(['status' => access_manager::BLOCKED, 'wait' => 37]);

			$this->assertSame(429, $status);
			$this->assertTrue($template['S_TOPIC_PASSWORD_BLOCKED']);
			$this->assertSame(37, $template['TOPIC_PASSWORD_WAIT']);
		}

		public function test_invalid_attempt_keeps_http_200()
		{
			[$status, $template] = $this->handle(['status' => access_manager::INVALID, 'wait' => 0]);

			$this->assertSame(200, $status);
			$this->assertFalse($template['S_TOPIC_PASSWORD_BLOCKED']);
			$this->assertSame(0, $template['TOPIC_PASSWORD_WAIT']);
		}

		private function handle(array $verification)
		{
			$helper = new access_controller_helper_spy();
			$template = new access_controller_template_spy();
			$controller = new access_controller(
				$helper,
				new access_controller_language_spy(),
				new access_controller_request_stub(),
				$template,
				new \stdClass(),
				new access_controller_policy_stub(),
				new access_controller_access_stub($verification),
				new access_controller_topics_stub(),
				new access_controller_return_target_stub()
			);

			$controller->handle(42);

			return [$helper->status, $template->vars];
		}
	}

	class access_controller_helper_spy
	{
		public $status;

		public function render($template, $title, $status)
		{
			$this->status = $status;
			return new \stdClass();
		}
	}

	class access_controller_language_spy
	{
		public function add_lang($file, $extension)
		{
		}

		public function lang($key, ...$arguments)
		{
			return $key . ($arguments ? ':' . implode(',', $arguments) : '');
		}
	}

	class access_controller_request_stub
	{
		public function is_set_post($name)
		{
			return true;
		}

		public function variable($name, $default, $multibyte = false)
		{
			return 'wrong password';
		}
	}

	class access_controller_template_spy
	{
		public $vars = [];

		public function assign_vars(array $vars)
		{
			$this->vars = $vars;
		}
	}

	class access_controller_policy_stub
	{
		public function native_can_read(array $topic)
		{
			return true;
		}

		public function is_protected(array $topic)
		{
			return true;
		}
	}

	class access_controller_access_stub
	{
		private $verification;

		public function __construct(array $verification)
		{
			$this->verification = $verification;
		}

		public function is_allowed(array $topic)
		{
			return false;
		}

		public function verify_and_grant(array $topic, $password)
		{
			return $this->verification;
		}
	}

	class access_controller_topics_stub
	{
		public function find($topic_id)
		{
			return [
				'topic_id' => (int) $topic_id,
				'forum_id' => 2,
				'topic_title' => 'Protected topic',
				'topic_password' => 'hash',
			];
		}
	}

	class access_controller_return_target_stub
	{
		public function for_topic($topic_id)
		{
			return '/viewtopic.php?t=' . (int) $topic_id;
		}

		public function for_forum($forum_id)
		{
			return '/viewforum.php?f=' . (int) $forum_id;
		}
	}
}
