<?php

/**
 * @file
 * Contains \Drupal\payment\Entity\Payment\PaymentListBuilder.
 */

namespace Drupal\payment\Entity\Payment;

use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Lists payment entities.
 */
class PaymentListBuilder extends EntityListBuilder {

  /**
   * The currency storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $currencyStorage;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs a new class instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $payment_storage
   *   The payment storage.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request.
   * @param \Drupal\Core\DateTime\DateFormatter $date_formatter
   *   The date formatter.
   * @param \Drupal\Core\Entity\EntityStorageInterface $currency_storage
   *   The currency storage.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $payment_storage, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, RequestStack $request_stack, DateFormatter $date_formatter, EntityStorageInterface $currency_storage) {
    parent::__construct($entity_type, $payment_storage);
    $this->currencyStorage = $currency_storage;
    $this->dateFormatter = $date_formatter;
    $this->moduleHandler = $module_handler;
    $this->requestStack = $request_stack;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $container->get('entity.manager');

    return new static($entity_type, $entity_manager->getStorage('payment'), $container->get('string_translation'), $container->get('module_handler'), $container->get('request_stack'), $container->get('date.formatter'), $entity_manager->getStorage('currency'));
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery();
    $header = $this->buildHeader();
    $query->tableSort($header);

    return $query
      ->pager($this->limit)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['table']['#empty'] = $this->t('There are no payments yet.');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['updated'] = [
      'data' => $this->t('Last updated'),
      'field' => 'changed',
      'sort' => 'DESC',
      'specifier' => 'changed',
    ];
    $header['status'] = $this->t('Status');
    $header['amount'] =$this-> t('Amount');
    $header['payment_method'] = array(
      'data' => $this->t('Payment method'),
      'class' => array(RESPONSIVE_PRIORITY_LOW),
    );
    $header['owner'] = array(
      'data' => $this->t('Payer'),
      'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
    );
    $header['operations'] = $this->t('Operations');

    return $header;
  }

  /**
   * {@inheritdnoc}
   */
  public function buildRow(EntityInterface $payment) {
    /** @var \Drupal\payment\Entity\PaymentInterface $payment */
    $row['data']['updated'] = $this->dateFormatter->format($payment->getChangedTime());

    $status_definition = $payment->getPaymentStatus()->getPluginDefinition();
    $row['data']['status'] = $status_definition['label'];

    /** @var \Drupal\currency\Entity\CurrencyInterface $currency */
    $currency = $this->currencyStorage->load($payment->getCurrencyCode());
    if (!$currency) {
      $currency = $this->currencyStorage->load('XXX');
    }
    $row['data']['amount'] = $currency->formatAmount($payment->getAmount());

    $row['data']['payment_method'] = $payment->getPaymentMethod() ? $payment->getPaymentMethod()->getPluginLabel() : $this->t('Unavailable');

      $row['data']['owner']['data'] = array(
        '#theme' => 'username',
        '#account' => $payment->getOwner(),
      );

    $operations = $this->buildOperations($payment);
    $row['data']['operations']['data'] = $operations;

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $destination = $this->requestStack->getCurrentRequest()->attributes->get('_system_path');

    $operations = parent::getDefaultOperations($entity);
    foreach ($operations as &$operation) {
      $operation['query'] = array(
        'destination' => $destination,
      );
    }

    if ($entity->access('view')) {
      $operations['view'] = array(
        'title' => $this->t('View'),
        'weight' => -10,
        'url' => $entity->urlInfo(),
      );
    }
    if ($entity->access('update_status')) {
      $operations['update_status'] = array(
        'title' => $this->t('Update status'),
        'attributes' => array(
          'class' => array('use-ajax'),
          'data-accepts' => 'application/vnd.drupal-modal',
        ),
        'query' => array(
          'destination' => $destination,
        ),
        'url' => $entity->urlInfo('update-status-form'),
      );
    }
    if ($entity->access('capture')) {
      $operations['capture'] = array(
          'title' => $this->t('Capture'),
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
          ),
          'query' => array(
            'destination' => $destination,
          ),
          'url' => $entity->urlInfo('capture-form'),
        );
    }
    if ($entity->access('refund')) {
      $operations['refund'] = array(
          'title' => $this->t('Refund'),
          'attributes' => array(
            'class' => array('use-ajax'),
            'data-accepts' => 'application/vnd.drupal-modal',
          ),
          'query' => array(
            'destination' => $destination,
          ),
          'url' => $entity->urlInfo('refund-form'),
        );
    }
    if ($entity->access('complete')) {
      $operations['resume'] = array(
        'title' => $this->t('Complete'),
        'url' => $entity->urlInfo('complete'),
      );
    }

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOperations(EntityInterface $entity) {
    $build = parent::buildOperations($entity);
    // @todo Remove this when https://drupal.org/node/2253257 is fixed.
    $build['#attached'] = array(
      'library' => array('core/drupal.ajax'),
    );

    return $build;
  }
}