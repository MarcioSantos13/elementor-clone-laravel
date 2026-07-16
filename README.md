# Elementor Clone - Laravel Page Builder

A drag-and-drop page builder for CEAD/UNB, similar to WordPress Elementor, built with Laravel.

## Features

- **Visual Editor**: Drag-and-drop interface for building pages
- **17 Widget Types**: Section, Column, Heading, Text, Image, Button, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Callout, Table, Math
- **Responsive Preview**: Desktop, Tablet, Mobile views
- **Navigator Panel**: Visual tree of all elements
- **History System**: Undo/Redo support
- **Auto-save**: Automatic saving with visual feedback
- **Page Templates**: Pre-built layouts
- **Form Submissions**: Store and manage form submissions
- **Educational Focus**: Specialized widgets for academic content (KaTeX math support)

## Requirements

- PHP 8.2+
- Laravel 12
- Node.js 18+
- npm

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd elementor-clone-laravel

# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Build frontend assets
npm run build

# Start development server
php artisan serve
```

## Project Structure

```
├── app/
│   ├── Http/Controllers/PageBuilder/   # Page builder controllers
│   ├── Models/                         # Eloquent models (Page, FormSubmission)
│   └── Services/PageBuilder/Core/      # Core services (ElementManager, Renderer)
├── resources/
│   ├── js/editor/                      # Modular JS editor (ES modules)
│   │   ├── index.js                    # Main entry point
│   │   ├── state.js                    # Shared state object
│   │   ├── canvas.js                   # Canvas rendering
│   │   ├── dragdrop.js                 # Drag-and-drop logic
│   │   ├── history.js                  # Undo/redo
│   │   ├── navigator.js                # Navigator panel
│   │   └── utils.js                    # Utility functions
│   └── views/page-builder/
│       ├── editor.blade.php            # Main editor (uses partials)
│       └── editor/                     # Editor partials
│           ├── css.blade.php
│           ├── toolbar.blade.php
│           ├── widget-panel.blade.php
│           ├── canvas.blade.php
│           ├── settings-panel.blade.php
│           ├── navigator.blade.php
│           └── scripts.blade.php
├── config/page-builder.php             # Widget configuration
├── routes/page-builder.php             # Page builder routes
└── vite.config.js                      # Vite configuration
```

## Widget Configuration

Widgets are configured in `config/page-builder.php`. Each widget defines:
- `label`: Display name
- `icon`: HTML entity or emoji
- `category`: Widget category (layout, basic, educational)
- `settings`: Available configuration options
- `children`: Allowed child widget types (for containers)
- `defaultContent`: Default content for new instances

## Development

### Building Assets

```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Adding a New Widget

1. Add widget configuration to `config/page-builder.php`
2. Add rendering logic in `app/Services/PageBuilder/Core/Renderer.php`
3. Add settings form in the editor's `buildSettingsForm()` method

## License

MIT License