<?php

namespace Drupal\entity_transition;

use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for the entity transition plugin.
 *
 * @package Drupal\entity_transition
 */
abstract class EntityTransitionPluginBase extends PluginBase implements EntityTransitionPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->pluginDefinition['entity'];
  }

  /**
   * {@inheritdoc}
   */
  public function getBundles() {
    if (empty($this->pluginDefinition['bundles'])) {
      return [];
    }
    elseif (is_array($this->pluginDefinition['bundles'])) {
      return $this->pluginDefinition['bundles'];
    }

    return [$this->pluginDefinition['bundles']];
  }

  /**
   * {@inheritdoc}
   */
  public function applicableCondition(QueryInterface $query) {
    return FALSE;
  }

}
