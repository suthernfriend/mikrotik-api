# suthernfriend/mikrotik-api

A Comfortable Wrapper Around the RouterOS Library for accessing MikroTik routers via their API

# Getting started

## Install the package with composer

    composer require suthernfriend/mikrotik-api

## Create the ConnectionManager

```php
<?php
use MikroTikApi\ConnectionManager;
use Psr\Log\LoggerInterface;

/** @var LoggerInterface $logger */
$logger = getPsrLoggerInterface();

/** @var array $config */
$config = [
    "router1" => [
        "host" => "10.1.1.1",
        "user" => "admin",
        "password" => "passwordForRouter1",
    ],
    "router2" => [
        "host" => "10.1.2.1",
        "user" => "admin",
        "password" => "passwordForRouter2",
    ]
];

$connectionManager = new ConnectionManager($logger, $config);
```

```yaml
/* Or via Symfony Service */

services:
  MikroTikApi\ConnectionManager:
    arguments:
      $config:
        router1:
          host: 10.1.1.1
          user: admin
          password: passwordForRouter1
        router2:
          host: 10.1.2.1
          user: admin
          password: passwordForRouter2

```

## Example use:    

```php
<?php

namespace App;

use MikroTikApi\ConnectionManager;
use MikroTikApi\DataObjectArray;
use MikroTikApi\DataObject;

/** @var ConnectionManager $connectionManager */
$connectionManager = new ConnectionManager($logger, $config);

// Read something
/** @var DataObjectArray $obj */
$obj = $connectionManager->createRequest("router1")->ip->address->print();

echo "$obj\n";
/**
 * Will print p.e. 
 * [
 *   { id: *27, address: 10.1.1.1/24, network: 10.1.1.0, interface: eth1, actualInterface: eth1, dynamic: false, invalid: false, disabled: false },
 *   { id: *2A, address: 192.168.1.1/24, network: 192.168.1.0, interface: trunk, actualInterface: trunk, dynamic: false, invalid: false, disabled: false }
 * ]
 */

// Add an object
$vlanInterface = DataObject::create([
	"name" => "myVlanInterface",
	"vlanId" => 50,
	"interface" => "trunk"
]);

$connectionManager->createRequest("router1")->interface->vlan->add($vlanInterface);

// Change an object
$query = DataObject::create([ "address" => "192.168.1.100" ]);

$lease = $connectionManager->createRequest("router1")->ip->dhcpServer->lease->print($query);
$lease->address = "192.168.1.101";

$connectionManager->createRequest("router1")->ip->dhcpServer->lease->set($lease);

// Use some other action
// without argument createRequest() will use the first ! router given by $config
$connectionManager->createRequest()->ip->dhcpServer->lease->makeStatic($lease);


```

## General

You can use any MikroTik command by converting the command in the terminal interface to camelCase

**All commands (and even new commands that may appear in a future version of RouterOS) are supported!**

### Examples:

- `/system reboot` to `$connectionManager->createRequest()->system->reboot();`
- `/caps-man provisioning print` to `$connectionManager->createRequest()->capsMan->provisioning->print();`
- `/routing ospf-v3 virtual-link remove` to `$connectionManager->createRequest()->routing->ospfV3->virtualLink->remove();`
### The same is valid for properties

```php
<?php
$prov = $connectionManager->createRequest()->capsMan->provisioning->print(\MikroTikApi\DataObject::create(["comment" => "first-config"]))->getOne();
echo $prov->radioMac . "\n";
echo $prov->commonNameRegexp . "\n";
```

### Values are not converted

```php
<?php
$prov = $connectionManager->createRequest()->capsMan->provisioning->print(\MikroTikApi\DataObject::create(["comment" => "first-config"]))->getOne();
$prov->action = "create-dynamic-enabled";
```

### Types

IP-Addresse, Mac addresses, Durations, Numbers and MikroTik Ids are converted to special instances of classes which have additional features. See the Types folder.

## Bugs

I developed this for a project at work. So there is not much documentation. If there is a need 
feel free to ask me here or at my email and i will try my best to respond.

I'm expecting a lot of bugs in the code, since i haven't had the time to test everything.
Feel free to report them or post pull-requests.

## License

Apache License 2.0
