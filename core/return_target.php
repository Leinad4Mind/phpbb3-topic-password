<?php

namespace leinad4mind\topicpassword\core;

class return_target
{
	protected $root_path;
	protected $php_ext;

	public function __construct($root_path, $php_ext)
	{
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	public function for_topic($topic_id)
	{
		return append_sid($this->root_path . 'viewtopic.' . $this->php_ext, 't=' . (int) $topic_id);
	}

	public function for_forum($forum_id)
	{
		return (int) $forum_id > 0
			? append_sid($this->root_path . 'viewforum.' . $this->php_ext, 'f=' . (int) $forum_id)
			: append_sid($this->root_path . 'index.' . $this->php_ext);
	}
}
