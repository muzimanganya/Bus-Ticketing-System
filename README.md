# Transportation Web App

LAP LTD Transportation  Portal and API

### Cloning to your local directory
1. Fork this project ([Click here to learn how to do that](https://docs.gitlab.com/ee/user/project/repository/forking_workflow.html#creating-a-fork))
2. Click the blue top-right corner "clone" button and copy https URL
3. run `git clone https://<YOUR URL HERE>.git`
4. Create new branch with format `feature/feature-name`. If you are adding QR Code API it can be something like `git branch feature/qr-code-api`
5. Switch to new branch with command `git checkout feature/qr-code-api`
6. Do all your changes. With each change make sure to commit it separately via `git commit -m "commit message"`
7. Push changes to your fork with `git push`
8. Do merge request to this Project ([Click here to learn how to do it](https://docs.gitlab.com/ee/gitlab-basics/add-merge-request.html))

### Few things to note:
1. Create db.php in config if you have not created yet. Here is a sample code to go in that file
```php
<?php
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=volcano_shared',
    'username' => 'root',
    'password' => 'jesus',
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    // Duration of schema cache.
    'schemaCacheDuration' => 3600,//
    // Name of the cache component used to store schema information
    'schemaCache' => 'cache',
];
```

2. make sure folders `runtime/` and `web/assets` are fully writable (chmod 777 or equivalent)
3. If you are writing tests then make sure that you create `config/tests_db.php`. The above sample for `db.php` applies here too

To Lean more about Git check out [the Git Book](https://git-scm.com/book/en/v2)
