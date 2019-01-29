<?php

namespace Drupal\alipay\Plugin\TransferGateway;

use Drupal\commerce_price\Price;
use Drupal\Component\Utility\NestedArray;
use Drupal\Console\Bootstrap\Drupal;
use Drupal\Core\Form\FormStateInterface;
use Drupal\account\Entity\WithdrawInterface;
use Drupal\account\Plugin\TransferGatewayBase;
use Drupal\entity\BundleFieldDefinition;
use Omnipay\Alipay\AopAppGateway;
use Omnipay\Alipay\Requests\AopTransferToAccountRequest;
use Omnipay\Alipay\Responses\AopTransferToAccountResponse;
use Omnipay\Omnipay;

/**
 * @TransferGateway(
 *   id = "alipay",
 *   label = @Translation("Alipay Transfer")
 * )
 */
class Alipay extends TransferGatewayBase {
  /**
   * @inheritdoc
   */
  public function buildFieldDefinitions() {
    $fields['alipay_account'] = BundleFieldDefinition::create('string')
      ->setLabel(t('Account'))
      ->setDescription(t('account of the transfer target.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -9,
      ]);

    $fields['alipay_name'] = BundleFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Real name of the transfer target.'))
      ->setDisplayOptions('view', [
        'label' => 'inline',
        'type' => 'string',
        'weight' => -9,
      ]);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('App ID'),
      '#description' => $this->t('APP ID'),
      '#default_value' => isset($this->configuration['app_id']) ? $this->configuration['app_id'] : '',
      '#required' => TRUE,
    ];

    $form['app_private_key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Private key path'),
      '#description' => $this->t('The app private key'),
      '#default_value' => isset($this->configuration['app_private_key_path']) ? $this->configuration['app_private_key_path'] : ''
    ];

    $form['alipay_public_key_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public key path'),
      '#description' => $this->t('The alipay public key'),
      '#default_value' => isset($this->configuration['alipay_public_key_path']) ? $this->configuration['alipay_public_key_path'] : ''
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['app_id'] = $values['app_id'];
      $this->configuration['app_private_key_path'] = $values['app_private_key_path'];
      $this->configuration['alipay_public_key_path'] = $values['alipay_public_key_path'];
    }
  }

  /**
   * 转账
   *
   * @param WithdrawInterface $withdraw
   * @return bool
   * @throws \Exception
   */
  public function transfer(WithdrawInterface $withdraw) {
    // return true; // 直接成功，方便测试
    $transfer = $this->getSDK();

    /** @var AopTransferToAccountRequest $request */
    $request = $transfer->transfer();


    $config = \Drupal::config('alipay.settings');

    // 计算手续费
    $fee = $withdraw->getAmount()->multiply((string)((float)$config->get('transfer_poundage.percentage') / 100));
    $fee = new Price((string)(ceil($fee->getNumber() * 100) / 100), $fee->getCurrencyCode());

    $amount = $withdraw->getAmount();
    $remark = $withdraw->getName();
    if ($config->get('transfer_poundage.enable') && !$fee->isZero()) {
      $amount = $amount->subtract($fee);
      $remark .= '(已扣除手续费'.$this->getCurrencyFormatter()->format($fee->getNumber(), $fee->getCurrencyCode()).')';
    }

    $request->setBizContent([
      'out_biz_no'      => $withdraw->id() . '-' . time(),
      'payee_type' => 'ALIPAY_LOGONID',
      'payee_account' => $withdraw->getTransferMethod()->get('alipay_account')->value,
      'amount' => round($amount->getNumber(), 2),
      'payer_show_name' => \Drupal::config('system.site')->get('name') . '：' . $withdraw->getName(),
      'payee_real_name' => $withdraw->getTransferMethod()->get('alipay_name')->value,
      'remark' => $remark
    ]);

    /** @var AopTransferToAccountResponse $response */
    $response = $request->send();

    if (!$response->isSuccessful()) {
      \Drupal::logger('alipay')->notice(var_export($response->getData(), true));
      throw new \Exception(
        $response->data('alipay_fund_trans_toaccount_transfer_response.code'). ' '.
        $response->data('alipay_fund_trans_toaccount_transfer_response.msg'). ' '.
        $response->data('alipay_fund_trans_toaccount_transfer_response.sub_code'). ' '.
        $response->data('alipay_fund_trans_toaccount_transfer_response.sub_msg'));
    } else {
      $order_id = $response->data('alipay_fund_trans_toaccount_transfer_response.order_id');
      $withdraw->setTransactionNumber($order_id);
    }

    return $response->isSuccessful();
  }

  /**
   * @return \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface
   */
  private function getCurrencyFormatter() {
    return \Drupal::getContainer()->get('commerce_price.currency_formatter');
  }

  /**
   * @return AopAppGateway
   */
  private function getSDK() {
    /** @var AopAppGateway $gateway */
    $gateway = Omnipay::create('Alipay_AopApp');
    $gateway->setSignType('RSA2'); //RSA/RSA2

    $gateway->setAppId($this->getConfiguration()['app_id']);
    $gateway->setPrivateKey($this->getConfiguration()['app_private_key_path']);
    $gateway->setAlipayPublicKey($this->getConfiguration()['alipay_public_key_path']);

    return $gateway;
  }
}
