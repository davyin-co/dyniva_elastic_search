<?php

namespace Drupal\dyniva_elastic_search\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\dyniva_elastic_search\SearchHelper;

/**
 * Custom exception subscriber.
 */
class ElasticConnectorSubscriber implements EventSubscriberInterface {

  /**
   * Handles errors for this subscriber.
   *
   * @param \Drupal\elasticsearch_connector\Event\PrepareMappingEvent $event
   *   The event to process.
   */
  public function onPrepareMapping(\Drupal\elasticsearch_connector\Event\PrepareMappingEvent $event) {
    if($event->getMappingType() == 'text') {
      $config = \Drupal::config('dyniva_elastic_search.settings');
      $index_analyzer = $config->get('text_index_analyzer') ?: 'ik_max_word';
      $search_analyzer = $config->get('text_search_analyzer') ?: 'ik_smart';
      $mappingConfig = $event->getMappingConfig();
      $mappingConfig += [
        "analyzer" => $index_analyzer,
        "search_analyzer" => $search_analyzer,
      ];
      $event->setMappingConfig($mappingConfig);
    }
  }
  /**
   * Handles errors for this subscriber.
   *
   * @param \Drupal\elasticsearch_connector\Event\PrepareSearchQueryEvent $event
   *   The event to process.
   */
  public function onPrepareQuery(\Drupal\elasticsearch_connector\Event\PrepareSearchQueryEvent $event) {
    $search_query = $event->getElasticSearchQuery();
    if(!empty($search_query['query_search_string']['query_string']['query'])) {
      $search_string = $search_query['query_search_string']['query_string']['query'];
      $search_string = trim($search_string);
      $search_string = trim($search_string,'~');
      SearchHelper::addQueryLog($search_string, REQUEST_TIME);
    }
  }

  /**
   * Handles errors for this subscriber.
   *
   * @param \Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent $event
   *   The event to process.
   */
  public function onBuildQuery(\Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent $event)
  {
    $params = $event->getElasticSearchParams();


    $minimum_should_match = \Drupal::config('dyniva_elastic_search.settings')->get('minimum_should_match');
    if(!empty($minimum_should_match)) {
      $params['body']['query']['bool']['minimum_should_match'] = $minimum_should_match;
    }
    // if ($params['type'] == 'escontent') {
    //   $factor = \Drupal::state()->get('ccms_elastic_search.viewcount_score_factor', [
    //     'factor' => 1,
    //     'modifier' => 'log2p', // none log log1p log2p ln ln1p ln2p square sqrt reciprocal
    //     'boost_mode' => 'multiply', // multiply sum min max replace
    //   ]);
    //   $params['body']['query'] = [
    //     'function_score' => [
    //       'query' => $params['body']['query'],
    //       'field_value_factor' => [
    //         'field' => 'ccms_viewcount',
    //         'modifier' => $factor['modifier'],
    //         'factor' => $factor['factor']
    //       ],
    //       'boost_mode' => $factor['boost_mode'],
    //     ]
    //   ];
    //   $event->setElasticSearchParams($params);
    // }
  }
  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];
    $events[\Drupal\elasticsearch_connector\Event\PrepareMappingEvent::PREPARE_MAPPING] = 'onPrepareMapping';
    $events[\Drupal\elasticsearch_connector\Event\PrepareSearchQueryEvent::PREPARE_QUERY] = 'onPrepareQuery';
    $events[\Drupal\elasticsearch_connector\Event\BuildSearchParamsEvent::BUILD_QUERY] = 'onBuildQuery';
    return $events;
  }

}
