<?php

/**
 * @file
 * Contains \Drupal\Tests\payment\Unit\Controller\AddPaymentMethodConfigurationTest.
 */

namespace Drupal\Tests\payment\Unit\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandlerInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\payment\Controller\AddPaymentMethodConfiguration;
use Drupal\payment\Entity\PaymentMethodConfigurationInterface;
use Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \Drupal\payment\Controller\AddPaymentMethodConfiguration
 *
 * @group Payment
 */
class AddPaymentMethodConfigurationTest extends UnitTestCase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $currentUser;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityFormBuilder;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The payment method configuration plugin manager.
   *
   * @var \Drupal\payment\Plugin\Payment\MethodConfiguration\PaymentMethodConfigurationManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $paymentMethodConfigurationManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * The string translator.
   *
   * @var \Drupal\Core\StringTranslation\TranslationInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $stringTranslation;

  /**
   * The class under test.
   *
   * @var \Drupal\payment\Controller\AddPaymentMethodConfiguration
   */
  protected $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->currentUser = $this->getMock(AccountInterface::class);

    $this->entityFormBuilder = $this->getMock(EntityFormBuilderInterface::class);

    $this->entityManager = $this->getMock(EntityManagerInterface::class);

    $this->paymentMethodConfigurationManager = $this->getMock(PaymentMethodConfigurationManagerInterface::class);

    $this->requestStack = $this->getMockBuilder(RequestStack::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->stringTranslation = $this->getStringTranslationStub();

    $this->sut = new AddPaymentMethodConfiguration($this->requestStack, $this->stringTranslation, $this->entityManager, $this->paymentMethodConfigurationManager, $this->entityFormBuilder, $this->currentUser);
  }

  /**
   * @covers ::create
   * @covers ::__construct
   */
  function testCreate() {
    $container = $this->getMock(ContainerInterface::class);
    $map = [
      ['current_user', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->currentUser],
      ['entity.form_builder', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityFormBuilder],
      ['entity.manager', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->entityManager],
      ['plugin.manager.payment.method_configuration', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->paymentMethodConfigurationManager],
      ['request_stack', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->requestStack],
      ['string_translation', ContainerInterface::EXCEPTION_ON_INVALID_REFERENCE, $this->stringTranslation],
    ];
    $container->expects($this->any())
      ->method('get')
      ->willReturnMap($map);

    $sut = AddPaymentMethodConfiguration::create($container);
    $this->assertInstanceOf(AddPaymentMethodConfiguration::class, $sut);
  }

  /**
   * @covers ::execute
   */
  public function testExecute() {
    $plugin_id = $this->randomMachineName();

    $payment_method_configuration = $this->getMock(PaymentMethodConfigurationInterface::class);

    $storage_controller = $this->getMock(EntityStorageInterface::class);
    $storage_controller->expects($this->once())
      ->method('create')
      ->willReturn($payment_method_configuration);

    $form = $this->getMock(EntityFormInterface::class);

    $this->entityManager->expects($this->once())
      ->method('getStorage')
      ->with('payment_method_configuration')
      ->willReturn($storage_controller);

    $this->entityFormBuilder->expects($this->once())
      ->method('getForm')
      ->with($payment_method_configuration, 'default')
      ->willReturn($form);

    $this->sut->execute($plugin_id);
  }

  /**
   * @covers ::access
   */
  public function testAccess() {
    $plugin_id = $this->randomMachineName();
    $request = new Request();
    $request->attributes->set('plugin_id', $plugin_id);

    $this->requestStack->expects($this->atLeastOnce())
      ->method('getCurrentRequest')
      ->willReturn($request);

    $access_controller = $this->getMock(EntityAccessControlHandlerInterface::class);
    $access_controller->expects($this->at(0))
      ->method('createAccess')
      ->with($plugin_id, $this->currentUser, [], TRUE)
      ->willReturn(AccessResult::allowed());
    $access_controller->expects($this->at(1))
      ->method('createAccess')
      ->with($plugin_id, $this->currentUser, [], TRUE)
      ->willReturn(AccessResult::forbidden());

    $this->entityManager->expects($this->exactly(2))
      ->method('getAccessControlHandler')
      ->with('payment_method_configuration')
      ->willReturn($access_controller);

    $this->assertTrue($this->sut->access($request)->isAllowed());
    $this->assertFalse($this->sut->access($request)->isAllowed());
  }

  /**
   * @covers ::title
   */
  public function testTitle() {
    $plugin_id = $this->randomMachineName();

    $this->paymentMethodConfigurationManager->expects($this->atLeastOnce())
      ->method('getDefinition')
      ->with($plugin_id)
      ->willReturn([
        'label' => $this->randomMachineName(),
      ]);

    $this->assertInternalType('string', $this->sut->title($plugin_id));
  }

}
