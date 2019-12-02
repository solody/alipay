<?php

namespace Drupal\alipay;

use Drupal\commerce_checkout_api\SupportHeadlessPaymentInterface;

interface AlipayGatewayInterface extends SupportHeadlessPaymentInterface {

  const CLIENT_TYPE_WEBSITE = 'website';
  const CLIENT_TYPE_FACE_TO_FACE = 'face_to_face';
  const CLIENT_TYPE_NATIVE_APP = 'native_app';
  const CLIENT_TYPE_H5 = 'h5';
}
