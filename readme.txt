=== Paige's Markdown Viewer ===
Contributors: paigejulianne
Tags: markdown, gutenberg, block, shortcode, github
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A customizable WordPress block and shortcode that renders Markdown content with proper styling.

== Description ==

Paige's Markdown Viewer allows you to easily add beautifully formatted Markdown content to your WordPress posts and pages. Enter markdown directly in the editor or load it from external URLs, including GitHub repositories.

= Features =

* **Gutenberg Block** - Full integration with the WordPress block editor
* **Shortcode Support** - Use `[paiges_markdown]` in the classic editor, widgets, or anywhere shortcodes work
* **External URL Support** - Load markdown files from any URL
* **GitHub Integration** - Automatically converts GitHub blob URLs to raw format
* **Live Preview** - See your rendered markdown in the editor before publishing
* **Scrollable Content** - Set a maximum height for long documents with automatic scrolling
* **Clean Styling** - Beautiful, readable typography with proper code formatting
* **Dark Mode Support** - Automatic dark mode styles based on user preferences
* **Secure** - Uses safe mode parsing to prevent XSS attacks
* **Live Content** - External URLs are fetched on page load, keeping content up-to-date
* **Smart Caching** - URLs are cached for 5 minutes by default, with option to disable

== Installation ==

1. Upload the `paiges_markdown_viewer` folder to `/wp-content/plugins/`
2. Activate the plugin through the **Plugins** menu in WordPress
3. Start using the block or shortcode in your content

== Usage ==

= Block Editor (Gutenberg) =

1. In the block editor, click the **+** button to add a new block
2. Search for "Markdown" or find "Paige's Markdown Viewer" in the Text category
3. Choose between Direct Input or From URL

= Shortcode =

Use the `[paiges_markdown]` shortcode anywhere shortcodes are supported:

**Direct Content:**
`[paiges_markdown]# Hello World
This is **bold** and *italic* text.[/paiges_markdown]`

**From URL:**
`[paiges_markdown url="https://example.com/readme.md"]`

**From GitHub:**
`[paiges_markdown url="https://github.com/user/repo/blob/main/README.md"]`

**With Maximum Height:**
`[paiges_markdown url="https://example.com/doc.md" max_height="400"]`

**Disable Caching:**
`[paiges_markdown url="https://example.com/file.md" no_cache="true"]`

== Frequently Asked Questions ==

= What markdown syntax is supported? =

The plugin supports standard Markdown including headings, bold, italic, links, images, code blocks, inline code, blockquotes, lists, horizontal rules, and tables.

= How does caching work? =

By default, external URLs are cached for 5 minutes to improve performance. You can disable caching per-block using the "Disable Caching" toggle, or per-shortcode using `no_cache="true"`.

= Does it work with GitHub URLs? =

Yes! GitHub blob URLs (e.g., `github.com/user/repo/blob/main/README.md`) are automatically converted to raw URLs for proper fetching.

= Is the content stored permanently? =

No. The plugin fetches content from the URL on each page load (subject to caching). It does not store a permanent copy.

== Screenshots ==

1. Block editor interface with markdown input
2. Rendered markdown on the frontend
3. Block settings sidebar

== Changelog ==

= 1.0.0 =
* Initial release
* Gutenberg block support
* Shortcode support
* External URL loading with caching
* GitHub URL conversion
* Maximum height with scrolling
* Dark mode support
* No-cache option for live content

== Upgrade Notice ==

= 1.0.0 =
Initial release.

== Credits ==

This plugin uses [Parsedown](https://parsedown.org/) for Markdown parsing.

== Support Development ==

If you find this plugin useful, please consider [making a donation](https://www.paypal.com/donate/?hosted_button_id=3X8QMH7RTRTGN) to support continued development.
