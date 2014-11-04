<?php

/**
 * @file
 * Definition of Drupal\payment\Entity\PaymentMethodConfiguration.
 */

namespace Drupal\payment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ThirdPartySettingsTrait;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\user\UserInterface;
use Drupal\user\UserStorageInterface;

/**
 * Defines a payment method configuration entity.
 *
 * @ConfigEntityType(
 *   bundle_label = @Translation("Payment method type"),
 *   handlers = {
 *     "access" = "Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationAccessControlHandler",
 *     "form" = {
 *       "default" = "Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationForm",
 *       "delete" = "Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationDeleteForm"
 *     },
 *     "list_builder" = "Drupal\payment\Entity\PaymentMethodConfiguration\PaymentMethodConfigurationListBuilder",
 *     "storage" = "\Drupal\Core\Config\Entity\ConfigEntityStorage",
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "pluginId",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   id = "payment_method_configuration",
 *   label = @Translation("Payment method configuration"),
 *   links = {
 *     "enable" = "payment.payment_method_configuration.enable",
 *     "disable" = "payment.payment_method_configuration.disable",
 *     "canonical" = "payment.payment_method_configuration.edit",
 *     "create-form" = "payment.payment_method_configuration.select",
 *     "edit-form" = "payment.payment_method_configuration.edit",
 *     "delete-form" = "payment.payment_method_configuration.delete",
 *     "duplicate-form" = "payment.payment_method_configuration.duplicate"
 *   }
 * )
 */
class PaymentMethodConfiguration extends ConfigEntityBase implements PaymentMethodConfigurationInterface {

  use ThirdPartySettingsTrait;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The entity's unique machine name.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable label.
   *
   * @var string
   */
  protected $label;

  /**
   * The UID of the user this payment method belongs to.
   *
   * @var integer
   */
  protected $ownerId;

  /**
   * The configuration, which comes from the entity's payment method plugin.
   *
   * @var array
   */
  protected $pluginConfiguration = [];

  /**
   * The bundle, which is the ID of the entity's payment method plugin.
   *
   * @var integer
   */
  protected $pluginId;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The entity's UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * {@inheritdoc}
   */
  public function bundle() {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function toArray() {
    $properties = parent::toArray();
    $properties['id'] = $this->id();
    $properties['label'] = $this->label();
    $properties['ownerId'] = $this->getOwnerId();
    $properties['pluginId'] = $this->bundle();
    $properties['pluginConfiguration'] = $this->getPluginConfiguration();

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($id) {
    $this->ownerId = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $user) {
    $this->ownerId = $user->id();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->ownerId;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->getUserStorage()->load($this->getOwnerId());
  }

  /**
   * {@inheritdoc}
   */
  public function setId($id) {
    $this->id = $id;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setUuid($uuid) {
    $this->uuid = $uuid;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setLabel($label) {
    $this->label = $label;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginConfiguration(array $configuration) {
    $this->pluginConfiguration = $configuration;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginConfiguration() {
    return $this->pluginConfiguration;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    $values += [
      'ownerId' => (int) \Drupal::currentUser()->id(),
    ];
  }

  /**
   * Sets the entity manager.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *
   * @return $this
   */
  public function setEntityManager(EntityManagerInterface $entity_manager) {
    $this->entityManager = $entity_manager;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function entityManager() {
    if (!$this->entityManager) {
      $this->entityManager = parent::entityManager();
    }

    return $this->entityManager;
  }

  /**
   * Sets the typed config.
   *
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_config_manager
   *
   * @return $this
   */
  public function setTypedConfig(TypedConfigManagerInterface $typed_config_manager) {
    $this->typedConfigManager = $typed_config_manager;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function getTypedConfig() {
    if (!$this->typedConfigManager) {
      $this->typedConfigManager = parent::getTypedConfig();
    }

    return $this->typedConfigManager;
  }

  /**
   * Sets the user storage.
   *
   * @param \Drupal\user\UserStorageInterface $user_storage
   *
   * @return $this
   */
  public function setUserStorage(UserStorageInterface $user_storage) {
    $this->userStorage = $user_storage;

    return $this;
  }

  /**
   * Gets the user storage.
   *
   * @return \Drupal\user\UserStorageInterface
   */
  protected function getUserStorage() {
    if (!$this->userStorage) {
      $this->userStorage = $this->entityManager()->getStorage('currency');
    }

    return $this->userStorage;
  }

}