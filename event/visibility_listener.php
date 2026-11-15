<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class visibility_listener implements EventSubscriberInterface
{
	protected $topics_table;
	protected $topics;
	protected $config;

	public function __construct($topics_table, $topics, $config)
	{
		$this->topics_table = $topics_table;
		$this->topics = $topics;
		$this->config = $config;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.search_native_by_keyword_modify_search_key' => 'namespace_search_cache',
			'core.search_native_by_author_modify_search_key' => 'namespace_search_cache',
			'core.search_mysql_by_keyword_modify_search_key' => 'namespace_search_cache',
			'core.search_mysql_by_author_modify_search_key' => 'namespace_search_cache',
			'core.search_postgres_by_keyword_modify_search_key' => 'namespace_search_cache',
			'core.search_postgres_by_author_modify_search_key' => 'namespace_search_cache',
			'core.search_native_keywords_count_query_before' => 'filter_native_keywords',
			'core.search_native_author_count_query_before' => 'filter_native_author',
			'core.search_mysql_keywords_main_query_before' => 'filter_fulltext_keywords',
			'core.search_postgres_keywords_main_query_before' => 'filter_fulltext_keywords',
			'core.search_mysql_author_query_before' => 'filter_fulltext_author',
			'core.search_postgres_author_count_query_before' => 'filter_fulltext_author',
			'core.search_sphinx_keywords_modify_options' => 'filter_sphinx',
			'core.search_modify_param_before' => 'guard_cookie_unread_search',
			'core.search_modify_param_after' => 'filter_premade_search',
			'core.get_unread_topics_modify_sql' => 'filter_unread_topics',
			'core.search_modify_rowset' => 'defend_search_rowset',
			'core.feed_base_modify_item_sql' => 'filter_feed',
		];
	}

	public function namespace_search_cache($event)
	{
		$search_key = $event['search_key_array'];
		$search_key[] = 'topicpassword-unprotected';
		$search_key[] = isset($this->config['topic_password_search_revision'])
			? (int) $this->config['topic_password_search_revision']
			: 1;
		$event['search_key_array'] = $search_key;
	}

	public function filter_native_keywords($event)
	{
		$sql_where = $event['sql_where'];
		$sql_where[] = $this->unprotected_condition('p.topic_id');
		$event['sql_where'] = $sql_where;
	}

	public function filter_native_author($event)
	{
		$sql_time = $event['sql_time'];
		$event['sql_time'] = $sql_time . ' AND ' . $this->unprotected_condition('p.topic_id');
	}

	public function filter_fulltext_keywords($event)
	{
		$sql_match_where = $event['sql_match_where'];
		$event['sql_match_where'] = $sql_match_where . ' AND ' . $this->unprotected_condition('p.topic_id');
	}

	public function filter_fulltext_author($event)
	{
		$m_approve_fid_sql = $event['m_approve_fid_sql'];
		$event['m_approve_fid_sql'] = $m_approve_fid_sql . ' AND ' . $this->unprotected_condition('p.topic_id');
	}

	public function filter_sphinx($event)
	{
		$protected = $this->topics->all_protected_ids();
		if (!$protected)
		{
			return;
		}

		$sphinx = $event['sphinx'];
		$sphinx->SetFilter('topic_id', $protected, true);
		$event['sphinx'] = $sphinx;
	}

	public function guard_cookie_unread_search($event)
	{
		if ($event['search_id'] === 'unreadposts' && empty($this->config['load_db_lastread']))
		{
			// phpBB exposes no event inside the cookie-tracking unread query. Returning
			// no results is safer than leaking protected topic ids or page counts.
			$event['search_id'] = 'topicpassword_unread_unavailable';
		}
	}

	public function filter_premade_search($event)
	{
		$sql = (string) $event['sql'];
		if ($sql === '')
		{
			return;
		}

		$condition = $event['show_results'] === 'posts'
			? $this->unprotected_post_condition()
			: $this->unprotected_topic_condition();
		$event['sql'] = $this->append_before_order_by($sql, $condition);
	}

	public function filter_unread_topics($event)
	{
		$sql = $event['sql_array'];
		$sql['WHERE'] = $this->append_before_order_by(
			$sql['WHERE'],
			$this->unprotected_condition('t.topic_id')
		);
		$event['sql_array'] = $sql;
	}

	public function defend_search_rowset($event)
	{
		$rowset = $event['rowset'];
		$ids = [];
		foreach ($rowset as $row)
		{
			if (isset($row['topic_id']))
			{
				$ids[] = (int) $row['topic_id'];
			}
		}
		$protected = $this->topics->protected_ids($ids);
		if ($protected)
		{
			$rowset = array_filter($rowset, static function ($row) use ($protected)
			{
				return empty($protected[(int) $row['topic_id']]);
			});
			$event['rowset'] = $rowset;
		}
	}

	public function filter_feed($event)
	{
		$sql = $event['sql_ary'];
		if (!isset($sql['FROM'][POSTS_TABLE]) || $sql['FROM'][POSTS_TABLE] !== 'p')
		{
			return;
		}

		$sql['LEFT_JOIN'][] = [
			'FROM' => [$this->topics_table => 'topicpassword_topics'],
			'ON' => 'topicpassword_topics.topic_id = p.topic_id',
		];
		$sql['WHERE'] .= " AND topicpassword_topics.topic_password = ''";
		$event['sql_ary'] = $sql;
	}

	private function unprotected_condition($topic_id_column)
	{
		if ($topic_id_column === 'p.topic_id')
		{
			return $this->unprotected_post_condition();
		}

		return $this->unprotected_topic_condition();
	}

	private function unprotected_post_condition()
	{
		return 'NOT EXISTS (SELECT 1 FROM ' . $this->topics_table . ' topicpassword_hidden'
			. ' WHERE topicpassword_hidden.topic_id = p.topic_id'
			. " AND topicpassword_hidden.topic_password <> '')";
	}

	private function unprotected_topic_condition()
	{
		return 'NOT EXISTS (SELECT 1 FROM ' . $this->topics_table . ' topicpassword_hidden'
			. ' WHERE topicpassword_hidden.topic_id = t.topic_id'
			. " AND topicpassword_hidden.topic_password <> '')";
	}

	private function append_before_order_by($sql, $condition)
	{
		$position = strripos($sql, 'ORDER BY');
		if ($position === false)
		{
			return rtrim($sql) . ' AND ' . $condition;
		}

		return rtrim(substr($sql, 0, $position)) . ' AND ' . $condition . ' '
			. substr($sql, $position);
	}
}
