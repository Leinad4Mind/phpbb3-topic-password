<?php

namespace leinad4mind\topicpassword\tests\event;

use ArrayObject;
use leinad4mind\topicpassword\event\visibility_listener;
use PHPUnit\Framework\TestCase;

class visibility_listener_test extends TestCase
{
	public function test_database_search_filters_are_applied_before_counting_and_pagination()
	{
		$listener = $this->listener();

		$native_keywords = new ArrayObject(['sql_where' => ['p.post_visibility = 1']]);
		$listener->filter_native_keywords($native_keywords);
		$this->assertCount(2, $native_keywords['sql_where']);
		$this->assertUnprotectedCondition($native_keywords['sql_where'][1]);

		$native_author = new ArrayObject(['sql_time' => ' AND p.post_time > 0']);
		$listener->filter_native_author($native_author);
		$this->assertUnprotectedCondition($native_author['sql_time']);

		$fulltext_keywords = new ArrayObject(['sql_match_where' => '']);
		$listener->filter_fulltext_keywords($fulltext_keywords);
		$this->assertUnprotectedCondition($fulltext_keywords['sql_match_where']);

		$fulltext_author = new ArrayObject(['m_approve_fid_sql' => ' AND p.post_visibility = 1']);
		$listener->filter_fulltext_author($fulltext_author);
		$this->assertUnprotectedCondition($fulltext_author['m_approve_fid_sql']);
	}

	public function test_cache_key_contains_protection_revision()
	{
		$listener = $this->listener([], ['topic_password_search_revision' => 9]);
		$event = new ArrayObject(['search_key_array' => ['query']]);

		$listener->namespace_search_cache($event);

		$this->assertSame(['query', 'topicpassword-unprotected', 9], $event['search_key_array']);
	}

	public function test_sphinx_excludes_protected_topic_ids_inside_search_engine()
	{
		$sphinx = new visibility_sphinx_spy();
		$listener = $this->listener([8, 13]);
		$event = new ArrayObject(['sphinx' => $sphinx]);

		$listener->filter_sphinx($event);

		$this->assertSame([['topic_id', [8, 13], true]], $sphinx->filters);
	}

	public function test_final_rowset_defence_remains_enabled()
	{
		$listener = $this->listener([3]);
		$event = new ArrayObject([
			'rowset' => [
				1 => ['topic_id' => 2],
				2 => ['topic_id' => 3],
			],
		]);

		$listener->defend_search_rowset($event);

		$this->assertSame([1 => ['topic_id' => 2]], $event['rowset']);
	}

	public function test_premade_and_database_unread_queries_are_filtered_before_limit()
	{
		$listener = $this->listener();
		$premade = new ArrayObject([
			'sql' => 'SELECT t.topic_id FROM phpbb_topics t WHERE t.topic_moved_id = 0 ORDER BY t.topic_id',
			'show_results' => 'topics',
		]);
		$listener->filter_premade_search($premade);
		$this->assertUnprotectedCondition($premade['sql'], 't.topic_id');
		$this->assertLessThan(
			strpos($premade['sql'], 'ORDER BY'),
			strpos($premade['sql'], 'NOT EXISTS')
		);

		$unread = new ArrayObject([
			'sql_array' => ['WHERE' => 't.topic_last_post_time > 0 ORDER BY t.topic_last_post_time DESC'],
		]);
		$listener->filter_unread_topics($unread);
		$this->assertUnprotectedCondition($unread['sql_array']['WHERE'], 't.topic_id');
		$this->assertLessThan(
			strpos($unread['sql_array']['WHERE'], 'ORDER BY'),
			strpos($unread['sql_array']['WHERE'], 'NOT EXISTS')
		);
	}

	public function test_cookie_based_unread_search_fails_closed()
	{
		$listener = $this->listener([], ['load_db_lastread' => 0]);
		$event = new ArrayObject(['search_id' => 'unreadposts']);

		$listener->guard_cookie_unread_search($event);

		$this->assertSame('topicpassword_unread_unavailable', $event['search_id']);
	}

	private function listener(array $protected = [], array $config = [])
	{
		$topics = new visibility_topics_stub($protected);

		return new visibility_listener('phpbb_topics', $topics, new ArrayObject($config));
	}

	private function assertUnprotectedCondition($sql, $topic_id_column = 'p.topic_id')
	{
		$this->assertStringContainsString('NOT EXISTS (SELECT 1 FROM phpbb_topics topicpassword_hidden', $sql);
		$this->assertStringContainsString('topicpassword_hidden.topic_id = ' . $topic_id_column, $sql);
		$this->assertStringContainsString("topicpassword_hidden.topic_password <> ''", $sql);
	}
}

class visibility_topics_stub
{
	private $protected;

	public function __construct(array $protected)
	{
		$this->protected = $protected;
	}

	public function all_protected_ids()
	{
		return $this->protected;
	}

	public function protected_ids(array $topic_ids)
	{
		return array_fill_keys(array_values(array_intersect($topic_ids, $this->protected)), true);
	}
}

class visibility_sphinx_spy
{
	public $filters = [];

	public function SetFilter($attribute, array $values, $exclude = false)
	{
		$this->filters[] = [$attribute, $values, $exclude];
	}
}
