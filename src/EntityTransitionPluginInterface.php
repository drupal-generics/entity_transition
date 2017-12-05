<?php

namespace Drupal\entity_transition;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Interface for the entity transition plugin.
 *
 * @package Drupal\entity_transition
 */
interface EntityTransitionPluginInterface {

  /**
   * Gets the entity type for which it applies.
   *
   * @return string
   *   The entity type ID.
   */
  public function getEntityType();

  /**
   * Gets the bundles of the entity type to which it applies.
   *
   * @return array
   *   The bundles. If empty array then it applies to all.
   */
  public function getBundles();

  /**
   * Determines whether the transition should happen.
   *
   * When applicable query condition is provided this method
   * won't be used in case of bulk transitions.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity for which to determine.
   *
   * @return bool
   *   Whether it should apply.
   */
  public function shouldApply(EntityInterface $entity);

  /**
   * Applies query condition to load entities to transition.
   *
   * Used in case of bulk transitions.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query to add the condition on.
   *
   * @return void|bool
   *   If FALSE returned then all the entities of the given
   *   bundles will be loaded and attempted for transition.
   */
  public function applicableCondition(QueryInterface $query);

  /**
   * Applies the transition on the entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function apply(EntityInterface $entity);

}
