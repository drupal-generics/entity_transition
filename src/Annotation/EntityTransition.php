<?php

namespace Drupal\entity_transition\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for entity transition plugins.
 *
 * @Annotation
 */
class EntityTransition extends Plugin {

  /**
   * The entity type.
   *
   * @var string
   */
  public $entity;

  /**
   * The bundles for which it applies.
   *
   * Provide NULL to make ot apply to all bundles.
   *
   * @var string|array|null
   */
  public $bundles = NULL;

  /**
   * Optional type name for the transition.
   *
   * @var string|null
   */
  public $type = NULL;

  /**
   * The priority of this transition.
   *
   * @var int
   */
  public $priority = 1;

}
