# MarkdownSectionTask

`Maestro\Markdown\Task\MarkdownSectionTask`
## Parameters
- **path** Path to existing or target markdown file - `string`
- **header** Header to match - `string`
- **content** Content to replace section with - `string`
- **prepend** Prepend a new section instead of appending it. - `bool`
## Description
Replace a section in a markdown document corresponding to the given header.

```php
new MarkdownSectionTask(
    path: 'README.md',
    header: '## Contributing',
    content: "## Contributing\n\nHello There"
);
```

If the document does not exist, it will be created.