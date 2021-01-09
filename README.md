# Magento 2 Storyblok Integration
![Unit Tests](https://github.com/Media-Lounge/magento2-storyblok-integration/workflows/Unit%20Tests/badge.svg)
![Coding Standards](https://github.com/Media-Lounge/magento2-storyblok-integration/workflows/Coding%20Standards/badge.svg)
[![codecov](https://codecov.io/gh/Media-Lounge/magento2-storyblok-integration/branch/master/graph/badge.svg?token=5GDZEF7FMQ)](https://codecov.io/gh/Media-Lounge/magento2-storyblok-integration)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](https://www.gnu.org/licenses/gpl-3.0)

Our Magento 2 integration allows developers and digital agencies to create content-rich pages that are easily editable using the Storyblok interface. 

<!-- TODO: Add Overview Image -->

## Why Storyblok?

Storyblok allows you to manage content through a CMS that is intuitive and easy to use. It offers features like:

- Visual Editor
- Content Types with Blocks
- Custom Fields
- Internationalization Support
- Content Scheduling, and more!

It can be used as an **alternative to Magento Commerce's Page Builder** as it provides all of its features and, combined with our integration module, it allows for a more pleasant developer experience when it comes to creating custom blocks.

## Installation

1. Install via composer `composer require medialounge/magento2-storyblok-integration`
2. Run `php bin/magento setup:upgrade`

## Getting Started

Before you can start using our module you will need to [create an account in Storyblok](https://www.storyblok.com/), you can start by using their free tier but you will definitely want to upgrade to one of their paid plans if you want to use the more powerful features.

After creating your account make sure to [create a new space](https://app.storyblok.com/#!/me/spaces/new) so you can start configuring the module in the Magento admin area.

## Configuration

There are only 3 settings you will need to setup after installing the module, you can access them by going to **Stores → Configuration → Media Lounge → Storyblok**:

### Enable
Whether the module is enabled or not.

### API Key (required)
Your Storyblok API Key, you can get it from your Storyblok space by going to **Settings → API-Keys**.

Make sure the "**Access Level**" is set to "**preview**".

### Webhook Secret (required)
Random string that we will use to authenticate that requests are coming from Storyblok, we recommend using a 20+ characters alphanumeric string.

After this is set make sure to use the same value in your Storyblok Space by going to **Settings → General → Webhook secret**.

## Creating our first page

With the module configured we can start creating content in Storyblok, go to **Content → + Entry** and give it any name you want.

![Create Page in Storyblok](https://user-images.githubusercontent.com/661330/104088773-1a363e80-5261-11eb-9864-2d07e9545089.gif)

You should now see a preview of the page in your Magento 2 store, for initially it will be empty so let's create our first block.

## Creating our first block

A block is an individual piece of content that can be reused throughout the whole site, you can create one from the page preview screen by clicking **+ Add block** and giving it a name.

With our block created you can then define a schema for it which is just one or more fields that will be used to store the block's content. There are multiple field types available by default in Storyblok (text, textarea, wysiwyg, image, etc.) and you can know more about them in their [Field Type Documentation](https://www.storyblok.com/docs/terminology/field-type).

After adding some custom field types and including the new block in our page we should see a debug message displayed where the block should be, this is a hint from the module to tell us that this is a new block and we have not yet created a template file in our theme to render it so let's go ahead and do that.

![Missing block template](https://user-images.githubusercontent.com/661330/104089279-7e5b0180-5265-11eb-990d-a401e037b294.png)

## Creating the template for our first block

Out of the box **our module doesn't include blocks**, in fact we don't even add any extra CSS or JS to your Magento 2 store! We give you the freedom of customising your content any way you want using the technology stack of your choice.

The debug message above shows all the data that we have available as well as the path where it expects the PHTML template file to be in, so we just need to create a new file in **MediaLounge_Storyblok/templates/story/block-name.phtml** in our custom theme.

Inside this template we have access to the Storyblok fields as part of the block's data so you can access them by using the `$block->getData()` method (or magic methods if you prefer).

**MediaLounge_Storyblok/templates/story/block-name.phtml**
```phtml
<?php /** @var MediaLounge\Storyblok\Block\Container\Element $block */ ?>

<div>
    <h1><?= $block->getTitle(); ?></h1>
    <img src="<?= $block->getImage()['filename'] ?>" alt="<?= __('New Luma Collection') ?>">
</div>
```

> Hint: You can see all the block's available data using `var_dump($block->getData())`

### Helper Methods

The template block uses the `MediaLounge\Storyblok\Block\Container\Element` class which also extends `Magento\Framework\View\Element\Template` so we have access to all the Magento block methods that we are used to.

On top of this we expose a few helper methods to make working with Storyblok content a bit easier:

#### $block->renderWysiwyg(array $arrContent)

| Arguments | Description |
| ----------- | ----------- |
| $arrContent | Content of "Richtext" field |

When using "Richtext" fields this method will ensure that HTML elements are rendered.

**Usage:**
```phtml
<?php /** @var MediaLounge\Storyblok\Block\Container\Element $block */ ?>

<div>
    <?= $block->renderWysiwyg($block->getDescription()) ?>
</div>
```

#### $block->transformImage(string $image, string $param = '')

| Arguments | Description |
| ----------- | ----------- |
| $image | Image URL |
| $param | Transformation parameters for Storyblok Image Service |

Storyblok offers an [Image Service](https://www.storyblok.com/docs/image-service) that allows you to transform image's size, format, quality amongst other things. This method provides a convinient way of interacting with it so we are able to modify images on the fly.


**Usage:**
```phtml
<?php /** @var MediaLounge\Storyblok\Block\Container\Element $block */ ?>

<div>
    <img width="1280" height="460" src="<?= $block->transformImage($block->getImage()['filename'], '1280x460/filters:quality(80):format(webp)') ?>" alt="<?= __('New Luma Collection') ?>">
</div>
```

> HINT: This can be really powerful when combined with the `srcset` attribute or `<picture>` element!

## Putting it all together

From here on it's just a matter of repeating the same process for all custom blocks and by adding some CSS and JS we can create from simple to very complex layouts using the Storyblok interface.

![Managing blocks in Storyblok](https://user-images.githubusercontent.com/661330/104090740-a7cd5a80-5270-11eb-8b49-9ce392da9afa.gif)

## Publishing Content

When we are ready to make our Storyblok content available in our Magento 2 store we just need to publish it by clicking the **Publish** button in the top right.

Under the hood Storyblok will make a `POST` request to our website and our module will use this to clear the cache for that specific page, this is where the **Webhook Secret** setting is used so only requests coming from Storyblok are allowed to do this.

Make sure to set the following URL in your Storyblok Space under **Settings → General → Story published & unpublished and Datasource entry saved**:
`http://yourmagento.url/storyblok/cache/clean`

> This needs to be a publicly accessible URL, if you want to test this in your local environment you can use a service like [ngrok](https://ngrok.com/) to expose your local domain to the world. 
