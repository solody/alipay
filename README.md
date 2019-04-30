# Alipay

- 为[Finance Account](https://www.drupal.org/project/account)模块提供提现转账网关插件，
  实现把人民币现金从支付宝商家账户转账到支付宝个人账户。
  
  - 提供了可选的提现手续费自动扣除功能，可以在后台设置费率。
  
- 为[commerce_payment](https://www.drupal.org/project/commerce) 模块提供订单支付网关，
  实现在PC web端使用支付宝支付购物订单，`(该功能未实现)`
  以及提供REST接口，使APP端可以获取支付宝调起来数据，在APP端实现支付宝支付。
  
## 依赖

- [lokielse/omnipay-alipay](https://github.com/lokielse/omnipay-alipay) 
  该composer库对支付宝的API进行了封装，提供了常见的多种支付方式支持。
