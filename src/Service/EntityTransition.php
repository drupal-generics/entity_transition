<?php

namespace Drupal\entity_transition\Service;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\entity_transition\EntityTransitionPluginInterface;
use Drupal\entity_transition\EntityTransitionPluginManager;

/**
 * Provides entity transition execution services.
 *
 * @package Drupal\entity_transition\Service
 */
class EntityTransition {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity transition manager service.
   *
   * @var \Drupal\entity_transition\EntityTransitionPluginManager
   */
  protected $entityTransitionManager;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * EntityTransition service constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\entity_transition\EntityTransitionPluginManager $entityTransitionPluginManager
   *   The entity transition plugin manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    EntityTransitionPluginManager $entityTransitionPluginManager,
    LanguageManagerInterface $languageManager
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTransitionManager = $entityTransitionPluginManager;
    $this->languageManager = $languageManager;
  }

  /**
   * Attempts transition on all entities.
   *
   * @return int
   *   Transitioned amount.
   */
  public function transitionAll() {
    $total = 0;
    foreach ($this->entityTransitionManager->getAll() as $transition) {
      $total += $this->doTransition($transition);
    }
    return $total;
  }

  /**
   * Attempts transitions of given type.
   *
   * @param string $type
   *   The type name of transitions.
   *
   * @return int
   *   Transitioned amount.
   */
  public function transitionOfType($type) {
    $total = 0;
    foreach ($this->entityTransitionManager->getByType($type) as $transition) {
      $total += $this->doTransition($transition);
    }
    return $total;
  }

  /**
   * Attempts transitions on given entities and their type.
   *
   * @param string $entityType
   *   The entity type ID.
   * @param null|string $bundle
   *   The bundle of the entity. If NULL all will be taken.
   *
   * @return int
   *   Transitioned amount.
   */
  public function transitionEntitiesOf($entityType, $bundle = NULL) {
    $total = 0;
    foreach ($this->entityTransitionManager->getFor($entityType, $bundle) as $transition) {
      $total += $this->doTransition($transition);
    }
    return $total;
  }

  /**
   * Does the entity transitions.
   *
   * @param \Drupal\entity_transition\EntityTransitionPluginInterface $entityTransition
   *   The transition plugin.
   *
   * @return int
   *   Transitioned amount.
   */
  public function doTransition(EntityTransitionPluginInterface $entityTransition) {
    $entityStorage = $this->entityTypeManager->getStorage($entityTransition->getEntityType());
    $query = $entityStorage->getQuery();

    if ($bundles = $entityTransition->getBundles()) {
      $query->condition('type', $bundles, 'IN');
    }

    $entityTransition->applicableCondition($query);
    $ids = $query->execute();

    // We load the entities and their translations, because the value of the
    // moderation state is set based on the activeLangCode of the entity.
    $entities = $this->getEntities($entityStorage, $ids);
    foreach ($entities as $langcode => $entityList) {
      if ($entityList) {
        foreach ($entityList as $entity) {
          if ($entityTransition->shouldApply($entity)) {
            $entityTransition->apply($entity);
          }
        }
      }
    }

    return count($entities);
  }

  /**
   * Loads the entity and their translations.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entityStorage
   *   The content entity storage object.
   * @param array $ids
   *   The ids that need to be loaded.
   *
   * @return array
   *   An array of entities.
   */
  private function getEntities(EntityStorageInterface $entityStorage, array $ids) {
    $multiLanguageEntities = [];
    $entities = $entityStorage->loadMultiple($ids);
    // The entities that are loaded by an entity query are always the entities
    // in the default language.
    // When setting a moderation state, we need to check if the translations
    // need to be set too, so we need to load the translations as well.
    foreach ($entities as $id => $entity) {
      /** @var \Drupal\Core\Entity\ContentEntityBase $entity */
      $multiLanguageEntities[$entity->language()->getId()][$entity->id()] = $entity;
      $translatedEntities = $this->getTranslatedEntities($entity);

      if ($translatedEntities) {
        $multiLanguageEntities = array_merge($multiLanguageEntities, $translatedEntities);
      }

    }
    return $multiLanguageEntities;
  }

  /**
   * Loads the translations of an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityBase $entity
   *   The original entity.
   *
   * @return array
   *   An array of entities.
   */
  private function getTranslatedEntities(ContentEntityBase $entity) {
    $languages = $this->languageManager->getLanguages();

    $entities = [];
    foreach ($languages as $langcode => $language) {
      if ($entity->hasTranslation($langcode)) {
        $translation = $entity->getTranslation($langcode);
        $entities[$langcode][$translation->id()] = $translation;
      }
    }

    return $entities;
  }

}
