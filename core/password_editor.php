<?php

namespace leinad4mind\topicpassword\core;

class password_editor
{
	protected $passwords;

	public function __construct($passwords)
	{
		$this->passwords = $passwords;
	}

	public function validate($existing, $remove, $password, $confirmation, $can_create)
	{
		$errors = [];
		$has_new_password = $password !== '' || $confirmation !== '';

		if ($remove && $has_new_password)
		{
			$errors[] = 'TOPICPASSWORD_REMOVE_AND_SET';
		}
		if ($has_new_password && !$can_create)
		{
			$errors[] = 'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM';
		}
		if ($has_new_password && $password !== $confirmation)
		{
			$errors[] = 'TOPICPASSWORD_MISMATCH';
		}
		if ($has_new_password && $this->character_length($password) < 8)
		{
			$errors[] = 'TOPICPASSWORD_TOO_SHORT';
		}
		if ($has_new_password && strlen($password) > 72)
		{
			$errors[] = 'TOPICPASSWORD_TOO_LONG';
		}

		$action = 'keep';
		if ($remove)
		{
			$action = 'remove';
		}
		else if ($has_new_password && !$errors)
		{
			$action = 'set';
		}

		return [
			'action' => $action,
			'password' => $password,
			'errors' => $errors,
			'was_protected' => $existing !== '',
		];
	}

	public function hash($password)
	{
		return $this->passwords->hash($password);
	}

	private function character_length($value)
	{
		if (function_exists('utf8_strlen'))
		{
			return utf8_strlen($value);
		}

		return preg_match_all('/./us', $value, $matches) ?: 0;
	}
}
