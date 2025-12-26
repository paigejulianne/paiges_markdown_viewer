# Paige's Markdown Viewer

A customizable WordPress block and shortcode plugin that renders Markdown content with proper styling.

## Description

Paige's Markdown Viewer allows you to easily add beautifully formatted Markdown content to your WordPress posts and pages. Enter markdown directly in the editor or load it from external URLs, including GitHub repositories.

### Features

- **Gutenberg Block** - Full integration with the WordPress block editor
- **Shortcode Support** - Use `[paiges_markdown]` in the classic editor, widgets, or anywhere shortcodes work
- **External URL Support** - Load markdown files from any URL
- **GitHub Integration** - Automatically converts GitHub blob URLs to raw format
- **Live Preview** - See your rendered markdown in the editor before publishing
- **Scrollable Content** - Set a maximum height for long documents with automatic scrolling
- **Clean Styling** - Beautiful, readable typography with proper code formatting
- **Dark Mode Support** - Automatic dark mode styles based on user preferences
- **Secure** - Uses safe mode parsing to prevent XSS attacks
- **Live Content** - External URLs are fetched on page load, keeping content up-to-date
- **Smart Caching** - URLs are cached for 5 minutes by default, with option to disable caching

## Installation

1. Upload the `paiges_markdown_viewer` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Start using the block or shortcode in your content

## Usage

### Block Editor (Gutenberg)

1. In the block editor, click the **+** button to add a new block
2. Search for "**Markdown**" or find "**Paige's Markdown Viewer**" in the **Text** category
3. Choose between:
   - **Direct Input**: Type or paste your markdown directly
   - **From URL**: Load markdown from an external file

### Shortcode

Use the `[paiges_markdown]` shortcode anywhere shortcodes are supported:

#### Direct Content

```text
[paiges_markdown]
# Hello World

This is **bold** and *italic* text.

- List item 1
- List item 2
- List item 3
[/paiges_markdown]
```

#### From URL

```
[paiges_markdown url="https://example.com/readme.md"]
```

#### From GitHub

```
[paiges_markdown url="https://github.com/user/repo/blob/main/README.md"]
```

GitHub URLs are automatically converted to raw format for proper fetching.

#### With Maximum Height

```
[paiges_markdown url="https://example.com/long-document.md" max_height="400"]
```

This creates a scrollable container with a maximum height of 400 pixels.

#### Disable Caching

```
[paiges_markdown url="https://example.com/file.md" no_cache="true"]
```

By default, external URLs are cached for 5 minutes. Use `no_cache="true"` to fetch fresh content on every page load.

## Supported Markdown Syntax

- **Headings** (# H1, ## H2, ### H3, etc.)
- **Bold** (\*\*text\*\* or \_\_text\_\_)
- **Italic** (\*text\* or \_text\_)
- **Links** (\[text\](url))
- **Images** (!\[alt\](url))
- **Code blocks** (fenced with \`\`\`)
- **Inline code** (\`code\`)
- **Blockquotes** (> quote)
- **Unordered lists** (- item or * item)
- **Ordered lists** (1. item)
- **Horizontal rules** (---)
- **Tables**
- And more...

## Block Settings

In the block editor sidebar, you can configure:

- **Content Source**: Choose between direct input or URL
- **Maximum Height**: Set a pixel value to create a scrollable container (0 = no limit)
- **Disable Caching**: When using URL source, toggle this to fetch fresh content on every page load (off by default)

## Caching Behavior

When loading markdown from external URLs:

- **Default**: Content is cached for 5 minutes to improve performance and reduce server load
- **No Cache**: Enable "Disable Caching" in block settings or use `no_cache="true"` in shortcode for always-fresh content
- **Live Link**: This plugin fetches content from the URL on each page load (subject to caching) - it does not store a permanent copy

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher

## Changelog

### 1.0.0
- Initial release
- Gutenberg block support
- Shortcode support
- External URL loading
- GitHub URL conversion
- Maximum height with scrolling
- Dark mode support

## License

This plugin is licensed under the GPL-2.0-or-later license. See the [LICENSE](LICENSE) file for details.

## Author

**Paige Julianne Sullivan**
[https://paigejulianne.com/](https://paigejulianne.com/)

## Support Development

If you find this plugin useful and it saves you time, please consider making a donation to support continued development and maintenance. Your contribution helps keep this plugin free and actively maintained for the WordPress community.

[**Donate via PayPal**](https://www.paypal.com/donate/?hosted_button_id=3X8QMH7RTRTGN)

Every donation, no matter the size, is greatly appreciated and helps fuel future updates and new features. Thank you for your support!

## Credits

This plugin uses [Parsedown](https://parsedown.org/) for Markdown parsing.
