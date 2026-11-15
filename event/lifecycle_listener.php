<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class lifecycle_listener implements EventSubscriberInterface
{
	protected $request;
	protected $user;
	protected $topics;
	protected $grants;
	protected $attempts;
	protected $access_table;
	protected $attempts_table;

	public function __construct($request, $user, $topics, $grants, $attempts, $access_table, $attempts_table)
	{
		$this->request = $request;
		$this->user = $user;
		$this->topics = $topics;
		$this->grants = $grants;
		$this->attempts = $attempts;
		$this->access_table = $access_table;
		$this->attempts_table = $attempts_table;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.session_kill_after' => 'session_kill',
			'core.session_gc_after' => 'session_gc',
			'core.delete_topics_before_query' => 'delete_topics',
			'core.mcp_main_modify_fork_sql' => 'fork_topic',
			'core.mcp_main_before' => 'guard_split_merge',
		];
	}

	public function session_kill($event)
	{
		$this->grants->revoke_session((string) $event['session_id']);
	}

	public function session_gc()
	{
		$this->grants->garbage_collect();
		$this->attempts->garbage_collect(time() - 86400);
	}

	public function delete_topics($event)
	{
		$tables = $event['table_ary'];
		$tables[] = $this->access_table;
		$tables[] = $this->attempts_table;
		$event['table_ary'] = array_values(array_unique($tables));
	}

	public function fork_topic($event)
	{
		$topic = $event['topic_row'];
		$sql = $event['sql_ary'];
		$sql['topic_password'] = isset($topic['topic_password']) ? (string) $topic['topic_password'] : '';
		$sql['topic_password_revision'] = isset($topic['topic_password_revision'])
			? (int) $topic['topic_password_revision']
			: 0;
		$event['sql_ary'] = $sql;
	}

	public function guard_split_merge($event)
	{
		if (!in_array($event['action'], [
			'split_all',
			'split_beyond',
			'merge_posts',
			'merge_select',
			'merge_topic',
			'merge_topics',
		], true))
		{
			return;
		}

		$topic_ids = array_merge($this->request->variable('topic_id_list', [0]), [
			$this->request->variable('t', 0),
			$this->request->variable('topic_id', 0),
			$this->request->variable('to_topic_id', 0),
		]);
		$topic_ids = array_values(array_filter(array_map('intval', $topic_ids)));
		if ($this->topics->protected_ids($topic_ids))
		{
			trigger_error($this->user->lang('TOPICPASSWORD_SPLIT_MERGE_BLOCKED'), E_USER_WARNING);
		}
	}
}
