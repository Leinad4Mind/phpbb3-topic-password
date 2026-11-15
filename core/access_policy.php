<?php

namespace leinad4mind\topicpassword\core;

class access_policy
{
	protected $auth;
	protected $visibility;

	public function __construct($auth, $visibility)
	{
		$this->auth = $auth;
		$this->visibility = $visibility;
	}

	public function is_protected(array $topic)
	{
		return !empty($topic['topic_password']);
	}

	public function is_bypass(array $topic)
	{
		$forum_id = (int) $topic['forum_id'];
		return $this->auth->acl_get('a_') || $this->auth->acl_get('m_', $forum_id);
	}

	public function native_can_read(array $topic)
	{
		$forum_id = (int) $topic['forum_id'];
		return $this->auth->acl_get('f_read', $forum_id)
			&& $this->visibility->is_visible('topic', $forum_id, $topic);
	}
}
