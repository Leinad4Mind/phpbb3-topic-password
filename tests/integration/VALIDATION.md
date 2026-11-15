# Integration validation

Validated on 2026-11-14 with phpBB 3.3.19, PHP 8.1.34, and SQLite.

## Legacy upgrades

Each scenario started from the corresponding legacy schema and then enabled `leinad4mind/topicpassword` through phpBB's extension manager.

| Source | Stored value | Result | Revision | Legacy grants |
|---|---|---|---:|---|
| 1.x | plaintext | hashed and password verified | 1 | discarded |
| 2.1.0 | plaintext | hashed and password verified | 1 | discarded |
| 3.0.0 | phpBB password hash | retained and password verified | 0 | discarded |

All three scenarios reported extension version `4.0.0` and created the attempts schema.

The complete three-by-three upgrade matrix was executed through phpBB 3.3.19's real extension manager on MySQL 8.0.46, MariaDB 10.11.19, and PostgreSQL 16.15. Every source/engine combination passed the following checks:

- the legacy source was classified as plaintext for 1.x/2.1.0 and hash for 3.0.0;
- the protected-topic count remained unchanged;
- plaintext was replaced by a phpBB-recognized hash with revision 1;
- the 3.0.0 hash was preserved byte-for-byte with revision 0;
- the original password remained verifiable;
- all legacy grants were discarded;
- the attempts table, grant revision/timestamp columns, session index, and HMAC secret were created;
- disable followed by purge removed both extension tables, all added columns, indexes, and runtime configuration.

After a real activation and cache rebuild, both operational commands were also exercised: `topicpassword:audit --json` listed the protected fixture without its hash, and `topicpassword:remove-all --confirm` removed the protection so disable/purge could proceed.

## Migration interruption and ambiguity

Two additional activation scenarios were executed through phpBB 3.3.19's real extension manager on a disposable SQLite installation:

- a non-empty `topic_password` column without the other 1.x schema markers was classified as unknown and activation stopped with `TOPICPASSWORD_UNKNOWN_LEGACY_SOURCE`, rather than guessing that the value was plaintext;
- a deliberately partial 1.x-to-4.0 schema, with the topic revision column already present but the legacy grants table still missing the 4.0 columns, resumed successfully. Activation hashed and verified the plaintext password, set revision 1, discarded the legacy grant, added the missing grant columns and attempts table, and recorded version 4.0.0.

Unit tests also force both password-change races: a stale revision and a failed conditional rehash each prevent creation of a grant.

## Search visibility

The Native backend was exercised through its real phpBB service/event pipeline. A control corpus contained the same indexed term in two topics. After one topic was protected and the search revision incremented:

- keyword results changed from 2 to 1;
- author results changed from 2 to 1;
- no post ID belonging to the protected topic remained;
- the reported total equalled the number of returned unprotected IDs;
- pagination reproduced the filtered result order;
- the cache namespace moved from revision 1 to revision 2.

The real fulltext backends were then exercised against clean phpBB 3.3.19 installations on MySQL 8.4.10, MariaDB 10.11.19, and PostgreSQL 16.16. In every engine, keyword and author searches changed from 2 results to 1 after protecting one of the two fixture topics; the protected post ID was absent and the returned count matched the remaining IDs. The fixtures and search-result cache rows were removed after each run.

The real phpBB `fulltext_sphinx` backend was connected through its legacy API to a Sphinx 3.9.1 daemon with two RT indexes exposing phpBB's normal attributes. Keyword and author searches both changed from 2 results to 1 after topic 1 was protected, and post 101 carrying `topic_id = 1` was absent. This verifies that the listener's excluding `SetFilter('topic_id', ..., true)` affects Sphinx's own result IDs and `total_found`, rather than only applying a later rowset defence.

Unit tests additionally verify the SQL injected into Native, MySQL, and PostgreSQL keyword/author events, Sphinx's excluding `topic_id` filter, pre-made searches, DB-backed unread topics, final rowset defence, and cache revision rotation after a password change.

