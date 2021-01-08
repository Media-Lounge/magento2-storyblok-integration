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

1. Install via composer `composer require media-lounge/magento2-storyblok-integration`
2. Run `php bin/magento setup:upgrade`

## Getting Started

Before you can start using our module you will need to [create an account in Storyblok](https://www.storyblok.com/), you can start by using their free tier but you will definitely want to upgrade to one of their paid plans if you want to use the more powerful features.

After creating your account make sure to [create a new space](https://app.storyblok.com/#!/me/spaces/new) so you can start configuring the module in the Magento admin area.

## Configuration

There are only 3 settings you will need to setup after installing the module, you can access them by going to **Stores → Configuration → Media Lounge → Storyblok**:

#### Enable
Wheter the module is enabled or not.

#### API Key (required)
Your Storyblok API Key, you can get it from your Storyblok space by going to **Settings → API-Keys**.

Make sure the "**Access Level**" is set to "**preview**".

#### Webhook Secret (required)
Random string that we will use to authenticate that requests are coming from Storyblok, we recommend using a 20+ characters alphanumeric string.

After this is set make sure to use the same value in your Storyblok Space by going to **Settings → General → Webhook secret**.
