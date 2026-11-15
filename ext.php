<?php

/**
 * Topic Password extension for phpBB.
 *
 * @copyright (c) Topic Password contributors
 * @license GNU General Public License, version 2 (GPL-2.0)
 */

namespace leinad4mind\topicpassword;

class ext extends \phpbb\extension\base
{
	public const PHPBB_MIN = '3.3.19';
	public const PHPBB_MAX = '3.4.0-dev';
	public const PHP_MIN = '8.1.0';

	/**
	 * {@inheritdoc}
	 */
	public function is_enableable()
	{
		if (!phpbb_version_compare(PHPBB_VERSION, self::PHPBB_MIN, '>=')
			|| !phpbb_version_compare(PHPBB_VERSION, self::PHPBB_MAX, '<')
			|| version_compare(PHP_VERSION, self::PHP_MIN, '<'))
		{
			return false;
		}

		$extension_manager = $this->container->get('ext.manager');
		if ($extension_manager->is_enabled('mykeehu/topas'))
		{
			$language = $this->container->get('language');
			$language->add_lang('info_acp_topicpassword', 'leinad4mind/topicpassword');

			return $language->lang('TOPICPASSWORD_OLD_EXTENSION_ACTIVE');
		}

		return true;
	}

	/**
	 * Do not let direct extension-manager calls disable the access barrier.
	 *
	 * {@inheritdoc}
	 */
	public function disable_step($old_state)
	{
		$db = $this->container->get('dbal.conn');
		$table_prefix = $this->container->getParameter('core.table_prefix');

		$sql = 'SELECT COUNT(topic_id) AS total
			FROM ' . $table_prefix . "topics
			WHERE topic_password <> ''";
		$result = $db->sql_query($sql);
		$total = (int) $db->sql_fetchfield('total');
		$db->sql_freeresult($result);

		if ($total > 0)
		{
			$language = $this->container->get('language');
			$language->add_lang('info_acp_topicpassword', 'leinad4mind/topicpassword');
			throw new \RuntimeException($language->lang('TOPICPASSWORD_DISABLE_BLOCKED', $total));
		}

		return parent::disable_step($old_state);
	}
}
