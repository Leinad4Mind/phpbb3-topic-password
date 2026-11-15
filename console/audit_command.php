<?php

namespace leinad4mind\topicpassword\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class audit_command extends Command
{
	protected $topics;

	public function __construct($topics)
	{
		parent::__construct();
		$this->topics = $topics;
	}

	protected function configure()
	{
		$this
			->setName('topicpassword:audit')
			->setDescription('List protected topic IDs without exporting password hashes.')
			->addOption('json', null, InputOption::VALUE_NONE, 'Write one JSON object per topic.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$count = $this->topics->count_protected();
		$output->writeln('Protected topics: ' . $count);
		$after = 0;
		do
		{
			$rows = $this->topics->list_protected($after, 100);
			foreach ($rows as $row)
			{
				$after = $row['topic_id'];
				if ($input->getOption('json'))
				{
					$output->writeln(json_encode($row, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
				}
				else
				{
					$output->writeln(sprintf(
						'%d\tforum=%d\trevision=%d\t%s',
						$row['topic_id'],
						$row['forum_id'],
						$row['topic_password_revision'],
						$row['topic_title']
					));
				}
			}
		}
		while (count($rows) === 100);

		return 0;
	}
}
