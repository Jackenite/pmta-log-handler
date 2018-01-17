# pmta-log-handler
PowerMTA Log Handler to mysql

This is a very simple script design to handle Power MTA log files using pipe.

I know that the code is not either adapted now for Symfony or Laravel because it was made as a very quick script for the moment.
Our team is planning to update and issue further releases.

Currently, PowerMTA writes files to /var/log/pmta.

Example of a file config in Power MTA

```
<acct-file /var/log/pmta/acct.csv>
    records d
    max-size 256M
    delete-after 30d
    sync yes
</acct-file>

<acct-file /var/log/pmta/fbl.csv>
    records f
    map-header-to-field feedback-loop header_X-HmXmrOriginalRecipient rcpt # hotmail recipient
    record-fields f *, header_subject, header_BatchId, header_Message-Id, header_List-Unsubscribe, header_List-Id, header_X-Mw-Subscriber-Uid, header_X-Mailer-LID, header_X-Mailer-RecptId, header_x-ccms-hash
    sync yes
</acct-file>

<acct-file /var/log/pmta/bounces.csv>
    records b, rb
    record-fields b *, header_x-id, header_x-ccms-hash
    record-fields rb *, header_x-id, header_x-ccms-hash
    sync yes
</acct-file>

<acct-file /var/log/pmta/receipts.csv>
    records r, rs
    record-fields r *, header_x-ccms-hash
    record-fields rs *, header_x-ccms-hash
    sync yes
</acct-file>
```

Now, using the bounce handlers, it's easy by using pipe (|) instead.
Assuming you will checkout this little program under /opt/pmta

Steps:

1. Checkout the repository

```bash
[root@srv ~]# mkdir -p /opt/pmta
[root@srv ~]# cd /opt/pmta/
[root@srv pmta]# git clone git@github.com:amerom/pmta-log-handler.git
Cloning into 'pmta-log-handler'...
remote: Counting objects: 509, done.
remote: Compressing objects: 100% (276/276), done.
remote: Total 509 (delta 188), reused 506 (delta 188), pack-reused 0
Receiving objects: 100% (509/509), 373.04 KiB | 0 bytes/s, done.
Resolving deltas: 100% (188/188), done.
[root@srv pmta]# cd pmta-log-handler/
[root@srv pmta-log-handler]# ls
composer.json  composer.lock  config  Constants.php  Handler.php  loader.php  README.md  scripts
[root@srv pmta-log-handler]# 
```

2. Edit your parameters yml file with your database information. (under /config/ folder)

```yaml
parameters:
    database_host: localhost
    database_port: null
    database_name: pmta
    database_user: user
    database_password: password
```


```
<acct-file |/usr/bin/php /opt/pmta/pmta-log-handler/scripts/acct.php>
    records d
    max-size 256M
    delete-after 30d
    sync yes
</acct-file>

<acct-file |/usr/bin/php /opt/pmta/pmta-log-handler/scripts/fbl.php>
    records f
    map-header-to-field feedback-loop header_X-HmXmrOriginalRecipient rcpt # hotmail recipient
    record-fields f *, header_subject, header_BatchId, header_Message-Id, header_List-Unsubscribe, header_List-Id, header_X-Mw-Subscriber-Uid, header_X-Mailer-LID, header_X-Mailer-RecptId, header_x-ccms-hash
    sync yes
</acct-file>

<acct-file |/usr/bin/php /opt/pmta/pmta-log-handler/scripts/bounces.php>
    records b, rb
    record-fields b *, header_x-id, header_x-ccms-hash
    record-fields rb *, header_x-id, header_x-ccms-hash
    sync yes
</acct-file>

<acct-file |/usr/bin/php /opt/pmta/pmta-log-handler/scripts/receipts.php>
    records r, rs
    record-fields r *, header_x-ccms-hash
    record-fields rs *, header_x-ccms-hash
    sync yes
</acct-file>
```

After this is configured on the same server which has PowerMTA installed, everything should work as 
expected and the tables/fields will automatically be created, columns will be normalized using snake_case.

Attention!! That's a simple quick script made in one hour and it works. We are planning to update it and make it more advanced.
