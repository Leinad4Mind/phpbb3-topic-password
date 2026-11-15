<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'Uma palavra-passe antiga de tópico tem um formato ambíguo. A migração parou sem a alterar.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'Não foi possível criar o hash de uma palavra-passe antiga. A migração parou.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Existem palavras-passe de tópicos, mas não foi possível provar a versão de origem. A migração parou.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Desative mykeehu/topas em modo de manutenção antes de ativar esta extensão.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'Não é possível desativar Topic Password enquanto existir %d tópico protegido.',
		2 => 'Não é possível desativar Topic Password enquanto existirem %d tópicos protegidos.',
	],
]);
