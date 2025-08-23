# VeloFrame Template System - Enhanced Features

This document describes the enhanced VeloFrame template system with support for conditionals, loops, and advanced template features.

## New Template Syntax

### Conditional Statements

#### Basic If Statement
```html
{%if variable_name%}
    Content to show if variable is true
{%endif%}
```

#### If/Else Statement
```html
{%if variable_name%}
    Content to show if variable is true
{%else%}
    Content to show if variable is false
{%endif%}
```

#### Examples
```html
<!-- Simple conditional -->
{%if user_logged_in%}
    Welcome back!
{%endif%}

<!-- If/else conditional -->
{%if is_admin%}
    <a href="/admin">Admin Panel</a>
{%else%}
    <span>Regular User</span>
{%endif%}

<!-- Multiple conditionals -->
{%if show_header%}<header>Site Header</header>{%endif%}
{%if show_content%}<main>Main Content</main>{%endif%}
{%if show_footer%}<footer>Site Footer</footer>{%endif%}
```

### Loop Statements

#### Basic For Loop
```html
{%for item in array_name%}
    Content to repeat for each item
    Use {[item]} to access the current item
{%endfor%}
```

#### Loop with Object Properties
```html
{%for user in users%}
    <div>Name: {[user.name]}, Email: {[user.email]}</div>
{%endfor%}
```

#### Examples
```html
<!-- Simple array loop -->
{%for product in products%}
    <p>Product: {[product]}</p>
{%endfor%}

<!-- Object array loop -->
{%for user in users%}
    <div class="user">
        <h3>{[user.name]}</h3>
        <p>Email: {[user.email]}</p>
        <p>Role: {[user.role]}</p>
    </div>
{%endfor%}

<!-- Loop with conditionals -->
{%for item in items%}
    <div>
        {[item.name]}
        {%if item.featured%}
            <span class="badge">Featured</span>
        {%endif%}
    </div>
{%endfor%}
```

### Nested Conditionals

The template system supports nested conditionals:

```html
{%if show_users%}
    <h2>Users</h2>
    {%if has_admin_users%}
        <h3>Administrators</h3>
        <!-- Admin user content -->
    {%else%}
        <h3>Regular Users</h3>
        <!-- Regular user content -->
    {%endif%}
{%else%}
    <p>No users to display</p>
{%endif%}
```

### Complex Nesting - Loops with Conditionals

```html
{%for user in users%}
    <div class="user-card">
        <h3>{[user.name]}</h3>
        {%if user.active%}
            <span class="status active">Active</span>
            {%if user.premium%}
                <span class="badge premium">Premium Member</span>
            {%endif%}
        {%else%}
            <span class="status inactive">Inactive</span>
        {%endif%}
    </div>
{%endfor%}
```

## Variable Types Support

The template system supports various PHP data types:

### Boolean Variables
```php
$template->setVariable('is_admin', true);
$template->setVariable('show_section', false);
```

### String Variables
```php
$template->setVariable('username', 'John Doe');
$template->setVariable('page_title', 'Welcome');
```

### Numeric Variables
```php
$template->setVariable('user_count', 42);
$template->setVariable('price', 29.99);
```

### Array Variables
```php
// Simple array
$template->setVariable('items', ['Apple', 'Banana', 'Cherry']);

// Array of objects/associative arrays
$template->setVariable('users', [
    ['name' => 'John', 'email' => 'john@example.com', 'active' => true],
    ['name' => 'Jane', 'email' => 'jane@example.com', 'active' => false]
]);
```

## Component Support

Both Template and TemplateComponent classes support the new conditional and loop functionality:

### Template Components with Conditionals
```html
<!-- Component file: user_card.htm -->
<div class="card">
    {%if show_avatar%}
        <img src="{[avatar_url]}" alt="Avatar">
    {%endif%}
    <h3>{[name]}</h3>
    <p>{[email]}</p>
</div>
```

