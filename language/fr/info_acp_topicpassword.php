<?php

// Terminologie française historique par Galixte, complétée pour la version 4.0.

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'TOPICPASSWORD_AMBIGUOUS_LEGACY_PASSWORD' => 'Un ancien mot de passe de sujet présente un format ambigu. La migration a été interrompue sans le modifier.',
	'TOPICPASSWORD_LEGACY_HASH_FAILED' => 'Le hachage d’un ancien mot de passe de sujet a échoué. La migration a été interrompue.',
	'TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE' => 'Des mots de passe de sujets existent, mais leur version d’origine ne peut pas être établie. La migration a été interrompue.',
	'TOPICPASSWORD_OLD_EXTENSION_ACTIVE' => 'Désactivez mykeehu/topas en mode maintenance avant d’activer cette extension.',
	'TOPICPASSWORD_DISABLE_BLOCKED' => [
		1 => 'Topic Password ne peut pas être désactivée tant que %d sujet protégé existe.',
		2 => 'Topic Password ne peut pas être désactivée tant que %d sujets protégés existent.',
	],
]);
