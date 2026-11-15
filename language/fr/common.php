<?php

// Terminologie française historique par Galixte, complétée pour la version 4.0.

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => 'Suppression des mots de passe de %d sujet(s) protégé(s) avec la commande Topic Password.',
	'TOPICPASSWORD' => 'Mot de passe du sujet',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'Laissez les deux champs vides pour conserver le mot de passe actuel. Le mot de passe doit contenir au moins 8 caractères et ne pas dépasser 72 octets.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'Le mot de passe du sujet a changé pendant sa vérification. Veuillez réessayer.',
	'TOPICPASSWORD_CONFIRM' => 'Confirmer le mot de passe',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Autoriser les mots de passe pour les sujets',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Permet aux auteurs de nouveaux sujets et aux personnes modifiant le premier message de protéger le sujet par un mot de passe.',
	'TOPICPASSWORD_HASH_FAILED' => 'Le hachage du mot de passe a échoué.',
	'TOPICPASSWORD_INVALID' => 'Le mot de passe du sujet est incorrect.',
	'TOPICPASSWORD_LOGIN' => 'Sujet protégé',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Saisissez le mot de passe pour ouvrir ce sujet pendant la session actuelle.',
	'TOPICPASSWORD_MISMATCH' => 'Les mots de passe du sujet ne correspondent pas.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'Ce forum n’autorise pas de nouveaux mots de passe pour les sujets.',
	'TOPICPASSWORD_PROTECTED' => 'Sujet protégé par mot de passe',
	'TOPICPASSWORD_RATE_LIMITED' => 'Trop de tentatives ont échoué. Réessayez dans %d secondes.',
	'TOPICPASSWORD_REMOVE' => 'Supprimer le mot de passe actuel du sujet',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Choisissez soit de supprimer le mot de passe, soit d’en définir un nouveau, mais pas les deux.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'Ce sujet est protégé par mot de passe. %1$sSaisissez le mot de passe du sujet%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'La division ou la fusion d’un sujet protégé par mot de passe est désactivée dans cette version afin d’éviter une faille de contrôle d’accès.',
	'TOPICPASSWORD_TOO_LONG' => 'Le mot de passe du sujet ne doit pas dépasser 72 octets.',
	'TOPICPASSWORD_TOO_SHORT' => 'Le mot de passe du sujet doit contenir au moins 8 caractères.',
]);