### Template Components with Loops
```html
<!-- Component file: product_list.htm -->
<div class="product-list">
    <h2>{[title]}</h2>
    {%for product in products%}
        <div class="product">
            <h4>{[product.name]}</h4>
            <p>Price: ${[product.price]}</p>
            {%if product.sale%}
                <span class="sale-badge">ON SALE!</span>
            {%endif%}
        </div>
    {%endfor%}
</div>
```

## Usage Examples

### PHP Code
```php
use VeloFrame\Template;
use VeloFrame\TemplateComponent;

// Basic template with conditionals
$template = new Template('user_dashboard');
$template->setVariable('user_name', 'John Doe');
$template->setVariable('is_admin', true);
$template->setVariable('show_notifications', false);

// Template with loops
$template->setVariable('recent_posts', [
    ['title' => 'Post 1', 'date' => '2024-01-15', 'published' => true],
    ['title' => 'Post 2', 'date' => '2024-01-14', 'published' => false],
    ['title' => 'Post 3', 'date' => '2024-01-13', 'published' => true]
]);

echo $template->output();

// Component usage
$product_list = new TemplateComponent('product_list', [
    'title' => 'Featured Products',
    'products' => [
        ['name' => 'Laptop', 'price' => '999', 'sale' => true],
        ['name' => 'Mouse', 'price' => '29', 'sale' => false]
    ]
]);

echo $product_list->output();
```

### Template File Example
```html
<!-- templates/user_dashboard.htm -->
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - {[user_name]}</title>
</head>
<body>
    <h1>Welcome, {[user_name]}!</h1>
    
    {%if is_admin%}
        <div class="admin-panel">
            <a href="/admin">Administration</a>
        </div>
    {%endif%}
    
    {%if show_notifications%}
        <div class="notifications">
            <!-- Notification content -->
        </div>
    {%else%}
        <p>No new notifications</p>
    {%endif%}
    
    <h2>Recent Posts</h2>
    {%for post in recent_posts%}
        <article>
            <h3>{[post.title]}</h3>
            <p>Date: {[post.date]}</p>
            {%if post.published%}
                <span class="status published">Published</span>
            {%else%}
                <span class="status draft">Draft</span>
            {%endif%}
        </article>
    {%endfor%}
</body>
</html>
```

## Error Handling

### Missing Variables
- Variables not set will use the default value specified in `output($var_default)`
- If no default is provided, missing variables will be left as empty strings

### Empty Arrays
- Loops with empty arrays will produce no output
- Loops with non-existent array variables will produce no output

### Boolean Evaluation
- Variables are evaluated as truthy/falsy in conditionals
- `false`, `0`, `""`, `null`, and empty arrays are considered false
- Everything else is considered true

## Backward Compatibility

All existing VeloFrame template functionality remains unchanged:

- Basic variable substitution: `{[variable_name]}`
- Template inclusion: `{![template_name]}`
- Component prefix/suffix syntax: `{prefix|[variable]|suffix}`
- Inline components: `<_component_name>content</_component_name>`

## Performance Considerations

- Conditional and loop processing adds minimal overhead
- Nested loops may impact performance with very large datasets
- Template caching is recommended for production use

## Best Practices

1. **Keep logic simple**: Use conditionals for display logic, not business logic
2. **Limit nesting depth**: Avoid deeply nested conditionals and loops
3. **Use meaningful variable names**: Make templates self-documenting
4. **Test edge cases**: Verify behavior with empty arrays and missing variables
5. **Validate data**: Ensure arrays and objects have expected structure before passing to templates

## Troubleshooting

### Common Issues

1. **Conditionals not working**: Check variable names match exactly
2. **Loops not displaying**: Verify array variable is set and contains data
3. **Variables not substituting**: Ensure variable names don't contain invalid characters
4. **Nested structures**: Use dotted notation for object properties (`user.name`)

### Debug Tips

1. Use `var_dump()` to inspect variables before setting them
2. Test templates with known data sets
3. Check template file paths and permissions
4. Use the built-in debug output (controlled by DEBUG constant)

---

This enhanced template system provides powerful functionality while maintaining the simplicity and performance that VeloFrame is known for.