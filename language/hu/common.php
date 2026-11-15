<?php

// A Mykee-féle eredeti magyar fordítás alapján, a 4.0-s verzióhoz kiegészítve.

if (!defined('IN_PHPBB'))
{
	exit;
}

$lang = array_merge($lang, [
	'LOG_TOPIC_PASSWORD_REMOVE_ALL' => '%d jelszóval védett topik jelszava eltávolítva a Topic Password parancssori eszközével.',
	'TOPICPASSWORD' => 'Topik jelszava',
	'TOPICPASSWORD_CHANGE_EXPLAIN' => 'A jelenlegi jelszó megtartásához hagyd mindkét mezőt üresen. A jelszónak legalább 8 karaktert kell tartalmaznia, és legfeljebb 72 bájt hosszú lehet.',
	'TOPICPASSWORD_CHANGED_RETRY' => 'A topik jelszava ellenőrzés közben megváltozott. Próbáld újra.',
	'TOPICPASSWORD_CONFIRM' => 'Jelszó megerősítése',
	'TOPICPASSWORD_FORUM_ALLOW' => 'Topikjelszó engedélyezése',
	'TOPICPASSWORD_FORUM_ALLOW_EXPLAIN' => 'Engedélyezi, hogy az új topikok szerzői és az első hozzászólás szerkesztői jelszóval védjék a topikot.',
	'TOPICPASSWORD_HASH_FAILED' => 'Nem sikerült létrehozni a jelszó hashét.',
	'TOPICPASSWORD_INVALID' => 'A topik jelszava hibás.',
	'TOPICPASSWORD_LOGIN' => 'Jelszóval védett topik',
	'TOPICPASSWORD_LOGIN_EXPLAIN' => 'Add meg a jelszót a topik megnyitásához az aktuális munkamenetben.',
	'TOPICPASSWORD_MISMATCH' => 'A topikhoz megadott jelszavak nem egyeznek.',
	'TOPICPASSWORD_NOT_ALLOWED_IN_FORUM' => 'Ebben a fórumban nem engedélyezett új topikjelszó beállítása.',
	'TOPICPASSWORD_PROTECTED' => 'Jelszóval védett topik',
	'TOPICPASSWORD_RATE_LIMITED' => 'Túl sok sikertelen próbálkozás. Próbáld újra %d másodperc múlva.',
	'TOPICPASSWORD_REMOVE' => 'Jelenlegi topikjelszó eltávolítása',
	'TOPICPASSWORD_REMOVE_AND_SET' => 'Vagy távolítsd el a jelszót, vagy adj meg újat; a kettő egyszerre nem lehetséges.',
	'TOPICPASSWORD_REQUIRED_LINK' => 'Ez a topik jelszóval védett. %1$sAdd meg a topik jelszavát%2$s.',
	'TOPICPASSWORD_SPLIT_MERGE_BLOCKED' => 'A jelszóval védett topikok szétválasztása és egyesítése ebben a verzióban le van tiltva a hozzáférés-védelmi rés elkerülése érdekében.',
	'TOPICPASSWORD_TOO_LONG' => 'A topik jelszava legfeljebb 72 bájt hosszú lehet.',
	'TOPICPASSWORD_TOO_SHORT' => 'A topik jelszavának legalább 8 karaktert kell tartalmaznia.',
]);
