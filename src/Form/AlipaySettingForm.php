<?php

namespace Drupal\alipay\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Payment\Common\PayException;
use Payment\Client\Charge;

/**
 * Class AlipaySettingForm.
 *
 * @package Drupal\alipay\Form
 */
class AlipaySettingForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'alipay.alipaysetting',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'alipay_setting_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('alipay.alipaysetting');

    //wechat payment
    $form['payment']['wechat'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Use Wechat Payment'),
        '#default_value' => $config->get('wechat.wechat'),
        '#tree' => FALSE,
        '#description' => $this->t('启用微信支付.')
    );
    $form['payment']['wechat_settings'] = array(
        '#type' => 'container',
        '#states' => array(
            'invisible' => array(
                'input[name="wechat"]' => array('checked' => FALSE),
            ),
        ),
    );
    $form['payment']['wechat_settings']['use_sandbox'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sandbox'),
      '#default_value' => $config->get('wechat.use_sandbox'),
      '#description' => $this->t('是否开启沙箱模式.'),
    );

    //alipay payment
    $form['payment']['alipay'] = array(
        '#type' => 'checkbox',
        '#title' => $this->t('Use Alipay Payment'),
        '#default_value' => $config->get('alipay.alipay'),
        '#tree' => FALSE,
        '#description' => $this->t('启用支付宝支付.')
    );
    $form['payment']['alipay_settings'] = array(
        '#type' => 'container',
        '#states' => array(
            'invisible' => array(
                'input[name="alipay"]' => array('checked' => FALSE),
            ),
        ),
    );
    $form['payment']['alipay_settings']['use_sandbox'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Sandbox'),
      '#default_value' => $config->get('alipay.use_sandbox'),
      '#description' => $this->t('是否开启沙箱模式.'),
    );
    $form['payment']['alipay_settings']['partner'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Partner'),
        '#description' => $this->t('商户UID，以2088开头.'),
        '#default_value' => $config->get('alipay.partner'),
    );
    $form['payment']['alipay_settings']['app_id'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('App ID'),
        '#description' => $this->t('支付宝分配给开发者的应用ID.'),
        '#default_value' => $config->get('alipay.app_id'),
    );
    $form['payment']['alipay_settings']['sign_type'] = array(
        '#type' => 'select',
        '#title' => $this->t('Sign Type'),
        '#options' => array('RSA','RSA2'),
        '#description' => $this->t('签名方式.'),
        '#default_value' => $config->get('alipay.sign_type'),
    );
    $form['payment']['alipay_settings']['ali_public_key'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Public Key'),
        '#description' => $this->t('支付宝公钥.'),
        '#default_value' => $config->get('alipay.ali_public_key'),
    );
    $form['payment']['alipay_settings']['rsa_private_key'] = array(
        '#type' => 'textarea',
        '#title' => $this->t('Private Key'),
        '#description' => $this->t('用户应用私钥.'),
        '#default_value' => $config->get('alipay.rsa_private_key'),
    );
    $form['payment']['alipay_settings']['rsa_private_key'] = array(
        '#type' => 'limit_pay',
        '#title' => $this->t('Processors'),
        '#description' => $this->t('限制的支付方式.'),
        '#options' => $this->definitions['processor'],
        '#default_value' => $config->get('processors'),
    );
    $form['payment']['alipay_settings']['notify_url'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Notify Url'),
        '#description' => $this->t('支付宝异步通知的服务器地址.'),
        '#default_value' => $config->get('alipay.notify_url'),
    );
    $form['payment']['alipay_settings']['return_url'] = array(
        '#type' => 'textfield',
        '#title' => $this->t('Return Url'),
        '#description' => $this->t('支付报支付成功返回地址.'),
        '#default_value' => $config->get('alipay.return_url'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('alipay.alipaysetting')
      ->save();
  }

}
