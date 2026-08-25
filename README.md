# DravenCMS Structure

Content structure and menu routing for DravenCMS applications. The package manages hierarchical menu entries, translated slugs and metadata, CMS component placement, frontend layouts, and route resolution.

## Features

- Hierarchical menu and page structure.
- Localized names, slugs, titles, headings, and metadata.
- Dynamic mapping between menu entries and frontend presenter actions.
- CMS component discovery and placement in page layouts.
- Configurable frontend layout directory and presenter mappings.
- Optional sitemap and `robots.txt` integration with DravenCMS SEO.
- Admin menu, ACL, and Doctrine fixture support.

## Requirements

- PHP version supported by the installed DravenCMS stack.
- DravenCMS Locale 2.0 or newer.
- DravenCMS Admin 2.0 or newer.
- DravenCMS SEO is optional.

## Installation

Install the package with Composer:

```bash
composer require dravencms/structure
```

The DravenCMS package loader reads `dravencms.config.neon` from the package metadata. When integrating the package without that loader, include the file from your application configuration, adjusting the relative path as needed:

```neon
includes:
    - ../vendor/dravencms/structure/dravencms.config.neon
```

Configure the extension for your frontend application:

```neon
dravencms.structure:
    tempPath: %appDir%/FrontModule/cms
    presenterModule: Front
    presenterMapping: Dravencms\*Module\*Presenter
    parentClass: Dravencms\FrontModule\SlugPresenter
    layoutDir: %appDir%/FrontModule/templates
    defaultLayout: layout
    mappings:
        Dravencms\FrontModule\Components\*\*\-\*Factory: Dravencms\Model\*\Repository\*CmsRepository
```

Apply the resulting database schema changes through the Doctrine migration workflow used by your application. Load the supplied fixtures when you need the default administration menu and ACL definitions.

## Configuration

| Option | Purpose |
| --- | --- |
| `tempPath` | Directory used for generated CMS templates |
| `presenterModule` | Frontend module containing routed presenters |
| `presenterMapping` | Pattern used to resolve presenter classes |
| `parentClass` | Base presenter used by generated CMS presenters |
| `layoutDir` | Directory containing frontend layouts |
| `defaultLayout` | Default layout name for new structure entries |
| `mappings` | CMS component factory patterns mapped to repository patterns |
| `templateOverrides` | Optional template-path overrides |

## SEO Integration

When `dravencms/seo` is installed, Structure detects its provider interfaces and registers an integration service automatically. Sitemap-enabled menu entries are contributed to `/sitemap.xml`, including route parameters, modification dates, priorities, and locale alternates. Entries excluded from the sitemap are contributed as `Disallow` records to `/robots.txt`.

Structure does not require SEO, and SEO does not require Structure. The integration service is omitted when the SEO interfaces are unavailable.

## License

This package is licensed under the LGPL-3.0 license.
