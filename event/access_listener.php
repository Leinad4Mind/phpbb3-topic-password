<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class access_listener implements EventSubscriberInterface
{
	protected $helper;
	protected $language;
	protected $policy;
	protected $access;
	protected $topics;

	public function __construct($helper, $language, $policy, $access, $topics)
	{
		$this->helper = $helper;
		$this->language = $language;
		$this->policy = $policy;
		$this->access = $access;
		$this->topics = $topics;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'load_language',
			'core.viewtopic_before_f_read_check' => 'guard_viewtopic',
			'core.modify_posting_auth' => 'guard_posting',
			'core.download_file_send_to_browser_before' => 'guard_attachment',
			'core.ucp_pm_compose_compose_pm_basic_info_query_before' => 'guard_pm_quote',
		];
	}

	public function load_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'leinad4mind/topicpassword',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function guard_viewtopic($event)
	{
		$topic = $event['topic_data'];
		if ($this->policy->native_can_read($topic) && !$this->access->is_allowed($topic))
		{
			redirect($this->challenge_url((int) $event['topic_id']));
		}
	}

	public function guard_posting($event)
	{
		if (!(bool) $event['is_authed'] || !(int) $event['topic_id'])
		{
			return;
		}

		$topic = $this->topics->find((int) $event['topic_id']);
		if ($topic && $this->policy->native_can_read($topic) && !$this->access->is_allowed($topic))
		{
			$error = $event['error'];
			$error[] = $this->required_message((int) $topic['topic_id']);
			$event['error'] = $error;
			$event['is_authed'] = false;
		}
	}

	public function guard_attachment($event)
	{
		$attachment = $event['attachment'];
		if (!empty($attachment['topic_id']))
		{
			$this->guard_topic_id((int) $attachment['topic_id']);
		}
	}

	public function guard_pm_quote($event)
	{
		if ($event['action'] !== 'quotepost' || empty($event['msg_id']))
		{
			return;
		}

		$topic = $this->topics->find_by_post((int) $event['msg_id']);
		if ($topic && $this->policy->native_can_read($topic) && !$this->access->is_allowed($topic))
		{
			trigger_error($this->required_message((int) $topic['topic_id']), E_USER_WARNING);
		}
	}

	private function guard_topic_id($topic_id)
	{
		$topic = $this->topics->find($topic_id);
		if ($topic && $this->policy->native_can_read($topic) && !$this->access->is_allowed($topic))
		{
			trigger_error($this->required_message($topic_id), E_USER_WARNING);
		}
	}

	private function required_message($topic_id)
	{
		return $this->language->lang(
			'TOPICPASSWORD_REQUIRED_LINK',
			'<a href="' . $this->challenge_url($topic_id) . '">',
			'</a>'
		);
	}

	private function challenge_url($topic_id)
	{
		return $this->helper->route('leinad4mind_topicpassword_access', ['topic_id' => (int) $topic_id]);
	}
}
