# Selene

A lightweight, standalone implementation of Laravel's Blade template engine syntax.

This project aims to provide a parser and renderer for Blade template syntax without requiring the full Laravel framework.

## Why This Project?

While Laravel's Blade template engine is powerful and elegant, sometimes you need a standalone solution that doesn't require the entire Laravel framework. This project will fill that gap by providing:

- Blade syntax
- No Laravel dependencies
- Easy integration into any PHP project
- Easy to extend with custom directives
- Compatible with existing Blade templates

## Roadmap

### Completed Features ✅
- Basic Blade syntax parsing
- Basic compiling (@if, @foreach, @while, {{ $variables }})

### In Progress 🚧
- Anonymous components (slots, attributes, properties)
- Layout directives (@extends, @section, @yield...)
- Custom directive support
- Template validation
- Documentation

### Planned Features 📋
- Template inheritance (@extends, @section)
- Cache system for compiled templates
- Additional built-in directives
- Error handling improvements
- Integration examples

### Future Considerations 🔮
- Template precompilation
- IDE integration
- Template debugging tools
- Plugin system
