<?php

namespace Drupal\dyniva_elastic_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\dyniva_elastic_search\SearchHelper;

class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dyniva_elastic_search_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    $config = $this->config('dyniva_elastic_search.settings');

    $form['query'] = [
      '#type' => 'details',
      '#open' => 'true',
      '#title' => $this->t('Query'),
    ];

    $form['query']['minimum_should_match'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Minimum should match'),
      '#default_value' => $config->get('minimum_should_match') ?: '3<75%',
      '#description' => $this->t('Minimum should match paramater in query function: https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html.'),
    ];
    $form['query']['text_index_analyzer'] = [
      '#type' => 'select',
      '#title' => $this->t('Fulltext index override'),
      '#default_value' => $config->get('text_index_analyzer') ?: 'ik_max_word',
      '#options' => [
        'ik_smart' => 'ik_smart',
        'ik_max_word' => 'ik_max_word',
        'standard' => 'standard',
        'simple' => 'simple',
        'whitespace' => 'whitespace',
      ],
    ];
    $form['query']['text_search_analyzer'] = [
      '#type' => 'select',
      '#title' => $this->t('Fulltext analyzer override'),
      '#default_value' => $config->get('text_search_analyzer') ?: 'ik_smart',
      '#options' => [
        'ik_smart' => 'ik_smart',
        'ik_max_word' => 'ik_max_word',
        'standard' => 'standard',
        'simple' => 'simple',
        'whitespace' => 'whitespace',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('dyniva_elastic_search.settings');
    $config->set('minimum_should_match', $form_state->getValue('minimum_should_match'));
    $config->set('text_index_analyzer', $form_state->getValue('text_index_analyzer'));
    $config->set('text_search_analyzer', $form_state->getValue('text_search_analyzer'));
    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'dyniva_elastic_search.settings',
    ];
  }
}
