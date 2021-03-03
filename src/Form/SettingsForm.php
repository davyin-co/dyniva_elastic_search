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

    $form['hot_words'] = [
      '#type' => 'details',
      '#open' => 'true',
      '#title' => $this->t('Hot words'),
    ];

    $form['hot_words']['hot_words_count'] = array(
      '#type' => 'number',
      '#title' => $this->t('Hot Words Minimum Search Count'),
      '#default_value' => $config->get('hot_words_count')?:0,
    );
    $form['hot_words']['hot_words_interval'] = array(
      '#type' => 'number',
      '#title' => $this->t('Hot Words Search Time Range'),
      '#description' => $this->t('Get hot words form search log which in this time range, 0 for unlimited.'),
      '#field_suffix' => $this->t('Days'),
      '#default_value' => $config->get('hot_words_interval')?:0,
    );
    $form['hot_words']['hot_words_cache_enabled'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Cache Hot Words'),
      '#description' => $this->t('Cache hot words in drupal cache.'),
      '#default_value' => $config->get('hot_words_cache_enabled')?:false,
    );
    $form['hot_words']['hot_words_cache_interval'] = array(
      '#type' => 'number',
      '#title' => $this->t('Hot Words Cache Interval'),
      '#field_suffix' => $this->t('Minutes'),
      '#default_value' => $config->get('hot_words_cache_interval')?:5,
    );
    $form['hot_words']['hot_words_black_list'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Hot Words black list'),
      '#default_value' => $config->get('hot_words_black_list')?implode("\n",$config->get('hot_words_black_list')):"",
    );

    $form['query'] = [
      '#type' => 'details',
      '#open' => 'true',
      '#title' => $this->t('Query'),
    ];

    $form['query']['minimum_should_match'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Minimum should match'),
      '#default_value' => $config->get('minimum_should_match') ?: '3<75%',
      '#description' => $this->t('Minimum should match paramater in query function: https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-minimum-should-match.html.'),
    );
    $form['query']['text_index_analyzer'] = array(
      '#type' => 'select',
      '#title' => $this->t('Text index analyzer'),
      '#default_value' => $config->get('text_index_analyzer') ?: 'ik_max_word',
      '#options' => [
        'ik_smart' => 'ik_smart',
        'ik_max_word' => 'ik_max_word',
        'standard' => 'standard',
        'simple' => 'simple',
        'whitespace' => 'whitespace',
      ],
    );
    $form['query']['text_search_analyzer'] = array(
      '#type' => 'select',
      '#title' => $this->t('Text search analyzer'),
      '#default_value' => $config->get('text_search_analyzer') ?: 'ik_smart',
      '#options' => [
        'ik_smart' => 'ik_smart',
        'ik_max_word' => 'ik_max_word',
        'standard' => 'standard',
        'simple' => 'simple',
        'whitespace' => 'whitespace',
      ],
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $black_list = explode("\n", $form_state->getValue('hot_words_black_list'));
    $config = $this->config('dyniva_elastic_search.settings');
    $config->set('hot_words_count', $form_state->getValue('hot_words_count'));
    $config->set('hot_words_interval', $form_state->getValue('hot_words_interval'));
    $config->set('hot_words_cache_enabled', $form_state->getValue('hot_words_cache_enabled'));
    $config->set('hot_words_cache_interval', $form_state->getValue('hot_words_cache_interval'));
    $config->set('hot_words_black_list', $black_list);
    $config->set('minimum_should_match', $form_state->getValue('minimum_should_match'));
    $config->set('text_index_analyzer', $form_state->getValue('text_index_analyzer'));
    $config->set('text_search_analyzer', $form_state->getValue('text_search_analyzer'));
    $config->save();
    return parent::submitForm($form, $form_state);
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