## Notifications

The phpBB 3.3.19 topic/post/forum/bookmark/quote notification types were audited. Their core templates expose titles, authors, and links but no post body, and recipients pass the native `f_read` check. The extension additionally sanitizes both notification-manager insertion paths: protected `post_text` and `message` are blanked and `post_subject` is replaced by the public topic title. Contract tests cover protected and public payloads.

The Native, MySQL/MariaDB, PostgreSQL, and Sphinx search paths have therefore all been executed against their real engines.

## Runtime and language matrix

The complete unit suite passes on PHP 8.1.34, 8.2.35, 8.4.27, and 8.5.12. The current suite contains 28 tests and 296 assertions. English, Portuguese (Portugal, Brazil, and pre-1990 spelling), French, Spanish, and Hungarian expose the same language files, keys, plural forms, and format placeholders. PHP 8.5 reports no deprecations from the extension tests.

Controller contracts verify that an ordinary invalid password keeps the challenge response at HTTP 200, while an active throttling block returns HTTP 429 and exposes the retry wait to the template.

## HTTP access-control scenario

A real phpBB 3.3.19 server with a normal registered user, protected topic, and attachment passed the following browser-level requests:

- `viewtopic.php` redirected to the password challenge without exposing the body;
- print mode redirected to the challenge without exposing the body;
- a challenge POST without a valid form token was rejected;
- an incorrect password was rejected and the correct password granted access;
- reply/quote posting UI was blocked before the grant and available afterwards;
- PM quote was stopped before phpBB loaded the source post, exposed only the safe challenge link, and became available after the grant;
- the poll title and options stayed hidden, and a direct vote POST before the grant left both the poll total and stored votes unchanged;
- an attachment was blocked before the grant and downloadable afterwards;
- a second login session for the same user did not inherit the grant;
- the protected body was absent from the feed;
- five invalid passwords returned the ordinary HTTP 200 challenge, while the sixth returned HTTP 429 with the localized lockout message and persisted account, session, and IP attempt rows.

The PM quote scenario also caught a phpBB 3.3.19 integration defect: `core.ucp_pm_compose_quotepost_query_after` does not expose `topic_id` because the core quote query does not select it. The barrier now runs at `core.ucp_pm_compose_compose_pm_basic_info_query_before` and resolves the topic from the submitted post ID before the post body can be queried.

The HTTP fixture database and generated attachment were restored/removed after execution.

## Release and package gates

The release-candidate tree was packaged with the single root `leinad4mind/topicpassword/`; all 63 entries used that prefix, `composer.json` was present at the expected location, and no PHPUnit result cache was included. The exact staged tree passed EPV with zero fatal errors, errors, warnings, or notices.

Composer 2 validates `composer.json`. Its only advisory is the normal recommendation to omit an explicit version when publishing on Packagist; phpBB extensions retain the version field as release metadata. The production PHP code passes the pinned phpBB coding standard (`senky/phpbb-coding-standard` commit `56047eb5`) under PHPCS 3.13.6. PHPUnit doubles under `tests/` are excluded from PHPCS because several intentionally share one file or reproduce upstream method names, but they remain covered by PHPUnit and EPV.

The manifest declares the stable version `4.0.0` and retrieves the phpBB version check over HTTPS directly from `raw.githubusercontent.com/Leinad4Mind/phpbb3-topic-password/main/release/version_check.json`. The matching JSON publication artifact uses the phpBB `3.3` branch and announces extension version 4.0.0, so no separate web-server upload is required.

The shared Docker laboratory then mounted the extension alongside the repository's other extensions on phpBB 3.3.19 with PHP 8.4 and SQLite, MySQL 8, and PostgreSQL 16. On all three boards, clean enable reported extension version `4.0.0`, `topicpassword:audit --json` ran successfully, and the disable → purge → enable lifecycle completed with a cache rebuild.
