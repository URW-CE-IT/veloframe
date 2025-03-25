***

# Input





* Full name: `\WebFramework\Input`




## Methods


### sanitize

Get GET / POST formencoded input vars, sanitizes them and returns them.

```php
public static sanitize(string $var_name, int $var_type = INPUT_TYPE_STRING, mixed $def_value = NULL, int $src = INPUT_SRC_GET): mixed
```



* This method is **static**.




**Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `$var_name` | **string** | Name of the formencoded variable to retrieve |
| `$var_type` | **int** | Type of the variable to sanitize properly |
| `$def_value` | **mixed** | Default value if the variable does not exist |
| `$src` | **int** | Whether to retrieve the variable from GET or POST |





***


***
> Automatically generated on 2025-03-25
