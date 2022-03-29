# ChainCommandBundle

ChainCommandBundle is a Symfony bundle that implements command chaining functionality. Other Symfony bundles in the
application may register their console commands to be members of a command chain. When a user runs the main command in a
chain, all other commands registered in this chain should be executed as well. Commands registered as chain members can
no longer be executed on their own.

Installation
------------

###Step 1:

Via composer

```bash
 composer require "timofiy/chain-command-bundle": "*"
```

###Step 2:

Add bundle to "./config/bundles.php"

```php
<?php

return [
    ...
    Timofiy\ChainCommandBundle\ChainCommandBundle::class => ['all' => true],
];

```

###Step 3 (OPTIONAL):

If you want to use Dynamically command chain with ChainCommandManager service:
- Create alias in "./config/services.yaml"

```php
Timofiy\ChainCommandBundle\Manager\ChainCommandManager: "@chain_command.manager"
```

Usage
======

1. Dynamic: "ChainCommandManager::putToChain"

```php
   /**
     * Some constructor
     *
     * @param ChainCommandManager $chainCommandManager
     */
    public function __construct(private ChainCommandManager $chainCommandManager)
    {
        $this->chainCommandManager->putCommandToChain('root:command', 'member:command');
        parent::__construct();
    }
```


2. Static: specifying chains in config files (in "./config")

```yaml
timofiy_chain_command:
  detailed_logging: # is detailed logging enabled
    enabled: true
  chain_commands:
    foo:hello:  # root command name
      - bar:hi:  # member command name
          arguments: { }  # arguments: { "-arg": "value", "--argWithoutVal": ~, "key": 'example value' }
          sort_index: 16  # sort_index: int - for sorting member commands
      - bar:hi:
          arguments: { }
          sort_index: 12
```

Demonstration
=============

##You can install demonstration symfony bundles:

- https://github.com/timofiyprisyazhnyuk/BarBundle
- https://github.com/timofiyprisyazhnyuk/FooBundle