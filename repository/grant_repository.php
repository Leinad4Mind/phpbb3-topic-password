<?php

namespace leinad4mind\topicpassword\repository;

class grant_repository
{
	protected $db;
	protected $access_table;
	protected $sessions_table;

	public function __construct($db, $access_table, $sessions_table)
	{
		$this->db = $db;
		$this->access_table = $access_table;
		$this->sessions_table = $sessions_table;
	}

	public function has($topic_id, $user_id, $session_id, $revision)
	{
		$sql = 'SELECT topic_id
			FROM ' . $this->access_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND user_id = ' . (int) $user_id . "
				AND session_id = '" . $this->db->sql_escape($session_id) . "'
				AND password_revision = " . (int) $revision;
		$result = $this->db->sql_query_limit($sql, 1);
		$found = (bool) $this->db->sql_fetchfield('topic_id');
		$this->db->sql_freeresult($result);

		return $found;
	}

	public function grant($topic_id, $user_id, $session_id, $revision, $now)
	{
		$this->revoke_identity($topic_id, $user_id, $session_id);
		$sql = 'INSERT INTO ' . $this->access_table . ' ' . $this->db->sql_build_array('INSERT', [
			'topic_id' => (int) $topic_id,
			'user_id' => (int) $user_id,
			'session_id' => $session_id,
			'password_revision' => (int) $revision,
			'granted_at' => (int) $now,
		]);
		$this->db->sql_query($sql);
	}

	public function revoke_identity($topic_id, $user_id, $session_id)
	{
		$sql = 'DELETE FROM ' . $this->access_table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND user_id = ' . (int) $user_id . "
				AND session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->db->sql_query($sql);
	}

	public function revoke_topic($topic_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->access_table . '
			WHERE topic_id = ' . (int) $topic_id);
	}

	public function revoke_session($session_id)
	{
		$sql = 'DELETE FROM ' . $this->access_table . "
			WHERE session_id = '" . $this->db->sql_escape($session_id) . "'";
		$this->db->sql_query($sql);
	}

	public function garbage_collect()
	{
		$sql = 'DELETE FROM ' . $this->access_table . '
			WHERE session_id NOT IN (
				SELECT session_id FROM ' . $this->sessions_table . '
			)';
		$this->db->sql_query($sql);
	}
}
