<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'Uma senha antiga de tópico tem um formato ambíguo. A migração foi interrompida sem alterá-la.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'Não foi possível criar o hash de uma senha antiga. A migração foi interrompida.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Existem senhas de tópicos, mas não foi possível comprovar a versão de origem. A migração foi interrompida.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Desative mykeehu/topas no modo de manutenção antes de ativar esta extensão.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'O Topic Password não pode ser desativado enquanto houver %d tópico protegido.',
		2 => 'O Topic Password não pode ser desativado enquanto houver %d tópicos protegidos.',
	],
]);
