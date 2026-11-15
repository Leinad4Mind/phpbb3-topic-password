<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class posting_listener implements EventSubscriberInterface
{
	protected $request;
	protected $user;
	protected $editor;
	protected $grants;
	protected $config;
	protected $pending;

	public function __construct($request, $user, $editor, $grants, $config)
	{
		$this->request = $request;
		$this->user = $user;
		$this->editor = $editor;
		$this->grants = $grants;
		$this->config = $config;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.posting_modify_submission_errors' => 'validate',
			'core.posting_modify_submit_post_before' => 'prepare',
			'core.submit_post_modify_sql_data' => 'persist',
			'core.posting_modify_submit_post_after' => 'after_submit',
			'core.posting_modify_template_vars' => 'template_vars',
		];
	}

	public function validate($event)
	{
		if (!(bool) $event['submit'] || !$this->can_manage($event['mode'], (int) $event['post_id'], $event['post_data']))
		{
			return;
		}

		$post_data = $event['post_data'];
		$existing = isset($post_data['topic_password']) ? (string) $post_data['topic_password'] : '';
		$this->pending = $this->editor->validate(
			$existing,
			$this->request->variable('topic_password_remove', false),
			$this->request->variable('topic_password', '', true),
			$this->request->variable('topic_password_confirm', '', true),
			!empty($post_data['forum_allow_topic_password']) || $existing !== ''
		);
		$this->pending['topic_id'] = (int) $event['topic_id'];
		$this->pending['revision'] = isset($post_data['topic_password_revision'])
			? (int) $post_data['topic_password_revision']
			: 0;

		$error = $event['error'];
		foreach ($this->pending['errors'] as $key)
		{
			$error[] = $this->user->lang($key);
		}
		$event['error'] = $error;
	}

	public function prepare($event)
	{
		if (!$this->pending || $this->pending['action'] !== 'set')
		{
			return;
		}

		$hash = $this->editor->hash($this->pending['password']);
		if ($hash === false)
		{
			trigger_error('TOPICPASSWORD_HASH_FAILED', E_USER_ERROR);
		}
		$this->pending['hash'] = $hash;
		$this->pending['password'] = '';
	}

	public function persist($event)
	{
		if (!$this->pending || $this->pending['action'] === 'keep')
		{
			return;
		}

		$sql_data = $event['sql_data'];
		if (!isset($sql_data[TOPICS_TABLE]['sql']))
		{
			return;
		}

		$sql_data[TOPICS_TABLE]['sql']['topic_password'] = $this->pending['action'] === 'set'
			? $this->pending['hash']
			: '';
		$sql_data[TOPICS_TABLE]['sql']['topic_password_revision'] = $this->pending['revision'] + 1;
		$event['sql_data'] = $sql_data;
	}

	public function after_submit($event)
	{
		if ($this->pending && $this->pending['action'] !== 'keep')
		{
			$topic_id = (int) $event['topic_id'];
			if (!$topic_id && isset($event['data']['topic_id']))
			{
				$topic_id = (int) $event['data']['topic_id'];
			}
			if ($topic_id)
			{
				$this->grants->revoke_topic($topic_id);
				$this->config->increment('topic_password_search_revision', 1);
			}
		}
		$this->pending = null;
	}

	public function template_vars($event)
	{
		if (!$this->can_manage($event['mode'], (int) $event['post_id'], $event['post_data']))
		{
			return;
		}

		$post_data = $event['post_data'];
		$page_data = $event['page_data'];
		$page_data['S_TOPIC_PASSWORD_AVAILABLE'] = !empty($post_data['forum_allow_topic_password'])
			|| !empty($post_data['topic_password']);
		$page_data['S_TOPIC_PASSWORD_PROTECTED'] = !empty($post_data['topic_password']);
		$event['page_data'] = $page_data;
	}

	private function can_manage($mode, $post_id, array $post_data)
	{
		if ($mode === 'post')
		{
			return true;
		}

		return $mode === 'edit'
			&& isset($post_data['topic_first_post_id'])
			&& (int) $post_data['topic_first_post_id'] === $post_id;
	}
}
