# ChattyBox AI Chatbot for Drupal

Add the ChattyBox source-cited AI chatbot to public Drupal pages without editing theme templates.

Connect your Drupal site to a ChattyBox project, then let ChattyBox answer from your published content with links back to the source pages.

## Requirements

- Drupal 10 or 11.
- PHP 8.1 or newer. Drupal core may require a newer PHP version for a specific release.
- A ChattyBox project and its public widget API key.

## Installation

### Composer

For a Drupal project managed with Composer, add this named VCS repository to the consuming project's root `composer.json`:

```json
{
  "repositories": {
    "chattybox-drupal": {
      "type": "vcs",
      "url": "https://github.com/OpenStaticFish/chattybox-drupal.git"
    }
  }
}
```

If the project already has a `repositories` section, merge only the `chattybox-drupal` entry into it. Then require the tagged module:

```sh
composer require openstaticfish/chattybox-drupal:^0.1
```

The equivalent Composer CLI setup is:

```sh
composer config repositories.chattybox-drupal vcs https://github.com/OpenStaticFish/chattybox-drupal
composer require openstaticfish/chattybox-drupal:^0.1
```

To use the current development branch instead, require `dev-main`.

The package is declared as a `drupal-module`, so Drupal Composer projects with the standard installer paths place it under `web/modules/contrib/`. Custom Composer projects should map the `drupal-module` package type to their module directory.

### Manual

1. Copy this `chattybox` directory into `web/modules/custom/` or your site's custom module directory.
2. Enable **ChattyBox** from **Extend**, or run `drush en chattybox`.
3. Open **Configuration > Web services > ChattyBox**.
4. Create a ChattyBox project, crawl the public Drupal URL, and test representative questions.
5. Copy the **public widget API key** from the project Embed tab.
6. Paste the key into the module settings and enable the chatbot.

The production API endpoint is configured automatically. The public API key is designed for frontend use; never paste a ChattyBox management credential into the module settings.

## Configuration

The production defaults are:

- Widget script: `https://chattybox.ai/widget.js`
- API endpoint: configured automatically by the module
- Locale: automatic detection from the page language and visitor browser

The module stores only the public widget key and display settings in Drupal configuration. It loads the widget on public HTML routes after Drupal has rendered the page.

## Excluding routes

The module skips administrative routes and non-HTML responses automatically. A custom module can disable the widget for additional routes with the `chattybox_load_widget` alter hook:

```php
/**
 * Implements hook_chattybox_load_widget_alter().
 */
function mymodule_chattybox_load_widget_alter(&$should_load, $config, $route_match) {
  if ($route_match->getRouteName() === 'commerce_checkout.form') {
    $should_load = FALSE;
  }
}
```

## Development

The module has no runtime dependencies or build step. If PHP is installed locally, run:

```sh
php -l chattybox.module
php -l src/Form/ChattyBoxSettingsForm.php
```

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
