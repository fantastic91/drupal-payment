<?php

/**
 * @file
 * Contains \Drupal\Tests\payment_reference\Unit\Element\PaymentReferenceUnitTest.
 */

namespace Drupal\Tests\payment_reference\Unit\Element;

use Drupal\Component\Utility\Random;
use Drupal\payment_reference\Element\PaymentReference;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @coversDefaultClass \Drupal\payment_reference\Element\PaymentReference
 *
 * @group Payment Reference Field
 */
class PaymentReferenceUnitTest extends UnitTestCase {

  /**
   * The element under test.
   *
   * @var \Drupal\payment_reference\Element\PaymentReference
   */
  protected $element;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $dateFormatter;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $linkGenerator;

  /**
   * The payment method selector manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodSelectorManager;

  /**
   * The payment queue.
   *
   * @var \Drupal\payment\QueueInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentQueue;

  /**
   * The payment storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentStorage;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $renderer;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The string translation service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The url generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $urlGenerator;

  /**
   * {@inheritdoc}
   *
   * @covers ::__construct
   */
  public function setUp() {
    $this->dateFormatter = $this->getMockBuilder('\Drupal\Core\Datetime\DateFormatter')
      ->disableOriginalConstructor()
      ->getMock();

    $this->linkGenerator = $this->getMock('\Drupal\Core\Utility\LinkGeneratorInterface');

    $this->paymentMethodSelectorManager = $this->getMock('\Drupal\payment\Plugin\Payment\MethodSelector\PaymentMethodSelectorManagerInterface');

    $this->paymentQueue = $this->getMock('\Drupal\payment\QueueInterface');

    $this->paymentStorage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');

    $this->renderer = $this->getMock('\Drupal\Core\Render\RendererInterface');

    $this->requestStack = $this->getMockBuilder('\Symfony\Component\HttpFoundation\RequestStack')
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->urlGenerator = $this->getMock('\Drupal\Core\Routing\UrlGeneratorInterface');

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $this->element = new PaymentReference($configuration, $plugin_id, $plugin_definition, $this->requestStack, $this->paymentStorage, $this->stringTranslation, $this->dateFormatter, $this->linkGenerator, $this->renderer, $this->paymentMethodSelectorManager, new Random(), $this->paymentQueue);
  }

  /**
   * @covers ::create
   */
  function testCreate() {
    $entity_manager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $entity_manager->expects($this->once())
      ->method('getStorage')
      ->with('payment')
      ->willReturn($this->paymentStorage);

    $container = $this->getMock('\Symfony\Component\DependencyInjection\ContainerInterface');
    $map = array(
      array('date.formatter', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->dateFormatter),
      array('entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $entity_manager),
      array('link_generator', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->linkGenerator),
      array('payment_reference.queue', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentQueue),
      array('plugin.manager.payment.method_selector', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodSelectorManager),
      array('renderer', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->renderer),
      array('request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack),
      array('string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation),
    );
    $container->expects($this->any())
      ->method('get')
      ->will($this->returnValueMap($map));

    $configuration = [];
    $plugin_id = $this->randomMachineName();
    $plugin_definition = [];

    $form = PaymentReference::create($container, $configuration, $plugin_id, $plugin_definition);
    $this->assertInstanceOf('\Drupal\payment_reference\Element\PaymentReference', $form);
  }

  /**
   * @covers ::getPaymentQueue
   */
  public function testGetPaymentQueue() {
    $method = new \ReflectionMethod($this->element, 'getPaymentQueue');
    $method->setAccessible(TRUE);
    $this->assertSame($this->paymentQueue, $method->invoke($this->element));
  }

}