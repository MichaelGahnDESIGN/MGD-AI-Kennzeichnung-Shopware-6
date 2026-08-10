# MGD AI Image Labels for Shopware 6

MGD AI Image Labels adds transparent, accessible labels to AI-generated or AI-edited images in a Shopware 6 storefront. Editors assign the status to a media item; the plugin does not detect AI content and never sends images to an external AI service.

## Requirements

- Shopware 6.6.10 or later in the 6.6 line, or a compatible Shopware 6.7 release
- PHP 8.2 or later
- a current database and file backup before production changes

## Installation

Upload the release ZIP in **Extensions > My extensions**, then install and activate it. Alternatively, copy the ZIP's `MGDAIImageLabels` directory to `custom/plugins/` and run:

```bash
bin/console plugin:refresh
bin/console plugin:install --activate MGDAIImageLabels
bin/console cache:clear
bin/console theme:compile
```

Validate the result in staging and in every active sales channel before a live rollout.

## Usage

Open an image in **Content > Media** and set the fields in **AI image labeling**. Available statuses are no label, fully AI-generated, partially AI-generated, modified with AI, and deepfake. Position and theme may be overridden per medium. Save with Shopware's normal media action.

In the extension configuration, the default language mode `Automatic` follows the sales-channel locale. German and English can also be forced explicitly. Display dimensions are global and restricted to safe ranges.

The Shopping Experiences element **Labeled background image** uses a local image medium and its label. Non-decorative images require meaningful alternative text. The plugin settings can prepare a bilingual AI-philosophy layout; it remains unlinked and unpublished until an editor reviews and assigns it.

## Privacy, accessibility and limitations

The plugin stores only configuration and media custom fields in Shopware. It does not upload, analyse, fingerprint or automatically classify images. Labels are textual, exposed as notes to assistive technology and do not block image interactions. Accessibility still depends on the active theme and editorial alternative text.

Custom themes that fully replace Shopware's thumbnail template may need an explicit integration. The real Shopware 6.6.10/6.7 installation matrix is still an open release-quality step; treat version 0.1.0 as a pre-release until that matrix has been completed and documented.

When uninstalling with **Keep user data**, the plugin custom-field set and plugin configuration remain. Without that option, the field definitions, media relation and Shopware plugin configuration are removed. Media files remain untouched; the three JSON keys already stored on media items may remain as technically unused values because the plugin does not rewrite every media record during uninstall.

For details, see the German [main documentation](README.md), [security policy](SECURITY.md), [theme guide](Dokumentation/Integration-eigener-Themes.md), and [rollback guide](Dokumentation/Deployment-und-Rueckfall.md).

## License

Licensed under `GPL-2.0-or-later`; see [LICENSE](LICENSE).
