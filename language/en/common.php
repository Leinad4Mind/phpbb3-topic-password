<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => 'Removed passwords from %d protected topic(s) using the Topic Password CLI.',
	'TOPICPASSWORD' => 'Topic password',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'Leave both fields empty to keep the current password. Passwords must contain at least 8 characters and no more than 72 bytes.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'The topic password changed while it was being checked. Please try again.',
	'TOPICPASSWORD_CONFIRM' => 'Confirm password',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Allow topic passwords',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Allows authors of new topics and editors of the first post to protect the topic with a password.',
	'TOPICPASSWORD_HASH_FAILED' => 'The password could not be hashed.',
	'TOPICPASSWORD_INVALID' => 'The topic password is incorrect.',
	'TOPICPASSWORD_LOGIN' => 'Protected topic',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Enter the password to open this topic for the current session.',
	'TOPICPASSWORD_MISMATCH' => 'The topic passwords do not match.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'This forum does not allow new topic passwords.',
	'TOPICPASSWORD_PROTECTED' => 'Password protected topic',
	'TOPICPASSWORD_RATE_LIMITED' => 'Too many failed attempts. Try again in %d seconds.',
	'TOPICPASSWORD_REMOVE' => 'Remove the current topic password',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Choose either to remove the password or to set a new one, not both.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'This topic is password protected. %1$sEnter the topic password%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'Splitting or merging a password-protected topic is disabled in this version to prevent an access-control leak.',
	'TOPICPASSWORD_TOO_LONG' => 'The topic password must not exceed 72 bytes.',
	'TOPICPASSWORD_TOO_SHORT' => 'The topic password must contain at least 8 characters.',
]);
