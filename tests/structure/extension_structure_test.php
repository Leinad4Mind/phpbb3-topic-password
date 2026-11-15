<?php

namespace leinad4mind\topicpassword\tests\structure;

use PHPUnit\Framework\TestCase;

class extension_structure_test extends TestCase
{
	public function test_declared_services_have_loadable_classes()
	{
		$root = dirname(__DIR__, 2);
		$services = file_get_contents($root . '/config/services.yml');
		preg_match_all('/^\s*class:\s+([^\r\n]+)$/m', $services, $matches);

		$this->assertNotEmpty($matches[1]);
		foreach ($matches[1] as $class)
		{
			$class = trim($class);
			$prefix = 'leinad4mind\\topicpassword\\';
			$this->assertStringStartsWith($prefix, $class);
			$file = $root . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
			$this->assertFileExists($file, $class . ' has no source file');
		}
	}

	public function test_templates_never_receive_or_render_the_stored_hash()
	{
		$root = dirname(__DIR__, 2);
		$posting = file_get_contents($root . '/event/posting_listener.php');
		$template = file_get_contents($root . '/styles/prosilver/template/event/posting_editor_subject_after.html');

		$this->assertStringNotContainsString("page_data['TOPIC_PASSWORD']", $posting);
		$this->assertStringNotContainsString('topic_password_revision', $template);
		$this->assertStringNotContainsString('topic_password_hash', $template);
	}

	public function test_all_access_barrier_events_are_registered()
	{
		$listener = file_get_contents(dirname(__DIR__, 2) . '/event/access_listener.php');
		foreach ([
			'core.viewtopic_before_f_read_check',
			'core.modify_posting_auth',
			'core.download_file_send_to_browser_before',
			'core.ucp_pm_compose_compose_pm_basic_info_query_before',
		] as $event)
		{
			$this->assertStringContainsString($event, $listener);
		}
	}

	public function test_route_targets_existing_controller_method()
	{
		$root = dirname(__DIR__, 2);
		$routing = file_get_contents($root . '/config/routing.yml');
		$this->assertStringContainsString('leinad4mind.topicpassword.controller.access:handle', $routing);
		$this->assertTrue(method_exists(\leinad4mind\topicpassword\controller\access_controller::class, 'handle'));
	}

	public function test_schema_definition_is_stable_for_resumable_migrations()
	{
		$schema = file_get_contents(dirname(__DIR__, 2) . '/migrations/v400_schema.php');
		$start = strpos($schema, 'public function update_schema()');
		$end = strpos($schema, 'public function revert_schema()');
		$update_schema = substr($schema, $start, $end - $start);

		$this->assertStringNotContainsString('sql_column_exists', $update_schema);
		$this->assertStringNotContainsString('sql_table_exists', $update_schema);
		$this->assertStringContainsString("'change_columns'", $update_schema);
	}
}
