<?php

namespace Drupal\alipay;

use Payment\Common\PayException;
use Payment\Client\Charge;

/**
 * Class AlipayConfigService.
 *
 * @package Drupal\alipay
 */
class AlipayConfigService  {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Constructs the BookBreadcrumbBuilder.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user account.
   */
  public function __construct() {
    $this->nodeStorage = new Charge();
  }

}
