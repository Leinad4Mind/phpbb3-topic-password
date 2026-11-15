<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => 'Removeu as senhas de %d tópico(s) protegido(s) pela linha de comando do Topic Password.',
	'TOPICPASSWORD' => 'Senha do tópico',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'Deixe os dois campos vazios para manter a senha atual. A senha deve conter pelo menos 8 caracteres e não pode ultrapassar 72 bytes.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'A senha do tópico mudou durante a verificação. Tente novamente.',
	'TOPICPASSWORD_CONFIRM' => 'Confirmar senha',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Permitir senhas nos tópicos',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Permite que autores de novos tópicos e usuários que editam a primeira mensagem protejam o tópico com uma senha.',
	'TOPICPASSWORD_HASH_FAILED' => 'Não foi possível criar o hash da senha.',
	'TOPICPASSWORD_INVALID' => 'A senha do tópico está incorreta.',
	'TOPICPASSWORD_LOGIN' => 'Tópico protegido',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Digite a senha para abrir este tópico durante a sessão atual.',
	'TOPICPASSWORD_MISMATCH' => 'As senhas do tópico não coincidem.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'Este fórum não permite novas senhas nos tópicos.',
	'TOPICPASSWORD_PROTECTED' => 'Tópico protegido por senha',
	'TOPICPASSWORD_RATE_LIMITED' => 'Muitas tentativas falharam. Tente novamente em %d segundos.',
	'TOPICPASSWORD_REMOVE' => 'Remover a senha atual do tópico',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Escolha entre remover a senha ou definir uma nova, não as duas opções.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'Este tópico está protegido por senha. %1$sDigite a senha do tópico%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'A divisão ou mesclagem de um tópico protegido está desativada nesta versão para evitar uma falha no controle de acesso.',
	'TOPICPASSWORD_TOO_LONG' => 'A senha do tópico não pode ultrapassar 72 bytes.',
	'TOPICPASSWORD_TOO_SHORT' => 'A senha do tópico deve conter pelo menos 8 caracteres.',
]);
