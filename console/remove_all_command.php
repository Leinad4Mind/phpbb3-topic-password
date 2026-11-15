<?php

namespace leinad4mind\topicpassword\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class remove_all_command extends Command
{
	protected $db;
	protected $log;
	protected $topics;
	protected $topics_table;
	protected $access_table;
	protected $attempts_table;
	protected $config;

	public function __construct($db, $log, $topics, $topics_table, $access_table, $attempts_table, $config)
	{
		parent::__construct();
		$this->db = $db;
		$this->log = $log;
		$this->topics = $topics;
		$this->topics_table = $topics_table;
		$this->access_table = $access_table;
		$this->attempts_table = $attempts_table;
		$this->config = $config;
	}

	protected function configure()
	{
		$this
			->setName('topicpassword:remove-all')
			->setDescription('Remove every topic password so the extension can be disabled safely.')
			->addOption('confirm', null, InputOption::VALUE_NONE, 'Required destructive-operation confirmation.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$count = $this->topics->count_protected();
		if (!$input->getOption('confirm'))
		{
			$output->writeln('<error>No changes made. Re-run with --confirm after reviewing topicpassword:audit.</error>');
			return 2;
		}
		if ($count === 0)
		{
			$output->writeln('<info>No protected topics found.</info>');
			return 0;
		}

		$this->db->sql_transaction('begin');
		$sql = 'UPDATE ' . $this->topics_table . "
			SET topic_password = '',
				topic_password_revision = topic_password_revision + 1
			WHERE topic_password <> ''";
		$this->db->sql_query($sql);
		$this->db->sql_query('DELETE FROM ' . $this->access_table);
		$this->db->sql_query('DELETE FROM ' . $this->attempts_table);
		$this->db->sql_transaction('commit');
		$this->config->increment('topic_password_search_revision', 1);

		$this->log->add(
			'admin',
			ANONYMOUS,
			'',
			'LOG_TOPIC_PASSWORD_REMOVE_ALL',
			false,
			[$count]
		);
		$output->writeln('<info>Removed protection from ' . $count . ' topic(s).</info>');

		return 0;
	}
}
