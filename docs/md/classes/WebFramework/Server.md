***

# Server





* Full name: `\WebFramework\Server`



## Properties


### proj_dir



```php
private string $proj_dir
```






***

### rh



```php
private \WebFramework\RoutingHandler $rh
```






***

### discovery_ran



```php
private bool $discovery_ran
```






***

## Methods


### __construct



```php
public __construct(string $proj_dir = NULL): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$proj_dir` | **string** |  |





***

### discoverPages

Automatically discover and register new Page Controllers

```php
public discoverPages(): void
```












***

### getRoutingHandler

Get the Routing Handler attached to the Server. Will return NULL if no RoutingHandler has been attached yet.

```php
public getRoutingHandler(): \WebFramework\RoutingHandler
```












***

### setRoutingHandler

Attach a new RoutingHandler to the Server. Will run discoverPages if it hasnt run before.

```php
public setRoutingHandler(\WebFramework\RoutingHandler $rh): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$rh` | **\WebFramework\RoutingHandler** |  |





***

### serve

Serve the Request

```php
public serve(): void
```












***


***
> Automatically generated on 2025-03-25
