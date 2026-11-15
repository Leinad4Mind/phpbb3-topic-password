<?php

namespace leinad4mind\topicpassword\tests\core;

use leinad4mind\topicpassword\core\password_editor;
use PHPUnit\Framework\TestCase;

class password_editor_test extends TestCase
{
	public function test_blank_fields_keep_existing_hash_without_exposing_it()
	{
		$editor = new password_editor(new fake_password_manager());
		$result = $editor->validate('$2y$stored-hash', false, '', '', true);

		$this->assertSame('keep', $result['action']);
		$this->assertSame('', $result['password']);
		$this->assertSame([], $result['errors']);
		$this->assertTrue($result['was_protected']);
	}

	public function test_remove_and_set_are_mutually_exclusive()
	{
		$editor = new password_editor(new fake_password_manager());
		$result = $editor->validate('$2y$stored-hash', true, 'abcdefgh', 'abcdefgh', true);

		$this->assertSame('remove', $result['action']);
		$this->assertContains('TOPICPASSWORD_REMOVE_AND_SET', $result['errors']);
	}

	public function test_new_password_requires_forum_permission_confirmation_and_limits()
	{
		$editor = new password_editor(new fake_password_manager());
		$result = $editor->validate('', false, 'short', 'different', false);

		$this->assertSame('keep', $result['action']);
		$this->assertContains('TOPICPASSWORD_NOT_ALLOWED_IN_FORUM', $result['errors']);
		$this->assertContains('TOPICPASSWORD_MISMATCH', $result['errors']);
		$this->assertContains('TOPICPASSWORD_TOO_SHORT', $result['errors']);
	}

	public function test_unicode_length_is_counted_as_characters()
	{
		$editor = new password_editor(new fake_password_manager());
		$result = $editor->validate('', false, 'áéíóúçãõ', 'áéíóúçãõ', true);

		$this->assertSame('set', $result['action']);
		$this->assertSame([], $result['errors']);
	}
}

class fake_password_manager
{
	public function hash($password)
	{
		return 'hashed:' . $password;
	}
}
