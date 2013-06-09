<?php

/**
 * @file
 * Contains class \Drupal\payment\Tests\PaymentMethodAccessControllerTest.
 */

namespace Drupal\payment\Tests;

use Drupal\payment\Tests\AccessibleInterfaceWebTestBase;
use Drupal\payment\Tests\Utility;

/**
 * Tests \Drupal\payment\PaymentMethodAccessController.
 */
class PaymentMethodAccessControllerTest extends AccessibleInterfaceWebTestBase {

  public static $modules = array('payment');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => '\Drupal\payment\PaymentMethodAccessController',
      'group' => 'Payment',
    );
  }

  /**
   * Tests access control.
   */
  function testAccessControl() {
    $authenticated = $this->drupalCreateUser();

    // Create a new payment method.
    // @todo Test this with a controller that actually has create permissions.
    // $this->assertDataAccess(Utility::createPaymentMethod(0), 'a payment method', 'create', $authenticated, array('payment.payment_method.create.PaymentMethodControllerUnavailable'));

    // Update a payment method that belongs to user 1.
    $this->assertDataAccess(Utility::createPaymentMethod(1), 'a payment method', 'update', $authenticated, array('payment.payment_method.update.any'));

    // Update a payment method that belongs to user 2.
    $this->assertDataAccess(Utility::createPaymentMethod($authenticated->uid), 'a payment method', 'update', $authenticated, array('payment.payment_method.update.own'));

    // Delete a payment method that belongs to user 1.
    $this->assertDataAccess(Utility::createPaymentMethod(1), 'a payment method', 'delete', $authenticated, array('payment.payment_method.delete.any'));

    // Delete a payment method that belongs to user 2.
    $this->assertDataAccess(Utility::createPaymentMethod($authenticated->uid), 'a payment method', 'delete', $authenticated, array('payment.payment_method.delete.own'));

    // Enable an enabled payment method that belongs to user 1.
    $payment_method = Utility::createPaymentMethod(1);
    $this->assertDataAccess($payment_method, 'an enabled payment method', 'enable', $authenticated, array('payment.payment_method.update.any'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Enable an enabled payment method that belongs to user 2.
    $payment_method = Utility::createPaymentMethod($authenticated->uid);
    $this->assertDataAccess($payment_method, 'an enabled payment method', 'enable', $authenticated, array('payment.payment_method.update.own'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Enable a disabled payment method that belongs to user 1.
    $payment_method = Utility::createPaymentMethod(1);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'enable', $authenticated, array('payment.payment_method.update.any'));

    // Enable a disabled payment method that belongs to user 2.
    $payment_method = Utility::createPaymentMethod($authenticated->uid);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'enable', $authenticated, array('payment.payment_method.update.own'));

    // Disable a disabled payment method that belongs to user 1.
    $payment_method = Utility::createPaymentMethod(1);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.any'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Disable a disabled payment method that belongs to user 2.
    $payment_method = Utility::createPaymentMethod($authenticated->uid);
    $payment_method->disable();
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.own'), array(
      'root' => FALSE,
      'authenticated_with_permissions' => FALSE,
    ));

    // Disable an enabled payment method that belongs to user 1.
    $payment_method = Utility::createPaymentMethod(1);
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.any'));

    // Enable am enabled payment method that belongs to user 2.
    $payment_method = Utility::createPaymentMethod($authenticated->uid);
    $this->assertDataAccess($payment_method, 'a disabled payment method', 'disable', $authenticated, array('payment.payment_method.update.own'));

    // Clone a payment method that belongs to user 1.
    // @todo Test this with a controller that actually has create permissions.
    // $this->assertDataAccess(Utility::createPaymentMethod(1), 'a payment method', 'clone', $authenticated, array('payment.payment_method.view.any', 'payment.payment_method.create.Drupal\\payment\\PaymentMethodControllerUnavailable'));

    // Clone a payment method that belongs to user 2.
    // @todo Test this with a controller that actually has create permissions.
    // $this->assertDataAccess(Utility::createPaymentMethod($authenticated->uid), 'a payment method', 'clone', $authenticated, array('payment.payment_method.view.own', 'payment.payment_method.create.Drupal\\payment\\PaymentMethodControllerUnavailable'));

    // View a payment method that belongs to user 1.
    $this->assertDataAccess(Utility::createPaymentMethod(1), 'a payment method', 'view', $authenticated, array('payment.payment_method.view.any'));

    // View a payment method that belongs to user 2.
    $this->assertDataAccess(Utility::createPaymentMethod($authenticated->uid), 'a payment method', 'view', $authenticated, array('payment.payment_method.view.own'));
  }
}
