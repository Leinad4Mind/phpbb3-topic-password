<?php

// A Mykee-féle eredeti magyar fordítás alapján, a 4.0-s verzióhoz kiegészítve.

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'Egy régi topikjelszó formátuma nem állapítható meg egyértelműen. Az átállás módosítás nélkül leállt.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'Egy régi topikjelszó hashelése sikertelen volt. Az átállás leállt.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Léteznek topikjelszavak, de a származási verziójuk nem igazolható. Az átállás leállt.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Az új bővítmény engedélyezése előtt karbantartási módban tiltsd le a mykeehu/topas bővítményt.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'A Topic Password nem tiltható le, amíg %d védett topik létezik.',
		2 => 'A Topic Password nem tiltható le, amíg %d védett topik létezik.',
	],
]);
