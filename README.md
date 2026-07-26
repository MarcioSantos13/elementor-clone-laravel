# Elementor Clone - Laravel Page Builder

A drag-and-drop page builder for CEAD/UNB, similar to WordPress Elementor, built with Laravel.

## Features

- **Visual Editor**: Drag-and-drop interface for building pages
- **29 Widget Types**: Section, Column, Inner Section, Heading, Text, Image, Button, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion, Callout, Table, Math, Counter, Progress Bar, Social Icons, Icon Box, Image Box, Testimonial, Price Table, Countdown, Google Maps, Carousel
- **27 Control Types**: text, number, textarea, select, color, boolean, url, image, video, wysiwyg, icon, gallery, repeater, typography, background, border, box_shadow, dimensions, hover, custom_css, animation, visibility, gradient, scroll_animation, text_shadow, text_stroke, column_width
- **Responsive Controls**: Desktop/Tablet/Mobile tabs with per-breakpoint values
- **Global Colors & Fonts**: Site-wide color palette and font management
- **Navigator Panel**: Visual tree of elements with search, drag-and-drop reordering, rename, context menu
- **Command Finder**: Ctrl+K to search all actions, widgets, and settings
- **History System**: Undo/Redo support with revision history and visual diff
- **Revision History**: Browse, compare, and restore page revisions
- **Parallax & Video Backgrounds**: Scroll-based parallax and autoplay video backgrounds
- **Shape Dividers**: 10 SVG shape divider types (tilt, waves, mountains, clouds, etc.)
- **Background Overlay**: Color overlay with blend modes
- **Scroll-Triggered Animations**: IntersectionObserver-based fade/zoom/slide animations
- **Image Filters**: 9 CSS filters (grayscale, sepia, blur, brightness, contrast, etc.) + rotation + flip
- **Gradient Backgrounds**: Linear and radial gradients with angle and color stops
- **Text Shadow & Stroke**: Per-element text shadow and stroke effects
- **Sticky Position**: Sticky sections with configurable offset
- **Column Resize**: Percentage-based column width slider
- **Save as Template**: Export page as reusable JSON template
- **Auto-save**: Automatic saving with visual feedback
- **Form Submissions**: Store and manage form submissions
- **Educational Focus**: Specialized widgets for academic content (KaTeX math support)
- **ARIA Accessibility**: Semantic roles, keyboard navigation (Tab, Escape), screen-reader support
- **Real-time Collaboration**: Presence indicators, element locking, cursors
- **HTML Import**: Import pages from external URLs or paste HTML directly

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
│   │   ├── PageController.php          # Pages CRUD + global settings
│   │   ├── ElementController.php       # Elements CRUD + rendering
│   │   ├── RevisionController.php      # Revision history + diff
│   │   ├── FormController.php          # Form submissions
│   │   ├── HtmlImportController.php    # HTML import from URL/paste
│   │   └── CollaborationController.php # Real-time collaboration
│   ├── Models/                         # Eloquent models (Page, Element, Revision, FormSubmission)
│   └── Services/PageBuilder/
│       ├── Core/                       # Core services (Renderer, ElementManager, PageBuilderService)
│       └── Widgets/                    # 29 widget classes extending BaseWidget
├── resources/
│   ├── js/editor/                      # Modular JS editor (8 ES modules)
│   │   ├── index.js                    # Main entry point (2400+ lines)
│   │   ├── state.js                    # Shared state object
│   │   ├── canvas.js                   # Canvas rendering + widget previews
│   │   ├── navigator.js                # Navigator panel + context menus
│   │   ├── history.js                  # Undo/redo
│   │   ├── dragdrop.js                 # Drag-and-drop (SortableJS)
│   │   ├── html-import.js              # HTML import modal
│   │   └── utils.js                    # Utility functions
│   └── views/page-builder/
│       ├── editor.blade.php            # Main editor (uses partials)
│       └── editor/                     # Editor partials
│           ├── css.blade.php           # All editor CSS (~800 lines)
│           ├── toolbar.blade.php       # Top toolbar
│           ├── widget-panel.blade.php  # Left panel (widgets + search)
│           ├── canvas.blade.php        # Center canvas
│           ├── settings-panel.blade.php # Right panel (controls + responsive tabs)
│           ├── navigator.blade.php     # Navigator overlay
│           └── scripts.blade.php       # Vite entry + init script
├── config/page-builder.php             # Widget configuration (29 widgets)
├── routes/page-builder.php             # 50+ API routes
└── vite.config.js                      # Vite configuration
```

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+Z` | Undo |
| `Ctrl+Shift+Z` / `Ctrl+Y` | Redo |
| `Ctrl+S` | Save |
| `Ctrl+D` | Duplicate selected |
| `Ctrl+K` | Open command finder |
| `Ctrl+Shift+C` | Copy styles |
| `Ctrl+Shift+V` | Paste styles |
| `Ctrl+0` | Reset zoom |
| `Ctrl++` / `Ctrl+-` | Zoom in/out |
| `Tab` / `Shift+Tab` | Navigate between elements |
| `Delete` | Delete selected |
| `Escape` | Deselect / close overlay |
| `F11` | Toggle fullscreen |

## Widget Configuration

Widgets are configured in `config/page-builder.php`. Each widget class defines:
- `$type`: Unique identifier
- `$label`: Display name
- `$icon`: CSS icon class
- `$categories`: Widget categories (basic, layout, educational, interactive)
- `$defaultSettings`: Default configuration values
- `$controls`: Available controls with types, labels, tabs, and options

## Development

### Building Assets

```bash
# Development build with hot reload
npm run dev

# Production build
npm run build
```

### Adding a New Widget

1. Create a class in `app/Services/PageBuilder/Widgets/` extending `BaseWidget`
2. Define `$type`, `$label`, `$icon`, `$categories`, `$defaultSettings`, `$controls`
3. Implement `render()` and `renderEditor()` methods
4. Register in `config/page-builder.php` → `widgets` array
5. Add card in `resources/views/page-builder/editor/widget-panel.blade.php`
6. Add rendering case in `resources/js/editor/canvas.js` → `elementHtml()`

## License

MIT License
