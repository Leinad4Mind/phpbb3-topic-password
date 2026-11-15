<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class acp_forum_listener implements EventSubscriberInterface
{
	protected $language;
	protected $request;
	protected $topics;

	public function __construct($language, $request, $topics)
	{
		$this->language = $language;
		$this->request = $request;
		$this->topics = $topics;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.acp_manage_forums_request_data' => 'request_data',
			'core.acp_manage_forums_initialise_data' => 'initialise_data',
			'core.acp_manage_forums_display_form' => 'display_form',
			'core.acp_manage_forums_update_data_before' => 'update_data',
			'core.acp_extensions_run_action_before' => 'guard_disable',
		];
	}

	public function request_data($event)
	{
		$data = $event['forum_data'];
		$data['forum_allow_topic_password'] = $this->request->variable('forum_allow_topic_password', false);
		$event['forum_data'] = $data;
	}

	public function initialise_data($event)
	{
		$data = $event['forum_data'];
		if (!isset($data['forum_allow_topic_password']))
		{
			$data['forum_allow_topic_password'] = 0;
		}
		$event['forum_data'] = $data;
	}

	public function display_form($event)
	{
		$data = $event['template_data'];
		$forum_data = $event['forum_data'];
		$data['S_FORUM_ALLOW_TOPIC_PASSWORD'] = !empty($forum_data['forum_allow_topic_password']);
		$event['template_data'] = $data;
	}

	public function update_data($event)
	{
		$sql = $event['forum_data_sql'];
		$data = $event['forum_data'];
		$sql['forum_allow_topic_password'] = !empty($data['forum_allow_topic_password']) ? 1 : 0;
		$event['forum_data_sql'] = $sql;
	}

	public function guard_disable($event)
	{
		if ($event['action'] !== 'disable' || $event['ext_name'] !== 'leinad4mind/topicpassword')
		{
			return;
		}

		$count = $this->topics->count_protected();
		if ($count)
		{
			$this->language->add_lang('info_acp_topicpassword', 'leinad4mind/topicpassword');
			$event['action'] = 'none';
			trigger_error($this->language->lang('TOPICPASSWORD_DISABLE_BLOCKED', $count), E_USER_WARNING);
		}
	}
}
