const lightCodeTheme = require('prism-react-renderer/themes/github');
const darkCodeTheme = require('prism-react-renderer/themes/dracula');

// With JSDoc @type annotations, IDEs can provide config autocompletion
/** @type {import('@docusaurus/types').DocusaurusConfig} */
(module.exports = {
  title: 'Laravel Paymongo',
  tagline: '',
  url: 'https://paymongo.rigelkentcarbonel.com',
  baseUrl: '/',
  onBrokenLinks: 'throw',
  onBrokenMarkdownLinks: 'warn',
  favicon: 'img/favicon.ico',
  organizationName: 'luigel',
  projectName: 'laravel-paymongo',
  noIndex: true,

  presets: [
    [
      '@docusaurus/preset-classic',
      /** @type {import('@docusaurus/preset-classic').Options} */
      ({
        docs: {
          routeBasePath: '/',
          sidebarPath: require.resolve('./sidebars.js'),
          editUrl: 'https://github.com/luigel/laravel-paymongo/edit/1.x/docs/',
        },
        theme: {

        },
      }),
    ],
  ],

  themeConfig:
    /** @type {import('@docusaurus/preset-classic').ThemeConfig} */
    ({
      navbar: {
        title: 'Laravel Paymongo',
        logo: {
          alt: 'Paymongo Logo',
          src: 'img/paymongo-logo.png',
        },
        items: [
          {
            type: 'doc',
            docId: 'getting-started',
            position: 'left',
            label: 'Documentation',
          },
          {
            href: 'https://github.com/luigel/laravel-paymongo',
            label: 'GitHub',
            position: 'right',
          },
        ],
      },
      footer: {
        style: 'dark',
        links: [
          {
            title: 'Docs',
            items: [
              {
                label: 'Documentation',
                to: '/',
              },
            ],
          },
          {
            title: 'Links',
            items: [
              {
                label: 'Github',
                to: 'https://github.com/luigel',
              },
            ],
          },
        ],
        copyright: `Copyright © ${new Date().getFullYear()} Rigel Kent Carbonel. Built with Docusaurus.`,
      },
      prism: {
        theme: lightCodeTheme,
        darkTheme: darkCodeTheme,
          additionalLanguages: ['php']
      },
    })
});
