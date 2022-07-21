# WP Image
The "missing" WP_Image object.

## Getting Started
Assuming you are in your projects' root directory that you wish to install **WP_Image** in, open the **composer.json** file and add the following line to the **require** JSON block:

```php
"devanime/wp-image": "dev-master",
```

### Installing
A step by step series of examples that tell you how to get **WP_Image** up and running in your environment:

Ensure you are in your projects' root directory, run the following command:

```linux
composer develop
```

In the event that the command `composer develop` is not configured on your particular project, run this sequence of commands:

```linux
composer install -o
cd wp-content/themes/baa
yarn
bower install
gulp
```

## Confirm Installation
You can confirm the installation by checking the **plugins** directory. You should notice the **wp-image** directory is present.

### Usage
To display a featured image of a post, pass in the post ID:

```php
WP_Image::get_featured($post_id);
```

To display an arbitrary image, pass in the attachment ID:

```php
WP_Image::get_by_attachment_id($attachment_id).
```

To display an arbitrary image, pass in the source URL:

```php
WP_Image::create_from_url('...');
```

## Attributes
Below is a list of all of the available attributes that can be used on the **WP_Image** object:
* **url** : returns the url of the image.
* **width** : returns the width of the image.
* **height** : returns the height of the image.
* **orig_url** : returns the original url of the image.
* **orig_width** : returns the original width of the image.
* **orig_height** : returns original height of the image.
* **alt** : returns the alt text of the image.
* **title** : returns the title of the image.
* **caption** : returns the caption of the image.
* **description** : returns the description of the image.
* **css_class** : returns any css classes assigned to this image.

```php
echo $wp_image->url;
echo $wp_image->width;
echo $wp_image->height;
echo $wp_image->orig_url;
echo $wp_image->orig_width;
echo $wp_image->orig_height;
echo $wp_image->alt;
echo $wp_image->title;
echo $wp_image->caption;
echo $wp_image->description;
echo $wp_image->css_class;
```

## Methods
Below is a list of all of the available public methods that can be used on the **WP_Image** object:

**Note** : These methods can be **daisy chained**

* **width** : change the width dimension of the image.

```php
echo $wp_image->width(100);
```

* **height** : change the height dimension of the image.

```php
echo $wp_image->height(100);
```

* **crop** : when resizing an image using `width` or `height`, you can set crop to `false` to prevent the hard crop.
 
```php
echo $wp_image->width(100)->height(100)->crop(false);
```

* **css_class** : add css classes to the image.

```php
echo $wp_image->css_class('media__image');
```

* **custom_attr** : add custom attributes to the image (i.e., data attributes, aria attributes, etc.).

```php
echo $wp_image->custom_attr('data-modal-target', '#my-example-post');
```

* **attr** : returns the value of an attribute.

```php
echo $wp_image->attr('data-modal-target');
```

## Authors
* **DevAnime** - [devanimecards@gmail.com](devanimecards@gmail.com)
* **DevAnime** - [devanimecards@gmail.com](devanimecards@gmail.com)
* **DevAnime** - [devanimecards@gmail.com](devanimecards@gmail.com)
* **Dev Anime** - [devanimecards@gmail.com](devanimecards@gmail.com)
