<?php

namespace leinad4mind\topicpassword\controller;

class access_controller
{
	protected $helper;
	protected $language;
	protected $request;
	protected $template;
	protected $user;
	protected $policy;
	protected $access;
	protected $topics;
	protected $return_target;

	public function __construct($helper, $language, $request, $template, $user, $policy, $access, $topics, $return_target)
	{
		$this->helper = $helper;
		$this->language = $language;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->policy = $policy;
		$this->access = $access;
		$this->topics = $topics;
		$this->return_target = $return_target;
	}

	public function handle($topic_id)
	{
		$this->language->add_lang('common', 'leinad4mind/topicpassword');
		$topic = $this->topics->find((int) $topic_id);
		if (!$topic)
		{
			throw new \phpbb\exception\http_exception(404, 'NO_TOPIC');
		}
		if (!$this->policy->native_can_read($topic))
		{
			throw new \phpbb\exception\http_exception(403, 'SORRY_AUTH_READ');
		}

		$target = $this->return_target->for_topic((int) $topic['topic_id']);
		if (!$this->policy->is_protected($topic) || $this->access->is_allowed($topic))
		{
			redirect($target);
		}

		$form_name = 'topicpassword_access_' . (int) $topic['topic_id'];
		add_form_key($form_name);
		$error = '';
		$wait = 0;

		if ($this->request->is_set_post('submit'))
		{
			if (!check_form_key($form_name))
			{
				$error = $this->language->lang('FORM_INVALID');
			}
			else
			{
				$result = $this->access->verify_and_grant(
					$topic,
					$this->request->variable('topic_password', '', true)
				);
				if ($result['status'] === \leinad4mind\topicpassword\core\access_manager::OK)
				{
					redirect($target);
				}
				if ($result['status'] === \leinad4mind\topicpassword\core\access_manager::BLOCKED)
				{
					$wait = (int) $result['wait'];
					$error = $this->language->lang('TOPICPASSWORD_RATE_LIMITED', $wait);
				}
				else if ($result['status'] === \leinad4mind\topicpassword\core\access_manager::RACE)
				{
					$error = $this->language->lang('TOPICPASSWORD_CHANGED_RETRY');
				}
				else
				{
					$error = $this->language->lang('TOPICPASSWORD_INVALID');
				}
			}
		}

		$this->template->assign_vars([
			'TOPIC_TITLE' => $topic['topic_title'],
			'ERROR' => $error,
			'S_ERROR' => $error !== '',
			'S_TOPIC_PASSWORD_BLOCKED' => $wait > 0,
			'TOPIC_PASSWORD_WAIT' => $wait,
			'U_FORUM' => $this->return_target->for_forum((int) $topic['forum_id']),
		]);

		return $this->helper->render(
			'topic_password_login.html',
			'TOPICPASSWORD_LOGIN',
			$wait > 0 ? 429 : 200
		);
	}
}
