<?php

namespace leinad4mind\topicpassword\repository;

class topic_repository
{
	protected $db;
	protected $topics_table;
	protected $forums_table;
	protected $posts_table;

	public function __construct($db, $topics_table, $forums_table, $posts_table)
	{
		$this->db = $db;
		$this->topics_table = $topics_table;
		$this->forums_table = $forums_table;
		$this->posts_table = $posts_table;
	}

	public function find($topic_id)
	{
		$sql = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_poster,
				t.topic_visibility, t.topic_password, t.topic_password_revision,
				f.forum_name, f.forum_allow_topic_password
			FROM ' . $this->topics_table . ' t
			LEFT JOIN ' . $this->forums_table . ' f
				ON f.forum_id = t.forum_id
			WHERE t.topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function find_by_post($post_id)
	{
		$sql = 'SELECT t.topic_id, t.forum_id, t.topic_title, t.topic_poster,
				t.topic_visibility, t.topic_password, t.topic_password_revision,
				f.forum_name, f.forum_allow_topic_password
			FROM ' . $this->posts_table . ' p
			INNER JOIN ' . $this->topics_table . ' t
				ON t.topic_id = p.topic_id
			LEFT JOIN ' . $this->forums_table . ' f
				ON f.forum_id = t.forum_id
			WHERE p.post_id = ' . (int) $post_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row ?: false;
	}

	public function count_protected($forum_id = null)
	{
		$where = "topic_password <> ''";
		if ($forum_id !== null)
		{
			$where .= ' AND forum_id = ' . (int) $forum_id;
		}

		$sql = 'SELECT COUNT(topic_id) AS total
			FROM ' . $this->topics_table . '
			WHERE ' . $where;
		$result = $this->db->sql_query($sql);
		$total = (int) $this->db->sql_fetchfield('total');
		$this->db->sql_freeresult($result);

		return $total;
	}

	public function update_hash_if_current($topic_id, $old_hash, $revision, $new_hash)
	{
		$sql = 'UPDATE ' . $this->topics_table . '
			SET ' . $this->db->sql_build_array('UPDATE', ['topic_password' => $new_hash]) . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND topic_password_revision = ' . (int) $revision . "
				AND topic_password = '" . $this->db->sql_escape($old_hash) . "'";
		$this->db->sql_query($sql);

		return (int) $this->db->sql_affectedrows() === 1;
	}

	public function password_revision($topic_id)
	{
		$sql = 'SELECT topic_password_revision
			FROM ' . $this->topics_table . '
			WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$revision = $this->db->sql_fetchfield('topic_password_revision');
		$this->db->sql_freeresult($result);

		return $revision === false ? null : (int) $revision;
	}

	public function protected_ids(array $topic_ids)
	{
		if (!$topic_ids)
		{
			return [];
		}

		$sql = 'SELECT topic_id
			FROM ' . $this->topics_table . "
			WHERE topic_password <> ''
				AND " . $this->db->sql_in_set('topic_id', array_map('intval', $topic_ids));
		$result = $this->db->sql_query($sql);
		$protected = [];
		while ($topic_id = $this->db->sql_fetchfield('topic_id'))
		{
			$protected[(int) $topic_id] = true;
		}
		$this->db->sql_freeresult($result);

		return $protected;
	}

	public function all_protected_ids()
	{
		$sql = 'SELECT topic_id
			FROM ' . $this->topics_table . "
			WHERE topic_password <> ''";
		$result = $this->db->sql_query($sql);
		$protected = [];
		while (($topic_id = $this->db->sql_fetchfield('topic_id')) !== false)
		{
			$protected[] = (int) $topic_id;
		}
		$this->db->sql_freeresult($result);

		return $protected;
	}

	public function list_protected($after = 0, $limit = 100)
	{
		$sql = 'SELECT topic_id, forum_id, topic_title, topic_password_revision
			FROM ' . $this->topics_table . "
			WHERE topic_password <> ''
				AND topic_id > " . (int) $after . '
			ORDER BY topic_id ASC';
		$result = $this->db->sql_query_limit($sql, max(1, (int) $limit));
		$rows = [];
		while ($row = $this->db->sql_fetchrow($result))
		{
			$rows[] = [
				'topic_id' => (int) $row['topic_id'],
				'forum_id' => (int) $row['forum_id'],
				'topic_title' => (string) $row['topic_title'],
				'topic_password_revision' => (int) $row['topic_password_revision'],
			];
		}
		$this->db->sql_freeresult($result);

		return $rows;
	}
}
