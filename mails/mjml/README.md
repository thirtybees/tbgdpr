## What is this?

The Emails SDK is a toolkit to create and add email templates to the store.

## Installation

You first need to install [Node.js](https://nodejs.org).

Install Gulp:

`npm install -g gulp`

Download or clone the repository

Install all the dependencies:

```
cd email-templates-sdk
npm install
```

## Usage

During development, you just need to watch files:

`gulp watch`

This will detect changes on your files and output a compiled version in `./dist/en/`.  
3 languages are available during development but more languages are available when the emails are installed on PrestaShop. More information below.

## Build

To install the emails on PrestaShop, you need to build a package:

`gulp build`

This will create the folder `./dist/` with the name you set in the `./src/config/settings.json`.

Once you have built the templates in the `./dist/` folder make sure you push these new files to this repository. The store will then grab the new files, generate its templates from it and install the new templates. This is all done automatically and it only takes a few minutes before the changes are live.

## MJML: Emails made easy

Building emails is not easy. We know that and Mailjet too. This is why they created a framework to develop emails and make sure they will work fine for all email clients.

We chose to use this framework to help you develop your emails for PrestaShop. The better part is that Mailjet improves MJML everyday and you can enjoy it directly.

All the thirty bees' emails have been converted in a MJML format so you can create your own emails based on it.

You can find a complete documentation on the [official website](https://mjml.io/documentation/).

## Translations

Emails are automatically translated when they are installed. This is why you can find variables like this in your templates:

`${{ lang.my_variable }}$`

Make sure to use these variables and not hard coded text. Currently the store only uses the English translation files.

## Dynamic variables

In order to allow the merchants to customize they emails, you can create dynamic variables. In your email, you just need to include a variable where you want:

`{{$my_var}}`

For every variable, you need to declare it in `./src/config/settings.json`

```
{
  "product_key": "",
  "name": "preston",
  "version": "1.0",
  "inputs": [
    {
      "type": "text", // Two types are supported: text or color
      "name": "my_var",
      "default": "",
      "required": false,
      "label": {
        "en": "English label",
        "fr": "French label",
        ...
      },
      "desc": {
        "en": "English description",
        "fr": "French description",
        ...
      }
    }
  ]
}

```

To help you during your development, you can use the `./src/config/fake.json` to declare fake data. This will replace the variables in the compiled versions.

You can replace :

* Your dynamic variables.
* Default variables like {shop_name}. The default fake.json already comes with almost all the default variables.
* Whatever you want!

Note: A few variables for PrestaShop 1.5 are also available in the `fake.json`. When you want to test for this version, just temporarly replace the variables in your templates.

## Conditions

You will sometimes need to add some conditions. Social links are a good example as the merchants are not using all of them.

Your conditions must be really simple and only check if a variable is here.

```html
<mj-raw>{{if $my_var}}</mj-raw>
	// Your code
<mj-raw>{/if}</mj-raw>
```
You can find real examples in the default template's footer.

## Partials

If you want to include the same code in multiple files, you can use partials. Create a new file in the `./src/partials/` folder and then, include it where you want:

`<mj-include path="./src/partials/your_file.mjml" />`

## Global CSS

MJML lets you custom every components with attributes and we really recommend you to do it like this but sometimes, you will need to add some global CSS. Media queries are a good example.

Another good example is the `order_conf.html` email. On PrestaShop 1.5, the products list is hard coded and you can't change the HTML so you will need to custom the style. You will have to do it in the global CSS. Take a look at the default template for an example.

Starting 1.6, tpl files are used in the `order_conf.html` and we provide a better version in this SDK. You can edit the files as much as you want, they will replace the default files.

## Tests & Compatibility

We recommend you to test your emails with Litmus. On PrestaShop Addons, we will use it to make sure that your emails are valid on the most common email clients, both desktop and mobile. You can also create a few accounts (GMail, Yahoo...) to test it yourself.
