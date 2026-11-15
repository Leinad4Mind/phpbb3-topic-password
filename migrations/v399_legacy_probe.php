<?php

namespace leinad4mind\topicpassword\migrations;

/**
 * Records the legacy storage format before the 4.0 schema normalises it.
 */
class v399_legacy_probe extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		return ['\\phpbb\\db\\migration\\data\\v33x\\v3319'];
	}

	public function effectively_installed()
	{
		return isset($this->config['topic_password_legacy_source']);
	}

	public function update_data()
	{
		return [
			['custom', [[$this, 'record_source_format']]],
		];
	}

	public function revert_data()
	{
		return [
			['config.remove', ['topic_password_legacy_source']],
		];
	}

	public function record_source_format()
	{
		$source = 'clean';
		$topics = $this->table_prefix . 'topics';

		if (isset($this->config['topic_password_version'])
			&& version_compare((string) $this->config['topic_password_version'], '3.0.0', '>='))
		{
			$source = 'hash';
		}
		else if (isset($this->config['topic_pass_version']))
		{
			$source = 'plaintext';
		}
		else if ($this->db_tools->sql_column_exists($topics, 'topic_password'))
		{
			$sql = 'SELECT topic_id
				FROM ' . $topics . "
				WHERE topic_password <> ''";
			$result = $this->db->sql_query_limit($sql, 1);
			$has_passwords = (bool) $this->db->sql_fetchfield('topic_id');
			$this->db->sql_freeresult($result);

			if ($has_passwords)
			{
				// The 1.x MOD had no version config and stored clear text. Only
				// accept it when its other two schema markers are both present.
				$forums = $this->table_prefix . 'forums';
				$access = $this->table_prefix . 'topics_access';
				$source = $this->db_tools->sql_column_exists($forums, 'forum_allow_topic_password')
					&& $this->db_tools->sql_table_exists($access)
					? 'plaintext'
					: 'unknown';
			}
		}

		$this->config->set('topic_password_legacy_source', $source, false);
	}
}
