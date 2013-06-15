<?php

/**
 * Contains \Drupal\payment\plugin\payment\PaymentMethod\PaymentMethodInterface.
 */

namespace Drupal\payment\plugin\payment\PaymentMethod;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\payment\PaymentProcessingInterface;
use Drupal\payment\Plugin\Core\entity\Payment;

/**
 * A payment method plugin (the logic behind a payment method entity).
 *
 * @see \Drupal\payment\Plugin\Core\entity\PaymentMethod
 */
interface PaymentMethodInterface extends PaymentProcessingInterface, PluginInspectionInterface {

  /**
   * Sets the plugin configuration.
   *
   * @param array $configuration
   */
  public function setConfiguration(array $configuration);

  /**
   * Gets the plugin configuration.
   *
   * @return array
   *  The data is not allowed to contain objects.
   */
  public function getConfiguration();

  /**
   * Returns the form elements to configure payment methods.
   *
   * $form_state['payment_method'] contains the payment method that is added or
   * edited. All method-specific information should be added to it during
   * element validation. The payment method will be saved automatically.
   *
   * @param array $form
   * @param array $form_state
   *
   * @return array
   *   A render array.
   */
  public function paymentMethodFormElements(array $form, array &$form_state);
}