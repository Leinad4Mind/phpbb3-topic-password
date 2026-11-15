<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'A legacy topic password has an ambiguous format. Migration stopped without changing it.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'A legacy topic password could not be hashed. Migration stopped.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Topic passwords exist, but their originating extension version cannot be proven. Migration stopped.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Disable mykeehu/topas in maintenance mode before enabling this extension.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'Topic Password cannot be disabled while %d protected topic exists.',
		2 => 'Topic Password cannot be disabled while %d protected topics exist.',
	],
]);
