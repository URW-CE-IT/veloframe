***

# TemplateComponent





* Full name: `\WebFramework\TemplateComponent`



## Properties


### html



```php
private string $html
```






***

### name



```php
private string $name
```






***

### vars



```php
private array&lt;string,mixed&gt; $vars
```






***

### var_default



```php
private string $var_default
```






***

## Methods


### __construct



```php
public __construct(string $component_name, mixed $args): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$component_name` | **string** |  |
| `$args` | **mixed** |  |





***

### open

Open a new Component by name. Will return false if the component could not be opened and true if the component has been loaded.

```php
public open(string $component_name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$component_name` | **string** |  |





***

### setVars

Public Alias of setVarArray with variadic parameter

```php
public setVars(string[] $args): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$args` | **string[]** | Array of values to assign to the variables in order |





***

### setVariable

Set a variable value.

```php
public setVariable(string $name, mixed $value): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### setNamedVarArray

Merges the given key-value paired array with the internal var array.

```php
public setNamedVarArray(array&lt;string,mixed&gt; $args): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$args` | **array<string,mixed>** |  |





***

### setVarArray

Parses variable names based on occurance in the components and sets values based on argument array index positions.

```php
private setVarArray(array&lt;string,mixed&gt; $args): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$args` | **array<string,mixed>** | Array of values to assign to the variables in order |





***

### output

Render the Component to HTML

```php
public output(string $var_default = &quot;&quot;): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$var_default` | **string** |  |





***


***
> Automatically generated on 2025-03-25
