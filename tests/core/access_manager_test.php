<?php

namespace leinad4mind\topicpassword\tests\core;

use leinad4mind\topicpassword\core\access_manager;
use PHPUnit\Framework\TestCase;

class access_manager_test extends TestCase
{
	public function test_successful_plaintext_check_creates_revision_bound_grant()
	{
		[$manager, $grants, $attempts] = $this->manager('secret-value');
		$result = $manager->verify_and_grant($this->topic(), 'correct horse', 1000);

		$this->assertSame(access_manager::OK, $result['status']);
		$this->assertSame([42, 7, 'session-1', 3, 1000], $grants->last_grant);
		$this->assertTrue($attempts->cleared);
	}

	public function test_stored_hash_is_not_itself_a_credential()
	{
		[$manager, $grants] = $this->manager('secret-value');
		$result = $manager->verify_and_grant($this->topic(), 'stored-hash', 1000);

		$this->assertSame(access_manager::INVALID, $result['status']);
		$this->assertNull($grants->last_grant);
	}

	public function test_active_rate_limit_prevents_password_check()
	{
		[$manager, $grants, $attempts, $passwords] = $this->manager('secret-value');
		$attempts->wait = 30;
		$result = $manager->verify_and_grant($this->topic(), 'correct horse', 1000);

		$this->assertSame(access_manager::BLOCKED, $result['status']);
		$this->assertSame(30, $result['wait']);
		$this->assertSame(0, $passwords->checks);
		$this->assertNull($grants->last_grant);
	}

	public function test_changed_revision_prevents_a_stale_grant()
	{
		[$manager, $grants, , , $topics] = $this->manager('secret-value');
		$topics->revision = 4;
		$result = $manager->verify_and_grant($this->topic(), 'correct horse', 1000);

		$this->assertSame(access_manager::RACE, $result['status']);
		$this->assertNull($grants->last_grant);
	}

	public function test_failed_conditional_rehash_prevents_a_grant()
	{
		[$manager, $grants, , $passwords, $topics] = $this->manager('secret-value');
		$passwords->convert_flag_on_check = true;
		$topics->update_succeeds = false;
		$result = $manager->verify_and_grant($this->topic(), 'correct horse', 1000);

		$this->assertSame(access_manager::RACE, $result['status']);
		$this->assertNull($grants->last_grant);
	}

	private function manager($hash)
	{
		$config = [
			'topic_password_hmac_secret' => str_repeat('a', 64),
			'topic_password_attempt_limit' => 5,
			'topic_password_ip_attempt_limit' => 20,
			'topic_password_attempt_window' => 900,
			'topic_password_lockout' => 900,
		];
		$passwords = new access_fake_passwords($hash);
		$user = (object) [
			'data' => ['user_id' => 7, 'session_id' => 'session-1'],
			'session_id' => 'session-1',
			'ip' => '192.0.2.5',
		];
		$policy = new access_fake_policy();
		$topics = new access_fake_topics();
		$grants = new access_fake_grants();
		$attempts = new access_fake_attempts();

		return [
			new access_manager($config, $passwords, $user, $policy, $topics, $grants, $attempts),
			$grants,
			$attempts,
			$passwords,
			$topics,
		];
	}

	private function topic()
	{
		return [
			'topic_id' => 42,
			'forum_id' => 2,
			'topic_password' => 'stored-hash',
			'topic_password_revision' => 3,
		];
	}
}

class access_fake_passwords
{
	public $convert_flag = false;
	public $convert_flag_on_check = false;
	public $checks = 0;
	private $expected_hash;

	public function __construct($expected_hash)
	{
		$this->expected_hash = $expected_hash;
	}

	public function check($password, $hash)
	{
		$this->checks++;
		$this->convert_flag = $this->convert_flag_on_check;
		return $password === 'correct horse' && $hash === 'stored-hash';
	}

	public function hash($password)
	{
		return 'new-hash';
	}
}

class access_fake_policy
{
	public function is_protected(array $topic)
	{
		return true;
	}

	public function is_bypass(array $topic)
	{
		return false;
	}
}

class access_fake_topics
{
	public $revision = 3;
	public $update_succeeds = true;

	public function password_revision($topic_id)
	{
		return $this->revision;
	}

	public function update_hash_if_current($topic_id, $old_hash, $revision, $new_hash)
	{
		return $this->update_succeeds;
	}
}

class access_fake_grants
{
	public $last_grant;

	public function grant($topic_id, $user_id, $session_id, $revision, $now)
	{
		$this->last_grant = [$topic_id, $user_id, $session_id, $revision, $now];
	}
}

class access_fake_attempts
{
	public $wait = 0;
	public $cleared = false;

	public function wait_seconds($topic_id, array $actors, $now)
	{
		return $this->wait;
	}

	public function record_failure($topic_id, $actor, $now, $limit, $window, $lockout)
	{
	}

	public function clear($topic_id, array $actors)
	{
		$this->cleared = true;
	}
}
