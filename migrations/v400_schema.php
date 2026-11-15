<?php

namespace leinad4mind\topicpassword\migrations;

class v400_schema extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\leinad4mind\\topicpassword\\migrations\\v399_legacy_probe'];
	}

	public function effectively_installed()
	{
		return isset($this->config['topic_password_version'])
			&& version_compare($this->config['topic_password_version'], '4.0.0', '>=');
	}

	public function update_schema()
	{
		$forums = $this->table_prefix . 'forums';
		$topics = $this->table_prefix . 'topics';
		$access = $this->table_prefix . 'topics_access';
		$attempts = $this->table_prefix . 'topic_password_attempts';

		// This definition must remain stable while phpBB resumes individual
		// schema steps. DB tools already make these operations idempotent.
		return [
			'add_tables' => [
				$access => [
				'COLUMNS' => [
					'topic_id' => ['UINT', 0],
					'user_id' => ['UINT', 0],
					'session_id' => ['CHAR:32', ''],
					'password_revision' => ['UINT', 0],
					'granted_at' => ['UINT', 0],
				],
				'PRIMARY_KEY' => ['topic_id', 'user_id', 'session_id'],
				'KEYS' => [
					'session_id' => ['INDEX', 'session_id'],
				],
				],
				$attempts => [
				'COLUMNS' => [
					'topic_id' => ['UINT', 0],
					'actor_key' => ['CHAR:64', ''],
					'attempt_count' => ['USINT', 0],
					'first_attempt' => ['UINT', 0],
					'last_attempt' => ['UINT', 0],
					'blocked_until' => ['UINT', 0],
				],
				'PRIMARY_KEY' => ['topic_id', 'actor_key'],
				'KEYS' => [
					'last_attempt' => ['INDEX', 'last_attempt'],
				],
				],
			],
			'change_columns' => [
				$topics => [
					'topic_password' => ['VCHAR:255', ''],
				],
			],
			'add_columns' => [
				$forums => [
					'forum_allow_topic_password' => ['BOOL', 0],
				],
				$topics => [
					'topic_password_revision' => ['UINT', 0],
				],
				$access => [
					'password_revision' => ['UINT', 0],
					'granted_at' => ['UINT', 0],
				],
			],
			'add_index' => [
				$access => [
					'session_id' => ['session_id'],
				],
			],
		];
	}

	public function revert_schema()
	{
		return [
			'drop_columns' => [
				$this->table_prefix . 'forums' => ['forum_allow_topic_password'],
				$this->table_prefix . 'topics' => ['topic_password', 'topic_password_revision'],
			],
			'drop_tables' => [
				$this->table_prefix . 'topics_access',
				$this->table_prefix . 'topic_password_attempts',
			],
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['topic_password_attempt_limit', 5]],
			['config.add', ['topic_password_ip_attempt_limit', 20]],
			['config.add', ['topic_password_attempt_window', 900]],
			['config.add', ['topic_password_lockout', 900]],
			['config.add', ['topic_password_search_revision', 1]],
			['custom', [[$this, 'ensure_hmac_secret']]],
			['custom', [[$this, 'discard_legacy_grants']]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['topic_password_hmac_secret']],
			['config.remove', ['topic_password_search_revision']],
		];
	}

	public function ensure_hmac_secret()
	{
		if (empty($this->config['topic_password_hmac_secret']))
		{
			$this->config->set('topic_password_hmac_secret', bin2hex(random_bytes(32)), false);
		}
	}

	public function discard_legacy_grants()
	{
		$this->db->sql_query('DELETE FROM ' . $this->table_prefix . 'topics_access');
	}
}
