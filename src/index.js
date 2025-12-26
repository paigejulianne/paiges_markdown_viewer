(function (wp) {
    const { registerBlockType } = wp.blocks;
    const { createElement: el, useState, useEffect } = wp.element;
    const { TextareaControl, TextControl, RadioControl, RangeControl, PanelBody, Placeholder, Spinner, ToggleControl } = wp.components;
    const { InspectorControls, useBlockProps } = wp.blockEditor;

    // Simple markdown to HTML converter for preview
    function parseMarkdownPreview(markdown) {
        if (!markdown) return '';

        let html = markdown;

        // Escape HTML first
        html = html.replace(/&/g, '&amp;')
                   .replace(/</g, '&lt;')
                   .replace(/>/g, '&gt;');

        // Code blocks (fenced)
        html = html.replace(/```(\w*)\n([\s\S]*?)```/g, function(match, lang, code) {
            return '<pre><code class="language-' + lang + '">' + code.trim() + '</code></pre>';
        });

        // Inline code
        html = html.replace(/`([^`]+)`/g, '<code>$1</code>');

        // Headers
        html = html.replace(/^######\s+(.*)$/gm, '<h6>$1</h6>');
        html = html.replace(/^#####\s+(.*)$/gm, '<h5>$1</h5>');
        html = html.replace(/^####\s+(.*)$/gm, '<h4>$1</h4>');
        html = html.replace(/^###\s+(.*)$/gm, '<h3>$1</h3>');
        html = html.replace(/^##\s+(.*)$/gm, '<h2>$1</h2>');
        html = html.replace(/^#\s+(.*)$/gm, '<h1>$1</h1>');

        // Bold
        html = html.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        html = html.replace(/__([^_]+)__/g, '<strong>$1</strong>');

        // Italic
        html = html.replace(/\*([^*]+)\*/g, '<em>$1</em>');
        html = html.replace(/_([^_]+)_/g, '<em>$1</em>');

        // Links
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2">$1</a>');

        // Images
        html = html.replace(/!\[([^\]]*)\]\(([^)]+)\)/g, '<img src="$2" alt="$1" />');

        // Horizontal rules
        html = html.replace(/^(-{3,}|\*{3,}|_{3,})$/gm, '<hr />');

        // Blockquotes
        html = html.replace(/^>\s+(.*)$/gm, '<blockquote>$1</blockquote>');

        // Unordered lists
        html = html.replace(/^[\*\-\+]\s+(.*)$/gm, '<li>$1</li>');

        // Ordered lists
        html = html.replace(/^\d+\.\s+(.*)$/gm, '<li>$1</li>');

        // Wrap consecutive li elements
        html = html.replace(/(<li>.*<\/li>\n?)+/g, '<ul>$&</ul>');

        // Paragraphs (simple approach)
        html = html.split(/\n\n+/).map(function(para) {
            para = para.trim();
            if (para && !para.match(/^<(h[1-6]|ul|ol|li|pre|blockquote|hr)/)) {
                return '<p>' + para + '</p>';
            }
            return para;
        }).join('\n');

        // Clean up line breaks
        html = html.replace(/\n/g, '<br />');
        html = html.replace(/<br \/><br \/>/g, '');
        html = html.replace(/<(\/?(h[1-6]|ul|ol|li|pre|blockquote|p|hr|div))><br \/>/g, '<$1>');
        html = html.replace(/<br \/><(\/?(h[1-6]|ul|ol|li|pre|blockquote|p|hr|div))>/g, '<$1>');

        return html;
    }

    registerBlockType('paiges-markdown-viewer/markdown', {
        title: "Paige's Markdown Viewer",
        icon: 'editor-code',
        category: 'text',
        description: 'Display markdown content with proper rendering.',
        keywords: ['markdown', 'md', 'code', 'text', 'github'],
        attributes: {
            sourceType: { type: 'string', default: 'direct' },
            markdownContent: { type: 'string', default: '' },
            markdownUrl: { type: 'string', default: '' },
            maxHeight: { type: 'number', default: 0 },
            noCache: { type: 'boolean', default: false }
        },
        edit: function (props) {
            const { attributes, setAttributes } = props;
            const { sourceType, markdownContent, markdownUrl, maxHeight, noCache } = attributes;
            const blockProps = useBlockProps({ className: 'markdown-viewer-editor' });

            const [previewContent, setPreviewContent] = useState('');
            const [isLoading, setIsLoading] = useState(false);
            const [showPreview, setShowPreview] = useState(false);

            // Fetch URL content for preview
            useEffect(function() {
                if (sourceType === 'url' && markdownUrl) {
                    setIsLoading(true);
                    // Convert GitHub blob URLs to raw URLs
                    let fetchUrl = markdownUrl;
                    const githubMatch = markdownUrl.match(/github\.com\/([^\/]+)\/([^\/]+)\/blob\/(.+)/);
                    if (githubMatch) {
                        fetchUrl = 'https://raw.githubusercontent.com/' + githubMatch[1] + '/' + githubMatch[2] + '/' + githubMatch[3];
                    }

                    fetch(fetchUrl)
                        .then(function(response) {
                            if (!response.ok) throw new Error('Failed to fetch');
                            return response.text();
                        })
                        .then(function(text) {
                            setPreviewContent(text);
                            setIsLoading(false);
                        })
                        .catch(function(error) {
                            setPreviewContent('Error loading markdown from URL');
                            setIsLoading(false);
                        });
                } else {
                    setPreviewContent(markdownContent);
                }
            }, [sourceType, markdownUrl, markdownContent]);

            return el(
                'div',
                blockProps,
                // Inspector Controls (sidebar)
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Markdown Source', initialOpen: true },
                        el(RadioControl, {
                            label: 'Content Source',
                            selected: sourceType,
                            options: [
                                { label: 'Enter Markdown Directly', value: 'direct' },
                                { label: 'Load from URL', value: 'url' }
                            ],
                            onChange: function (value) {
                                setAttributes({ sourceType: value });
                            }
                        })
                    ),
                    el(
                        PanelBody,
                        { title: 'Display Settings', initialOpen: true },
                        el(RangeControl, {
                            label: 'Maximum Height (px)',
                            help: maxHeight > 0 ? 'Content exceeding this height will scroll.' : 'Set to 0 for no limit.',
                            value: maxHeight || 0,
                            onChange: function (value) {
                                setAttributes({ maxHeight: value });
                            },
                            min: 0,
                            max: 1000,
                            step: 50
                        }),
                        maxHeight > 0 && el(
                            'p',
                            { style: { fontSize: '12px', color: '#757575', marginTop: '-8px' } },
                            'Current: ' + maxHeight + 'px'
                        ),
                        sourceType === 'url' && el(ToggleControl, {
                            label: 'Disable Caching',
                            help: noCache ? 'Content is fetched fresh on every page load.' : 'Content is cached for 5 minutes.',
                            checked: noCache,
                            onChange: function (value) {
                                setAttributes({ noCache: value });
                            }
                        })
                    )
                ),
                // Main editor content
                el(
                    'div',
                    { className: 'markdown-viewer-editor-container' },
                    el(
                        'div',
                        { className: 'markdown-viewer-header' },
                        el('span', { className: 'markdown-viewer-title' }, "📝 Paige's Markdown Viewer"),
                        el(
                            'button',
                            {
                                className: 'markdown-viewer-toggle-preview',
                                onClick: function() { setShowPreview(!showPreview); }
                            },
                            showPreview ? 'Edit' : 'Preview'
                        )
                    ),
                    el(
                        'div',
                        { className: 'markdown-viewer-tabs' },
                        el(
                            'button',
                            {
                                className: 'markdown-viewer-tab' + (sourceType === 'direct' ? ' active' : ''),
                                onClick: function() { setAttributes({ sourceType: 'direct' }); }
                            },
                            'Direct Input'
                        ),
                        el(
                            'button',
                            {
                                className: 'markdown-viewer-tab' + (sourceType === 'url' ? ' active' : ''),
                                onClick: function() { setAttributes({ sourceType: 'url' }); }
                            },
                            'From URL'
                        )
                    ),
                    showPreview
                        ? el(
                            'div',
                            {
                                className: 'markdown-viewer-preview markdown-viewer-content' + (maxHeight > 0 ? ' has-max-height' : ''),
                                style: maxHeight > 0 ? { maxHeight: maxHeight + 'px', overflowY: 'auto' } : {}
                            },
                            isLoading
                                ? el(Spinner)
                                : el('div', {
                                    dangerouslySetInnerHTML: { __html: parseMarkdownPreview(previewContent) }
                                })
                        )
                        : el(
                            'div',
                            { className: 'markdown-viewer-input-area' },
                            sourceType === 'direct'
                                ? el('textarea', {
                                    className: 'markdown-viewer-textarea',
                                    value: markdownContent,
                                    onChange: function(e) {
                                        setAttributes({ markdownContent: e.target.value });
                                    },
                                    placeholder: 'Enter your Markdown here...\n\n# Heading\n\nParagraph with **bold** and *italic* text.\n\n```javascript\nconsole.log("Hello World");\n```',
                                    rows: 15
                                })
                                : el(
                                    'div',
                                    { className: 'markdown-viewer-url-input' },
                                    el(TextControl, {
                                        label: 'Markdown File URL',
                                        value: markdownUrl,
                                        onChange: function (value) {
                                            setAttributes({ markdownUrl: value });
                                        },
                                        placeholder: 'https://github.com/user/repo/blob/main/README.md',
                                        help: 'Enter a URL to a .md file. GitHub URLs are automatically converted to raw format.'
                                    }),
                                    isLoading && el(Spinner)
                                )
                        )
                )
            );
        },
        save: function () {
            // Dynamic block - render handled by PHP
            return null;
        }
    });
})(window.wp);
