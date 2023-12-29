Amp SQL profiler bundle 
=======================

If you use [Amp][] with [Symfony][] then you are probably based, but that means [Doctrine][] will be useless (except perhaps its query builder), so you can't use the handy query profiler from [DoctrineBundle][]. This is the missing SQL profiler for Amp.

Features
--------

Profiles Amp SQL connections in Symfony applications and displays the results in the [Symfony Profiler][] toolbar.

* Shows query summary in toolbar view.
* Shows full query details in profiler view.
* Supports transactions.
* Supports backtraces.

Installation
------------

Add the bundle to your project with [Composer][].

```sh
composer require --dev async/amp-sql-profiler-bundle
```

If [Symfony Flex][] is installed, it should update `bundles.php` automatically with a line similar to the following.

```php
ScriptFUSION\AmpSqlProfilerBundle\AmpSqlProfilerBundle::class => ['dev' => true, 'test' => true],
```

We recommend removing the `'test'` key and only running the bundle in `dev`. If Flex is not available, the line can be added manually.

To enable profiling, all instances of `Amp\Sql\Pool` must be replaced with `ProfiledPool` at dependency injection time. To do this, add the following line to `services_dev.yaml`.

```yaml
  # Enable Amp SQL profiling.
  Amp\Sql\Pool:
    factory: '@ScriptFUSION\AmpSqlProfilerBundle\ProfiledPoolFactory'
```

Unfortunately, this will create a circular reference because `ProfiledPoolFactory` creates an instance of `ProfiledPool` which requires an instance of `Amp\Sql\Pool`. This can be resolved by specifying the specific implementation of the Pool that we want to profile. For example, the following configuration may suffice to profile Postgres.

```yaml
  ScriptFUSION\AmpSqlProfilerBundle\ProfiledPool:
    arguments:
      - '@Amp\Postgres\PostgresConnectionPool'

  Amp\Postgres\PostgresConnectionPool: ~
```


  [Amp]: https://amphp.org
  [Symfony]: https://symfony.com
  [Symfony Flex]: https://symfony.com/doc/current/setup/flex
  [Symfony Profiler]: https://symfony.com/doc/current/profiler
  [Doctrine]: https://www.doctrine-project.org
  [DoctrineBundle]: https://github.com/doctrine/DoctrineBundle
  [Composer]: https://getcomposer.org
