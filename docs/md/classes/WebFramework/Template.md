***

# Template





* Full name: `\WebFramework\Template`



## Properties


### html



```php
private string $html
```






***

### vars



```php
private array&lt;string,mixed&gt; $vars
```






***

## Methods


### __construct



```php
public __construct(string $template_name = null): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$template_name` | **string** |  |





***

### open

Open a new Template. Will replace the previously opened Template. Returns false if load failed and true if successful.

```php
public open(string $template_name): bool
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$template_name` | **string** |  |





***

### includeTemplate

Include a new sub-template into a subtemplate variable

```php
public includeTemplate(string $name, mixed $template): void
```

TODO: Migrate Template inclusion logic to output()






**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$template` | **mixed** | (Accepts either a string (name of a template) or a Template object) |





***

### setVariable

setVariable

```php
public setVariable(string $name, mixed $value): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$value` | **mixed** |  |





***

### setComponent

setComponent

```php
public setComponent(string $name, \WebFramework\TemplateComponent $component): void
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$name` | **string** |  |
| `$component` | **\WebFramework\TemplateComponent** |  |





***

### output

Output / "Render" the template to HTML.

```php
public output(string $var_default = &quot;&quot;): mixed
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$var_default` | **string** | Which value to set unassigned variables to. When set to NULL, unassigned variables will be forwarded (e.g. when including other templates) |





***

### processInlineComponents

Internal Function to process inline components

```php
private processInlineComponents(string $html): string
```








**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$html` | **string** |  |





***


***
> Automatically generated on 2025-03-25
