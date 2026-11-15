<?php

namespace leinad4mind\topicpassword\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class notification_listener implements EventSubscriberInterface
{
	protected $topics;

	public function __construct($topics)
	{
		$this->topics = $topics;
	}

	public static function getSubscribedEvents()
	{
		return [
			'core.notification_manager_add_notifications_before' => 'sanitize',
			'core.notification_manager_add_notifications_for_users_modify_data' => 'sanitize',
		];
	}

	public function sanitize($event)
	{
		$data = $event['data'];
		$topic_id = isset($data['topic_id']) ? (int) $data['topic_id'] : 0;
		if (!$topic_id || !$this->topics->protected_ids([$topic_id]))
		{
			return;
		}

		// The topic title is public by policy. Individual reply subjects and all
		// message bodies remain protected, including from custom notification methods.
		$data['post_text'] = '';
		if (isset($data['message']))
		{
			$data['message'] = '';
		}
		if (isset($data['post_subject']))
		{
			$data['post_subject'] = isset($data['topic_title']) ? (string) $data['topic_title'] : '';
		}
		$event['data'] = $data;
	}
}
