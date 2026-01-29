# Breakdance Icon Fix

This plugin fixes color selection for stroke-based icons.
https://wpsix.com/breakdance-icons/

## Description

This plugin extends Breakdance's icon functionality by registering a custom macro that handles icon CSS rendering. It addresses icon styling issues by providing a modified `atomV1IconCss` macro with improved stroke color handling and styling options.

## Features

- **Custom Icon CSS Macro**: Provides an enhanced `atomV1IconCss` Twig macro for better icon rendering
- **Stroke Color Support**: Adds explicit stroke color styling for SVG icons
- **Style Variants**: Supports solid and outline icon styles
- **Hover States**: Includes hover state styling for interactive icons

## Installation

1. Download or clone this plugin to your WordPress plugins directory
2. Ensure Breakdance page builder is installed and activated
3. Activate the "Breakdance Icon Fix" plugin through the WordPress plugins menu

## Requirements

- WordPress 5.0 or higher
- Breakdance page builder plugin (active)
- PHP 7.4 or higher

## How It Works

The plugin hooks into Breakdance's loading sequence and registers a custom macro save location. This macro overrides or extends the default icon CSS generation to include stroke color properties on both normal and hover states, ensuring consistent icon rendering.

### Key Enhancements

The `atom-v1-icon-css.twig` macro adds:
- Stroke color alongside fill color for SVG icons (line 62, 78)
- Proper color inheritance for icon elements
- Enhanced hover state handling
- Better default styling for solid and outline variants

## Usage

Once activated, the plugin automatically integrates with Breakdance. The custom macro will be available for use in Breakdance elements that utilize icon styling.

## File Structure

```
breakdance-icon-fix/
├── plugin.php                          # Main plugin file
├── readme.md                           # This file
└── macros/
    └── atom-v1-icon-css.twig          # Custom icon CSS macro
```

## Author

**Bence Boruzs**  
Website: [wpsix.com](https://wpsix.com/breakdance-icons/)

## License

GPL2 - GNU General Public License v2 or later

## Changelog

### 1.0.0
- Initial release
- Custom atomV1IconCss macro with stroke color support
- Integration with Breakdance Element Studio
