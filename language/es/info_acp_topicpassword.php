<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'Una contraseña antigua de tema tiene un formato ambiguo. La migración se detuvo sin modificarla.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'No se pudo generar el hash de una contraseña antigua. La migración se detuvo.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Existen contraseñas de temas, pero no se puede demostrar su versión de origen. La migración se detuvo.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Desactiva mykeehu/topas en modo de mantenimiento antes de activar esta extensión.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'Topic Password no se puede desactivar mientras exista %d tema protegido.',
		2 => 'Topic Password no se puede desactivar mientras existan %d temas protegidos.',
	],
]);
