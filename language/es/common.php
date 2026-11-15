<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => 'Se eliminaron las contraseñas de %d tema(s) protegido(s) mediante la consola de Topic Password.',
	'TOPICPASSWORD' => 'Contraseña del tema',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'Deja ambos campos vacíos para conservar la contraseña actual. La contraseña debe tener al menos 8 caracteres y no superar los 72 bytes.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'La contraseña del tema cambió mientras se comprobaba. Inténtalo de nuevo.',
	'TOPICPASSWORD_CONFIRM' => 'Confirmar contraseña',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Permitir contraseñas en los temas',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Permite a los autores de temas nuevos y a quienes editen el primer mensaje proteger el tema con una contraseña.',
	'TOPICPASSWORD_HASH_FAILED' => 'No se pudo generar el hash de la contraseña.',
	'TOPICPASSWORD_INVALID' => 'La contraseña del tema es incorrecta.',
	'TOPICPASSWORD_LOGIN' => 'Tema protegido',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Introduce la contraseña para abrir este tema durante la sesión actual.',
	'TOPICPASSWORD_MISMATCH' => 'Las contraseñas del tema no coinciden.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'Este foro no permite nuevas contraseñas en los temas.',
	'TOPICPASSWORD_PROTECTED' => 'Tema protegido por contraseña',
	'TOPICPASSWORD_RATE_LIMITED' => 'Demasiados intentos fallidos. Inténtalo de nuevo dentro de %d segundos.',
	'TOPICPASSWORD_REMOVE' => 'Eliminar la contraseña actual del tema',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Elige entre eliminar la contraseña o definir una nueva, no ambas opciones.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'Este tema está protegido por contraseña. %1$sIntroduce la contraseña del tema%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'La división o fusión de un tema protegido está desactivada en esta versión para evitar una fuga en el control de acceso.',
	'TOPICPASSWORD_TOO_LONG' => 'La contraseña del tema no puede superar los 72 bytes.',
	'TOPICPASSWORD_TOO_SHORT' => 'La contraseña del tema debe tener al menos 8 caracteres.',
]);
