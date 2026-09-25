/**
 * Creating a sidebar enables you to:
 - create an ordered group of docs
 - render a sidebar for each doc of that group
 - provide next/previous navigation

 The sidebars can be generated from the filesystem, or explicitly defined here.

 Create as many sidebars as you want.
 */

module.exports = {
  tutorialSidebar: [
    'introduction',
    'installation',
    'your-first-payment',
    'choose-a-flow',
    {
      type: 'category',
      label: 'Usage',
      collapsed: false,
      items: [
        'payment-intents',
        'payment-methods',
        'payments',
        'refunds',
        'customers',
        'checkout-sessions',
        'links',
        'payment-links',
        'qrph',
        'subscriptions',
        'payouts',
        'webhooks',
        'testing',
        'sources',
      ],
    },
  ],
};
