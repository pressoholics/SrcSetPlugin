



!!! HEY DONT FORGET THE PLUGIN NAME BELOW !!!!




=== YOUR PLUGIN NAME HERE by Benjamin Moody ===
Contributors: ben.moody, ew_holmes
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Tags: 
Requires at least: 3.0
Tested up to: 3.8
Stable tag: 1.0

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

== Description ==

This is the long description.  No limit, and you can use Markdown (as well as in the following sections).

For backwards compatibility, if this section is missing, the full length of the short description will be used, and
Markdown parsed.

A few notes about the sections above:

*   "Contributors" is a comma separated list of wp.org/wp-plugins.org usernames
*   "Tags" is a comma separated list of tags that apply to the plugin
*   "Requires at least" is the lowest version that the plugin will work on
*   "Tested up to" is the highest version that you've *successfully used to test the plugin*. Note that it might work on
higher versions... this is just the highest one you've verified.
*   Stable tag should indicate the Subversion "tag" of the latest stable version, or "trunk," if you use `/trunk/` for
stable.

    Note that the `readme.txt` of the stable tag is the one that is considered the defining one for the plugin, so
if the `/trunk/readme.txt` file says that the stable tag is `4.3`, then it is `/tags/4.3/readme.txt` that'll be used
for displaying information about the plugin.  In this situation, the only thing considered from the trunk `readme.txt`
is the stable tag pointer.  Thus, if you develop in trunk, you can update the trunk `readme.txt` to reflect changes in
your in-development version, without having that information incorrectly disclosed about the current stable version
that lacks those changes -- as long as the trunk's `readme.txt` points to the correct stable tag.

    If no stable tag is provided, it is assumed that trunk is stable, but you should specify "trunk" if that's where
you put the stable version, in order to eliminate any doubt.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the `tn-srcset` directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Under Tools > Srcset Options, create image groups for the image sizes you wish to add srcset attributes to.
1. Once your Image Groups are established, start adding in your images into the Media Library, for existing images, use a plugin like [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/ "Regenerate Thumbnails") to create any new images sizes required.

== Frequently Asked Questions ==

= I've set up an image group for an image size, but my images are not receiving the srcset attribute. What's wrong? =

If the images were already uploaded to the Media Library, you may have to regenerate thumbnails. We recommend using [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/ "Regenerate Thumbnails") for this.


= What are Image Groups? =

Image Groups give you a way to relate breakpoints to any specific image size (for example, medium). You can set specific breakpoints that align to your theme, and load in smaller versions of the same image. You can select existing image sizes (thunbmail, medium, large), custom image sizes created by themes or plugins, or add a custom image size with dimensions you create.

*Note:* If you use the Custom image size, remember to regenerate your thumbnails, otherwise these images will not exist, and therefore not be used within the srcset attribute.


= I just installed this plugin, but I have existing content with images that I'd like to add srcset attributes to. Can I do this? =

Absolutely! In the srcset Settings area, there is a Regeneration page. This feature will go through all public posts (pages and posts by default), and regenerate the image tags of WordPress attachments in post content.


= I just installed a new theme, and the breakpoints I created for my image groups no longer align with my theme. What can I do? =

First, take some time and adjust your image groups and their breakpoints. Once everything is set up properly to your liking, use [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/ "Regenerate Thumbnails") to create any new image sizes you've created. Then use the Srcset Regeneration tool.

= What about Retina support for srcset? =

We intended on including the pixel density (x) support into the plugin. On further investigation, we realized it was not nearly as useful as the breakpoint (w) feature.

 In future releases, we will be adding in the pixel density (x) feature support, and hope that it will become a more useful feature, to be combined with the breakpoint (w) feature.

= What benefits does this plugin give to my website? =

The srcset attribute is a new HTML5 spec which allows us to specify multiple versions of the same image for various screen sizes. This means that you can serve smaller images for smaller screens and devices, which means your viewers will load your page much faster.

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets 
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png` 
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
* A change since the previous version.
* Another change.

= 0.5 =
* List versions from most recent at top to oldest at bottom.

== Upgrade Notice ==

= 1.0 =
Upgrade notices describe the reason a user should upgrade.  No more than 300 characters.

= 0.5 =
This version fixes a security related bug.  Upgrade immediately.

== Arbitrary section ==

You may provide arbitrary sections, in the same format as the ones above.  This may be of use for extremely complicated
plugins where more information needs to be conveyed that doesn't fit into the categories of "description" or
"installation."  Arbitrary sections will be shown below the built-in sections outlined above.

== A brief Markdown Example ==

Ordered list:

1. Some feature
1. Another feature
1. Something else about the plugin

Unordered list:

* something
* something else
* third thing

Here's a link to [WordPress](http://wordpress.org/ "Your favorite software") and one to [Markdown's Syntax Documentation][markdown syntax].
Titles are optional, naturally.

[markdown syntax]: http://daringfireball.net/projects/markdown/syntax
            "Markdown is what the parser uses to process much of the readme file"

Markdown uses email style notation for blockquotes and I've been told:
> Asterisks for *emphasis*. Double it up  for **strong**.

`<?php code(); // goes in backticks ?>`
