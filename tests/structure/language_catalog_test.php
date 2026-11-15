<?php

namespace leinad4mind\topicpassword\tests\structure;

use PHPUnit\Framework\TestCase;

class language_catalog_test extends TestCase
{
	public function test_all_catalogs_have_the_same_keys_and_placeholders()
	{
		if (!defined('IN_PHPBB'))
		{
			define('IN_PHPBB', true);
		}

		$root = dirname(__DIR__, 2) . '/language';
		foreach (['common.php', 'info_acp_topicpassword.php'] as $filename)
		{
			$english = $this->load_catalog($root . '/en/' . $filename);
			foreach (['pt', 'pt_br', 'pt_preao', 'fr', 'es', 'hu'] as $iso)
			{
				$path = $root . '/' . $iso . '/' . $filename;
				$this->assertFileExists($path);
				$catalog = $this->load_catalog($path);
				$this->assertSame(array_keys($english), array_keys($catalog), $iso . ':' . $filename . ' language keys differ');
				foreach ($english as $key => $text)
				{
					$this->assertSame(
						$this->placeholder_signature($text),
						$this->placeholder_signature($catalog[$key]),
						$iso . ':' . $filename . ':' . $key . ' placeholders differ'
					);
				}
			}
		}
	}

	private function load_catalog($file)
	{
		$lang = [];
		include $file;

		return $lang;
	}

	private function placeholder_signature($value)
	{
		if (is_array($value))
		{
			$signature = [];
			foreach ($value as $key => $item)
			{
				$signature[$key] = $this->placeholder_signature($item);
			}

			return $signature;
		}

		preg_match_all('/%(?:\d+\$)?[ds]/', (string) $value, $matches);
		sort($matches[0]);

		return $matches[0];
	}
}
