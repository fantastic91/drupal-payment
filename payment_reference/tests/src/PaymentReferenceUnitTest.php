<?php

/**
 * @file
 * Contains \Drupal\payment_reference\Tests\PaymentReferenceUnitTest.
 */

namespace Drupal\payment_reference\Test;

use Drupal\payment_reference\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\Container;

/**
 * Tests \Drupal\payment_reference\PaymentReference.
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'description' => '',
      'group' => 'Payment',
      'name' => '\Drupal\payment_reference\PaymentReference unit test',
    );
  }

  /**
   * Tests lineItemManager().
   */
  public function testLineItemManager() {
    $container = new Container();
    $queue = $this->getMock('\Drupal\payment\QueueInterface');
    $container->set('payment_reference.queue', $queue);
    \Drupal::setContainer($container);
    $this->assertSame($queue, PaymentReference::queue());
  }

}