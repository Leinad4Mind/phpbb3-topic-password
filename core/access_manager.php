<?php

namespace leinad4mind\topicpassword\core;

class access_manager
{
	public const OK = 'ok';
	public const INVALID = 'invalid';
	public const BLOCKED = 'blocked';
	public const RACE = 'race';

	protected $config;
	protected $passwords;
	protected $user;
	protected $policy;
	protected $topics;
	protected $grants;
	protected $attempts;

	public function __construct($config, $passwords, $user, $policy, $topics, $grants, $attempts)
	{
		$this->config = $config;
		$this->passwords = $passwords;
		$this->user = $user;
		$this->policy = $policy;
		$this->topics = $topics;
		$this->grants = $grants;
		$this->attempts = $attempts;
	}

	public function is_allowed(array $topic)
	{
		if (!$this->policy->is_protected($topic) || $this->policy->is_bypass($topic))
		{
			return true;
		}

		return $this->grants->has(
			(int) $topic['topic_id'],
			$this->user_id(),
			$this->session_id(),
			(int) $topic['topic_password_revision']
		);
	}

	public function verify_and_grant(array $topic, $password, $now = null)
	{
		$now = $now === null ? time() : (int) $now;
		$actors = $this->actor_keys();
		$wait = $this->attempts->wait_seconds((int) $topic['topic_id'], $actors, $now);
		if ($wait > 0)
		{
			return ['status' => self::BLOCKED, 'wait' => $wait];
		}

		$this->passwords->convert_flag = false;
		if (!$this->passwords->check($password, (string) $topic['topic_password']))
		{
			foreach ($actors as $kind => $actor_key)
			{
				$limit = $kind === 'ip'
					? (int) $this->config['topic_password_ip_attempt_limit']
					: (int) $this->config['topic_password_attempt_limit'];
				$this->attempts->record_failure(
					(int) $topic['topic_id'],
					$actor_key,
					$now,
					$limit,
					(int) $this->config['topic_password_attempt_window'],
					(int) $this->config['topic_password_lockout']
				);
			}
			return ['status' => self::INVALID, 'wait' => 0];
		}
		$convert = (bool) $this->passwords->convert_flag;

		$revision = (int) $topic['topic_password_revision'];
		if ($convert)
		{
			$new_hash = $this->passwords->hash($password);
			if ($new_hash === false || !$this->topics->update_hash_if_current(
				(int) $topic['topic_id'],
				(string) $topic['topic_password'],
				$revision,
				$new_hash
			))
			{
				return ['status' => self::RACE, 'wait' => 0];
			}
		}

		if ($this->topics->password_revision((int) $topic['topic_id']) !== $revision)
		{
			return ['status' => self::RACE, 'wait' => 0];
		}

		$this->grants->grant(
			(int) $topic['topic_id'],
			$this->user_id(),
			$this->session_id(),
			$revision,
			$now
		);
		$this->attempts->clear((int) $topic['topic_id'], $actors);

		return ['status' => self::OK, 'wait' => 0];
	}

	private function actor_keys()
	{
		$secret = (string) $this->config['topic_password_hmac_secret'];
		$actors = [
			'session' => $this->actor_key('session', $this->session_id(), $secret),
			'ip' => $this->actor_key('ip', (string) $this->user->ip, $secret),
		];
		if ($this->user_id() !== ANONYMOUS)
		{
			$actors['account'] = $this->actor_key('account', (string) $this->user_id(), $secret);
		}

		return $actors;
	}

	private function actor_key($kind, $value, $secret)
	{
		return hash_hmac('sha256', $kind . ':' . $value, $secret);
	}

	private function user_id()
	{
		return (int) $this->user->data['user_id'];
	}

	private function session_id()
	{
		return isset($this->user->session_id)
			? (string) $this->user->session_id
			: (string) $this->user->data['session_id'];
	}
}
