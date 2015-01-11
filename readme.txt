=== TrueNorth SrcSet Plugin ===
Contributors: ben.moody,ew_holmes
Tags: srcset,responsive,responsive images,srcset attribute,retina,retina images
Requires at least: 3.0
Tested up to: 4.1
Stable tag: 1.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

TrueNorth SrcSet Wordpress Plugin allows you to change the dimensions of images based on browser size by automatically adding the srcset attribute.

== Description ==
TrueNorth SrcSet Wordpress Plugin allows you to change the dimensions of images based on the current width of the users browser. When you assign a SrcSet group to an image size within Wordpress (e.g. medium or large). Any image within a post or page using that image size will have the SrcSet group's attribute applied to it.

[youtube http://www.youtube.com/watch?v=2JTaunYbt1M]

= Why use SrcSet? =

The srcset attribute is fully supported by most modern mobile devices. Where the attribute shines is allowing developers and content creators to swap out large images for smaller ones more suitable to the size of the devices screen. This allows for less overhead when loading a website, improving load times, and reducing the amount of bandwidth visitors have to consume to view the site.

= How does TrueNorth SrcSet Plugin Help? =

The TrueNorth SrcSet Wordpress Plugin automates the generation of the srcset attribute for all images attached to or within a post/page\'s content. SrcSet groups can be setup for any image size registered with Wordpress and once setup all images using that image size (e.g. \'medium\') will have that SrcSet group\'s attribute added to the img tag. No short codes, in fact no coding required at all!

= What about all my existing post images? =

The TrueNorth SrcSet Wordpress Plugin comes with a Regeneration tool which will search all your posts and pages (including custom post types) find any images using sizes which have SrcSet groups and automatically add the srcset attribute to each one!

== Installation ==
1. Upload to the `/wp-content/plugins/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Go to Tools > SrcSet Settings and create a new SrcSet group under the \'General\' options tab and click \'save changes\'
4. Click the group options tab on the left for your new SrcSet group.
5. You want to create the srcset for all \"large\" images, so link this group to the \'large\' image size be selecting it from the \'Image Size Relationship\' drop down menu.
6. We need to tell the browser which image size to use when the browser size changes, start by selecting the \'small\' breakpoint from the \'Select Breakpoint\' drop down menu.
7. The default browser width for this breakpoint is 640px wide. We can leave this or use the \'Breakpoint Width\' slider to customize this.
8. Now we need to tell the browser which image size we want it to display at this breakpoint. Using the \'Breakpoint Image Size\' drop down menu we can either select an image size already registered with Wordpress OR we can create a new custom image size.
9. !!Important!! If you create a custom image size for any of your breakpoints you will have to regenerate your image thumbnails using a plugin such as \"Regen. Thumbnails\"
10. Complete steps 5 & 6 for each breakpoint (small, medium, large, large)
11. Save your SrcSet group options.
12. All existing posts/pages which have \"large\" images embedded in them will need to be converted to use your new SrcSet group.
13. !!Warning!! Backup your database BEFORE this step as the process is irreversible!! Click the \'Regeneration\' tab in the plugin options on the left. Click the \'Regenerate srcset\' button and wait for the process to complete. Now all your existing posts using \"large\" images are now using your SrcSet group!
14. Now whenever a user adds an image using the \"large\" size into a post/page content, the img html tag will also have your SrcSet group attribute added to it automatically!

== Frequently Asked Questions ==
= What are SrcSet groups? =
SrcSet groups allow you to change the dimensions of images based on the current width of the user\'s browser. When you assign a SrcSet group to an image size within Wordpress (e.g. medium or large). Any image within a post or page using that image size will have the SrcSet group\'s attribute applied to it.

For example, this 'medium' image...

img class=\"alignnone size-medium wp-image-1264\" src=\"http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img-200x150.jpeg\" alt=\"test_img\" width=\"200\" height=\"150\"

Would change to something like this…

img class="alignnone size-medium wp-image-1264\" 
src=\"http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img-200x150.jpeg\" 
srcset=\"http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img-125x125.jpeg 640w,
http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img-200x150.jpeg 1024w,
http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img.jpeg 1440w,
http://www.pressoholics.dev/wp-content/uploads/2011/05/test_img-1440x1200.jpeg 1506w\" 
alt=\"test_img\" width=\"200\" height=\"150\"

Note that the 'srcset' attribute had been added to the img tag and will instruct the browser to replace the image with different sizes at these breakpoints, 640w, 1024w, 1440w, and 1506w.

= Need help setting up a SrcSet Group? =
In the plugin options under Tools > SrcSet Settings, click the 'Help' tab on the left and following the instructions for more examples.

== Changelog ==
= 1.0 =
Initial plugin launch.