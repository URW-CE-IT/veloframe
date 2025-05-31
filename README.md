# VeloFrame

**VeloFrame** (fmr. WebFramework) is a lightweight yet powerful PHP framework designed for rapid, scalable, and easy-to-maintain web development. It provides a foundational template containing essential classes and functions to streamline building websites, web applications, or RESTful APIs.

## Key Features

- **Modular Structure:** Clear and maintainable separation between page controllers, templates, and components.
- **Templating Engine:** Easily create reusable HTML components and dynamic templates.
- **Routing System:** Built-in simple and flexible URI management and routing.
- **No External Dependencies:** Clean setup without external libraries or package managers.
- **Secure and Extendable:** Designed with security best practices and easy extensibility in mind.

## Project Structure
```
pages/                 # Contains page controllers
templates/             # Contains HTML template files
templates/components/  # Contains reusable HTML components
VeloFrame/          # Core framework classes and functions
index.php              # Main entry point of the application
.htaccess              # URL rewrite rules
```

## Getting Started

To quickly start a new project:

1. **Clone the Repository:**
   ```bash
   git clone https://github.com/URW-CE-IT/veloframe.git
   ```

2. **Customize your Project:**
   - Create your pages in the `pages/` folder.
   - Define HTML templates in `templates/`.
   - Develop reusable HTML blocks in `templates/components/`.
   - Extend or customize framework functionalities in `VeloFrame/`.

3. **Start the Development Server:**
   If you're using PHP's built-in server (note: it does NOT support `.htaccess`):
   ```bash
   php -S localhost:8000 index.php
   ```

   For full support including URL rewriting, use Apache or another web server supporting `.htaccess`.

4. **Access your Application:**
   Open a browser and visit `http://localhost:8000`.

## Documentation

Comprehensive documentation for VeloFrame is available here: [VeloFrame Documentation](https://github.com/URW-CE-IT/veloframe/wiki).

## Contributing

We welcome contributions from the community to enhance VeloFrame. To contribute:

1. **Fork the Repository**.
2. **Create a descriptive branch** for your feature or fix.
3. **Implement your changes** following the project's coding standards.
4. **Submit a pull request** clearly explaining your changes.

Detailed guidelines can be found in our [Contributing Guide](https://github.com/URW-CE-IT/veloframe/blob/main/CONTRIBUTING.md).

## License

VeloFrame is open-source software under the [MIT License](https://github.com/URW-CE-IT/veloframe/blob/main/LICENSE).
