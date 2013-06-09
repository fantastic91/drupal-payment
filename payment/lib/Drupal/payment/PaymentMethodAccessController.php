<?php

/**
 * @file
 * Definition of Drupal\payment\PaymentMethodAccessController.
 */

namespace Drupal\payment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessController;
use Drupal\user\Plugin\Core\Entity\User;

/**
 * Defines the default list controller for ConfigEntity objects.
 */
class PaymentMethodAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, User $account) {
    if ($operation == 'create' && $entity->controller) {
      return user_access('payment.payment_method.create.' . $entity->controller->name, $account);
    }
    elseif ($operation == 'enable') {
      return !$entity->status() && $entity->access('update', $account);
    }
    elseif ($operation == 'disable') {
      return $entity->status() && $entity->access('update', $account);
    }
    elseif ($operation == 'clone') {
      return $entity->access('create', $account) && $entity->access('view', $account);
    }
    else {
      $permission = 'payment.payment_method.' . $operation;
      return user_access($permission . '.any', $account) || user_access($permission . '.own', $account) && $entity->uid == $account->uid;
    }
  }
}
