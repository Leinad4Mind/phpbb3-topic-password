<?php

namespace leinad4mind\topicpassword\repository;

class attempt_repository
{
	protected $db;
	protected $table;

	public function __construct($db, $table)
	{
		$this->db = $db;
		$this->table = $table;
	}

	public function wait_seconds($topic_id, array $actor_keys, $now)
	{
		if (!$actor_keys)
		{
			return 0;
		}

		$sql = 'SELECT MAX(blocked_until) AS blocked_until
			FROM ' . $this->table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND ' . $this->db->sql_in_set('actor_key', array_values($actor_keys));
		$result = $this->db->sql_query($sql);
		$blocked_until = (int) $this->db->sql_fetchfield('blocked_until');
		$this->db->sql_freeresult($result);

		return max(0, $blocked_until - (int) $now);
	}

	public function record_failure($topic_id, $actor_key, $now, $limit, $window, $lockout)
	{
		$topic_id = (int) $topic_id;
		$now = (int) $now;
		$limit = max(1, (int) $limit);
		$cutoff = $now - (int) $window;
		$blocked_until = $now + (int) $lockout;

		// Increment in the database so concurrent failures cannot overwrite
		// one another with the same counter value.
		$sql = 'UPDATE ' . $this->table . '
			SET attempt_count = attempt_count + 1,
				last_attempt = ' . $now . ',
				blocked_until = CASE
					WHEN attempt_count + 1 >= ' . $limit . ' THEN ' . $blocked_until . '
					ELSE 0
				END
			WHERE topic_id = ' . (int) $topic_id . "
				AND actor_key = '" . $this->db->sql_escape($actor_key) . "'
				AND first_attempt > " . $cutoff;
		$this->db->sql_query($sql);
		if ((int) $this->db->sql_affectedrows() > 0)
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE topic_id = ' . (int) $topic_id . "
				AND actor_key = '" . $this->db->sql_escape($actor_key) . "'
				AND first_attempt <= " . $cutoff;
		$this->db->sql_query($sql);

		$sql = 'INSERT INTO ' . $this->table . ' ' . $this->db->sql_build_array('INSERT', [
			'topic_id' => $topic_id,
			'actor_key' => $actor_key,
			'attempt_count' => 1,
			'first_attempt' => $now,
			'last_attempt' => $now,
			'blocked_until' => $limit === 1 ? $blocked_until : 0,
		]);
		$this->db->sql_return_on_error(true);
		$inserted = $this->db->sql_query($sql);
		$this->db->sql_return_on_error(false);

		if ($inserted === false)
		{
			// Another request inserted the same actor between our UPDATE and
			// INSERT. Count this failure atomically against that new row.
			$sql = 'UPDATE ' . $this->table . '
				SET attempt_count = attempt_count + 1,
					last_attempt = ' . $now . ',
					blocked_until = CASE
						WHEN attempt_count + 1 >= ' . $limit . ' THEN ' . $blocked_until . '
						ELSE 0
					END
				WHERE topic_id = ' . (int) $topic_id . "
					AND actor_key = '" . $this->db->sql_escape($actor_key) . "'";
			$this->db->sql_query($sql);
		}
	}

	public function clear($topic_id, array $actor_keys)
	{
		if (!$actor_keys)
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->table . '
			WHERE topic_id = ' . (int) $topic_id . '
				AND ' . $this->db->sql_in_set('actor_key', array_values($actor_keys));
		$this->db->sql_query($sql);
	}

	public function delete_topic($topic_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->table . '
			WHERE topic_id = ' . (int) $topic_id);
	}

	public function garbage_collect($before)
	{
		$this->db->sql_query('DELETE FROM ' . $this->table . '
			WHERE last_attempt < ' . (int) $before);
	}

}
