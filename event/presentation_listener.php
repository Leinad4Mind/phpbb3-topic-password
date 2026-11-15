<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class presentation_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.viewforum_modify_topicrow' => 'topic_row',
		];
	}

	public function topic_row($event)
	{
		$row = $event['row'];
		$topic_row = $event['topic_row'];
		$topic_row['S_TOPIC_PASSWORD_PROTECTED'] = !empty($row['topic_password']);
		$event['topic_row'] = $topic_row;
	}
}
