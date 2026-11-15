<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => 'Removeu as palavras-passe de %d tópico(s) protegido(s) através da linha de comandos do Topic Password.',
	'TOPICPASSWORD' => 'Palavra-passe do tópico',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'Deixa ambos os campos vazios para manter a palavra-passe actual. A palavra-passe deve ter pelo menos 8 caracteres e no máximo 72 bytes.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'A palavra-passe do tópico mudou durante a verificação. Tenta novamente.',
	'TOPICPASSWORD_CONFIRM' => 'Confirmar palavra-passe',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Permitir palavras-passe nos tópicos',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Permite ao autor de um novo tópico e a quem editar a primeira mensagem proteger o tópico com uma palavra-passe.',
	'TOPICPASSWORD_HASH_FAILED' => 'Não foi possível criar o hash da palavra-passe.',
	'TOPICPASSWORD_INVALID' => 'A palavra-passe do tópico está incorrecta.',
	'TOPICPASSWORD_LOGIN' => 'Tópico protegido',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Introduz a palavra-passe para abrir este tópico durante a sessão actual.',
	'TOPICPASSWORD_MISMATCH' => 'As palavras-passe do tópico não coincidem.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'Este fórum não permite novas palavras-passe nos tópicos.',
	'TOPICPASSWORD_PROTECTED' => 'Tópico protegido por palavra-passe',
	'TOPICPASSWORD_RATE_LIMITED' => 'Demasiadas tentativas falhadas. Tenta novamente dentro de %d segundos.',
	'TOPICPASSWORD_REMOVE' => 'Remover a palavra-passe actual do tópico',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Escolhe remover a palavra-passe ou definir uma nova, não ambas as opções.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'Este tópico está protegido por palavra-passe. %1$sIntroduz a palavra-passe do tópico%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'A divisão ou fusão de um tópico protegido está desactivada nesta versão para evitar uma fuga no controlo de acesso.',
	'TOPICPASSWORD_TOO_LONG' => 'A palavra-passe do tópico não pode exceder 72 bytes.',
	'TOPICPASSWORD_TOO_SHORT' => 'A palavra-passe do tópico deve ter pelo menos 8 caracteres.',
]);
