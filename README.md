<p align="center">
<img src="https://user-images.githubusercontent.com/4788787/205036810-aa5dcd65-d7d4-4728-9284-1201a6067cb4.png" alt="PrettyBlocks logo">
</p>

# PrettyBlocks
<br>
<div style="background-color: #dc2626; color: white; padding: 10px; border-radius: 8px; text-align:center "> PrettyBlocks V4 arrive très prochainement 😉</div>
<br>

![Téléchargements](https://img.shields.io/github/downloads/PrestaSafe/prettyblocks/total)

Le premier page builder open source conçu pour PrestaShop ! 

La documentation technique est disponible ici : [https://prettyblocks.io/](https://prettyblocks.io/): 


## Installation

> L'installation du module à partir de GitHub nécessite l'installation de nodejs et npm.

### Manuellement

```
cd modules
git clone https://github.com/PrestaSafe/prettyblocks.git
cd prettyblocks
composer install
cd _dev
npm install && npm run build
cd ../../../ && php bin/console prestashop:module install prettyblocks
```

### Release

Téléchargez `prettyblocks-{version}.zip` dans les assets de la
[dernière release](https://github.com/PrestaSafe/prettyblocks/releases/latest),
puis installez ce fichier directement dans votre PrestaShop. N'utilisez pas
l'archive automatique **Source code (zip)** de GitHub : elle ne contient pas le
module compilé et n'est pas installable.

Vous pouvez également utiliser notre module ClassicBlocks, afin d'avoir 4 blocks à utiliser. Disponible ici: [Télécharger ClassicBlocks](https://github.com/PrestaSafe/classicblocks)

## Themes compatible

Découvrez notre [Theme Optimisé SEO / Performance et mobile PrestaShop: CartZilla](https://www.prestasafe.com/product/theme-prestashop-cartzilla) compatible avec PrettyBlocks !


## English version

PrettyBlocks is the first page builder open source for PrestaShop

You will find the technical documentation here (only in French, soon in english too): [Technical documentation](https://prettyblocks.io/)

## Installation

> In order to install the module from GitHub source, you must have node and npm available on your computer.

### Manually

```
cd modules
git clone https://github.com/PrestaSafe/prettyblocks.git
cd prettyblocks
composer install
cd _dev
npm install && npm run build
cd ../../../ && php bin/console prestashop:module install prettyblocks
```

### From Release
Download `prettyblocks-{version}.zip` from the assets of our
[latest release](https://github.com/PrestaSafe/prettyblocks/releases/latest)
and install that file directly in PrestaShop. Do not use GitHub's automatically
generated **Source code (zip)** archive: it does not contain the compiled module
and cannot be installed.

You can also add our module ClassicBlocks for having 4 blocks to use [Download ClassicBlocks](https://github.com/PrestaSafe/classicblocks)

## Compatible Themes

Discover our [PrestaShop SEO / Performance and Mobile Optimized Theme: CartZilla](https://www.prestasafe.com/product/theme-prestashop-cartzilla) compatible PrettyBlocks !

## Top contributors 
<p>Thanks to</p>
<a href="https://github.com/PrestaSafe/prettyblocks/graphs/contributors">
  <img src="https://contrib.rocks/image?repo=PrestaSafe/prettyblocks" />
</a>


## License

This module is released under AFL license.
See [License](/LICENSE.md) for details.

Enjoy 😉
