# Topic Password

Topic Password 4.0.0 protects individual phpBB topics with an additional password challenge whose authorization lasts only for the current phpBB session.

## Requirements

- phpBB `>=3.3.19,<3.4.0@dev`;
- PHP 8.1 or newer;
- one of phpBB's supported database engines.

The topic password is an extra authorization layer. It never grants `f_read`, posting, moderation, attachment, approval, or visibility permissions that the user does not already have through phpBB.

## Clean installation

1. Extract the package so that `composer.json` is located at `ext/leinad4mind/topicpassword/composer.json`.
2. In the ACP, open **Customise → Manage extensions** and enable **[Leinad4Mind] Topic Password**.
3. Edit each eligible forum in **ACP → Forums** and enable **Allow topic passwords**.
4. Create a test topic, set and confirm a password, then verify access in a separate non-moderator session.

The equivalent CLI activation is:

```console
php bin/phpbbcli.php extension:enable leinad4mind/topicpassword
php bin/phpbbcli.php cache:purge
```

## Upgrade from Topic Password 1.x, 2.1.0, or 3.0.0

The migration reuses the legacy `forum_allow_topic_password`, `topic_password`, and `topics_access` schema. It converts proven 1.x/2.1.0 plaintext values to phpBB hashes, preserves valid 3.0.0 hashes, and discards every legacy session grant.

Perform the upgrade with the forum in maintenance mode:

1. Make a verified, restorable backup of the database and files.
2. Record the number of protected topics and test a small sample with their known passwords.
3. Keep the old extension files available, but disable `mykeehu/topas` if it is installed. The two extensions cannot be active together.
4. Copy this package to `ext/leinad4mind/topicpassword/` and enable it.
5. Purge phpBB's cache, especially when activation was performed through the CLI.
6. Confirm that the migration reports version `4.0.0`:

   ```console
   php bin/phpbbcli.php config:get topic_password_version
   ```

7. Audit the migrated topics without exposing their hashes:

   ```console
   php bin/phpbbcli.php topicpassword:audit --json
   ```

8. Confirm that the protected-topic count matches the pre-upgrade count and retest the sampled passwords in a normal user session.
9. Leave maintenance mode only after those checks pass. Remove the old files in a later maintenance operation.

If the source schema or password format cannot be proven, activation stops instead of guessing.

## Administration and user behaviour

- A forum must have **Allow topic passwords** enabled before a new topic password can be created.
- The password fields appear when creating a topic or editing its first post.
- Leaving both password fields empty keeps the current password.
- Removing a password requires the explicit removal option.
- New passwords require at least 8 Unicode characters and at most 72 UTF-8 bytes.
- Changing or removing a password invalidates existing session grants.
- Administrators and moderators of the topic's forum bypass the additional password by design. Topic authors do not bypass it merely by being the author.
- A successful challenge authorizes only the current phpBB session and password revision. Logging in from another session requires the password again.
- Five failed account/session attempts inside fifteen minutes trigger a fifteen-minute lockout. IP throttling uses a separate higher limit.

Splitting or merging protected topics is deliberately blocked in 4.0.0. Moving and forking preserve the protection.

## Protected surfaces

Without a valid grant, the extension blocks or filters normal and print views, poll display and voting, posting and quoting flows, PM quote, attachments, feeds, and search results/counts. The topic title and ordinary list metadata may remain visible.

Notification payloads retain at most the public topic title and link. Message bodies and individual protected reply subjects are removed before notification storage or delivery.

## Rollback

Before the upgraded forum leaves maintenance mode, rollback means restoring the complete verified database/files backup and re-enabling the old extension. Do not try to convert migrated hashes back to plaintext, and do not use purge as a substitute for restoring the backup: purge intentionally removes the Topic Password columns and tables.

After users have accessed or changed topics under 4.0.0, restoring an older backup can lose newer forum data. Plan that rollback as a normal full-site recovery and communicate the recovery point before proceeding.

## Safe disable and removal

The extension refuses to disable while any protected topic remains, because disabling its listeners would expose the content through ordinary phpBB routes.

1. Audit and, if required, save the non-secret topic inventory:

   ```console
   php bin/phpbbcli.php topicpassword:audit --json
   ```

2. Explicitly remove every topic password:

   ```console
   php bin/phpbbcli.php topicpassword:remove-all --confirm
   ```

3. Disable and optionally purge the extension:

   ```console
   php bin/phpbbcli.php extension:disable leinad4mind/topicpassword
   php bin/phpbbcli.php extension:purge leinad4mind/topicpassword
   php bin/phpbbcli.php cache:purge
   ```

`topicpassword:remove-all` increments the affected password revisions, deletes grants and attempt counters, and records an administrative log entry. Purge removes the extension's schema and data; it is not reversible without a database backup.

## Languages and validation

English, Portuguese (Portugal, Brazil, and pre-1990 spelling), French, Spanish, and Hungarian are included. The release passed PHPUnit on PHP 8.1, 8.2, 8.4, and 8.5; real upgrade/search/HTTP scenarios; PHPCS; Composer validation; and EPV with no findings. Detailed evidence is recorded in `tests/integration/VALIDATION.md`.

## License and credits

GPL-2.0-only. Based on Topic Password 3.0.0 by Mykee, Topic Password MOD 2.1.0 by Martin (Kamahl19), and the earlier work by Barry Carlyon and Pthelovod. French legacy terminology by Galixte.
