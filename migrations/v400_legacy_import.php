<?php

namespace leinad4mind\topicpassword\migrations;

class v400_legacy_import extends \phpbb\db\migration\migration
{
	private const BATCH_SIZE = 250;

	public static function depends_on()
	{
		return ['\\leinad4mind\\topicpassword\\migrations\\v400_schema'];
	}

	public function effectively_installed()
	{
		return isset($this->config['topic_password_version'])
			&& version_compare($this->config['topic_password_version'], '4.0.0', '>=');
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'convert_legacy_passwords']]],
			['custom', [[$this, 'set_current_version']]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['topic_password_version']],
		];
	}

	public function set_current_version()
	{
		$this->config->set('topic_password_version', '4.0.0', false);
	}

	public function convert_legacy_passwords($after = 0)
	{
		$after = (int) $after;
		$source = $this->source_format();
		$sql = 'SELECT topic_id, topic_password
			FROM ' . $this->table_prefix . "topics
			WHERE topic_id > $after
				AND topic_password <> ''
			ORDER BY topic_id ASC";
		$result = $this->db->sql_query_limit($sql, self::BATCH_SIZE);
		$rows = [];
		$last = $after;

		while ($row = $this->db->sql_fetchrow($result))
		{
			$last = (int) $row['topic_id'];
			$rows[] = $row;
		}
		$this->db->sql_freeresult($result);

		if (!$rows)
		{
			return null;
		}

		global $phpbb_container;
		$passwords = $phpbb_container->get('passwords.manager');

		foreach ($rows as $row)
		{
			$stored = (string) $row['topic_password'];
			$algorithm = $passwords->detect_algorithm($stored);

			if ($source === 'hash')
			{
				if ($algorithm === false)
				{
					throw new \phpbb\db\migration\exception('TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD');
				}
				continue;
			}

			if ($source !== 'plaintext')
			{
				throw new \phpbb\db\migration\exception('TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE');
			}

			$hash = $passwords->hash($stored);
			if ($hash === false)
			{
				throw new \phpbb\db\migration\exception('TOPICPASSWORD_LEGACY_HASH_FAILED');
			}

			$sql = 'UPDATE ' . $this->table_prefix . 'topics
				SET ' . $this->db->sql_build_array('UPDATE', [
					'topic_password' => $hash,
					'topic_password_revision' => 1,
				]) . '
				WHERE topic_id = ' . (int) $row['topic_id'];
			$this->db->sql_query($sql);
		}

		return count($rows) === self::BATCH_SIZE ? $last : null;
	}

	private function source_format()
	{
		return isset($this->config['topic_password_legacy_source'])
			? (string) $this->config['topic_password_legacy_source']
			: 'unknown';
	}
}
