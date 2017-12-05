<?php

namespace Drupal\entity_transition;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for the entity transition plugins.
 *
 * @package Drupal\entity_transition
 */
class EntityTransitionPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/Transition',
      $namespaces,
      $module_handler,
      'Drupal\entity_transition\EntityTransitionPluginInterface',
      'Drupal\entity_transition\Annotation\EntityTransition'
    );

    $this->setCacheBackend($cache_backend, 'entity_transitions');
  }

  /**
   * Get all entity transition plugins.
   *
   * @return \Drupal\entity_transition\EntityTransitionPluginInterface[]
   *   The plugins.
   */
  public function getAll() {
    return $this->createFromDefinitions($this->getDefinitions());
  }

  /**
   * Get entity transitions by type.
   *
   * @param string $type
   *   Type name of the transitions.
   *
   * @return \Drupal\entity_transition\EntityTransitionPluginInterface[]
   *   Entity transition plugins.
   */
  public function getByType($type) {
    $entityTransitions = [];

    foreach ($this->getDefinitions() as $id => $definition) {
      if ($definition['type'] == $type) {
        $entityTransitions[$id] = $definition;
      }
    }

    return $this->createFromDefinitions($entityTransitions);
  }

  /**
   * Get entity transitions by entity and bundle.
   *
   * @param string $entity
   *   The entity ID.
   * @param string|null $bundle
   *   The bundle of the entity.
   *
   * @return \Drupal\entity_transition\EntityTransitionPluginInterface[]
   *   Entity transition plugins.
   */
  public function getFor($entity, $bundle = NULL) {
    $entityTransitions = [];

    foreach ($this->getDefinitions() as $id => $definition) {
      if ($definition['entity'] != $entity) {
        continue;
      }

      if ($bundle || empty($definition['bundle'])) {
        if (is_string($definition['bundle'])) {
          $definition['bundle'] = [$definition['bundle']];
        }
        if ($definition['entity'] != $entity || !in_array($bundle, $definition['bundle'])) {
          continue;
        }
      }

      $entityTransitions[$id] = $definition;
    }

    return $this->createFromDefinitions($entityTransitions);
  }

  /**
   * Creates entity transitions by definitions.
   *
   * @param array $definitions
   *   The definitions.
   *
   * @return \Drupal\entity_transition\EntityTransitionPluginInterface[]
   *   Entity transition plugins.
   */
  protected function createFromDefinitions(array $definitions) {
    $this->sortDefinitions($definitions);

    foreach ($definitions as $id => $entityTransition) {
      $definitions[$id] = $this->createInstance($id);
    }

    return $definitions;
  }

  /**
   * Sorts definitions by priority.
   *
   * @param array $definitions
   *   The definitions.
   */
  protected function sortDefinitions(array &$definitions) {
    // Sort the definitions after priority.
    uasort($definitions, function ($a, $b) {
      return $a['priority'] <=> $b['priority'];
    });
  }

}
