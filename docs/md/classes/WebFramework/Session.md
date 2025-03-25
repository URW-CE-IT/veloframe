***

# Session





* Full name: `\WebFramework\Session`




## Methods


### __construct



```php
public __construct(bool $autostart = TRUE): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$autostart` | **bool** |  |





***

### set

Sets a session variable to a specific value. Will return FALSE if failed.

```php
public static set(string $key, mixed $value): bool
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **string** |  |
| `$value` | **mixed** |  |





***

### get

Get a session variable. Will return $def_value if session variable does not exist.

```php
public static get(string $key, mixed $def_value = NULL): mixed
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$key` | **string** |  |
| `$def_value` | **mixed** |  |





***


***
> Automatically generated on 2025-03-25
