(function (Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.chattybox = {
    attach: function (context) {
      if (context !== document) {
        return;
      }

      var settings = drupalSettings.chattybox || {};
      if (!settings.apiKey || !settings.scriptUrl) {
        return;
      }

      if (document.querySelector('script[data-chattybox-widget="true"]')) {
        return;
      }

      var script = document.createElement('script');
      script.async = true;
      script.src = settings.scriptUrl;
      script.setAttribute('data-api-key', settings.apiKey);
      script.setAttribute('data-api-url', settings.apiUrl || '');
      script.setAttribute('data-chattybox-widget', 'true');

      if (settings.locale) {
        script.setAttribute('data-locale', settings.locale);
      }

      (document.head || document.documentElement).appendChild(script);
    }
  };
}(Drupal, drupalSettings));
