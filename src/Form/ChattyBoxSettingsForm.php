<?php

declare(strict_types=1);

namespace Drupal\chattybox\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure the ChattyBox widget.
 */
final class ChattyBoxSettingsForm extends ConfigFormBase {

  private const DEFAULT_SCRIPT_URL = 'https://chattybox.ai/widget.js';

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'chattybox_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['chattybox.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('chattybox.settings');

    $form['intro'] = [
      '#markup' => '<p>' . $this->t('Connect this Drupal site to a ChattyBox project and load the source-cited chatbot on public pages.') . '</p>',
    ];
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable chatbot'),
      '#default_value' => (bool) $config->get('enabled'),
      '#description' => $this->t('The widget is not loaded on administrative routes or non-HTML responses.'),
    ];
    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public widget API key'),
      '#default_value' => (string) $config->get('api_key'),
      '#maxlength' => 255,
      '#description' => $this->t('Paste the public widget key from the Embed tab of your ChattyBox project. Do not use a management credential.'),
    ];
    $form['script_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Widget script URL'),
      '#default_value' => (string) ($config->get('script_url') ?: self::DEFAULT_SCRIPT_URL),
      '#maxlength' => 2048,
      '#description' => $this->t('Leave the hosted URL unless ChattyBox has provided a preview or self-hosted loader URL.'),
    ];
    $form['locale'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Widget language override'),
      '#default_value' => (string) $config->get('locale'),
      '#maxlength' => 10,
      '#description' => $this->t('Optional. Use a two-letter language code such as en, fr, or de. Leave blank to detect the page or browser language.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $api_key = substr(trim((string) $form_state->getValue('api_key')), 0, 255);
    $form_state->setValue('api_key', $api_key);

    if ($form_state->getValue('enabled') && $api_key === '') {
      $form_state->setErrorByName('api_key', $this->t('Add the public widget API key before enabling ChattyBox.'));
    }

    $script_url = trim((string) $form_state->getValue('script_url'));
    if ($script_url !== '' && !$this->isAllowedUrl($script_url)) {
      $form_state->setErrorByName('script_url', $this->t('The widget script URL must use HTTPS. HTTP is only allowed for localhost development URLs.'));
    }

    $locale = strtolower(str_replace('_', '-', trim((string) $form_state->getValue('locale'))));
    if ($locale !== '' && !preg_match('/^[a-z]{2}(?:-[a-z]{2})?$/', $locale)) {
      $form_state->setErrorByName('locale', $this->t('The language override must be a two-letter code such as en, fr, or de.'));
    }
    $form_state->setValue('locale', $locale);

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $script_url = trim((string) $form_state->getValue('script_url'));
    if ($script_url === '') {
      $script_url = self::DEFAULT_SCRIPT_URL;
    }

    $this->config('chattybox.settings')
      ->setData([
        'enabled' => (bool) $form_state->getValue('enabled'),
        'api_key' => (string) $form_state->getValue('api_key'),
        'script_url' => $script_url,
        'locale' => (string) $form_state->getValue('locale'),
      ])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Only permit secure remote URLs, plus local HTTP URLs for development.
   */
  private function isAllowedUrl(string $url): bool {
    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
      return FALSE;
    }

    $scheme = strtolower((string) $parts['scheme']);
    if ($scheme === 'https') {
      return TRUE;
    }

    $host = strtolower(trim((string) $parts['host'], '[]'));
    return $scheme === 'http' && in_array($host, ['localhost', '127.0.0.1', '::1'], TRUE);
  }

}
