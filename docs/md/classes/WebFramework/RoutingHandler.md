***

# RoutingHandler





* Full name: `\WebFramework\RoutingHandler`



## Properties


### handlers



```php
private \WebFramework\RequestHandler[] $handlers
```






***

## Methods


### register

Register a new RequestHandler with a specific URI

```php
public register(string $uri, \WebFramework\RequestHandler $handler): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **string** |  |
| `$handler` | **\WebFramework\RequestHandler** |  |





***

### handle

Automatically select the correct previously registered RequestHandler to process a request for a given URI and return the rendered HTML string

```php
public handle(string $uri): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$uri` | **string** |  |





***


***
> Automatically generated on 2025-03-25
