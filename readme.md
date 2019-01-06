=== Campaign Monitor REST create and send from backend ===
Donate link: paypal.me/poppgerhard
Tags: E-Mail Marketing, Campaign Monitor, Newsletter 
Tested up to: 5
Requires PHP: 5.2.4
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
 
Simply create Campaign Monitor Campaigns from the backend edit post or edit custom post type pages
 
== Description ==
 
This is simple Plugin to create and send campaigns via Campaign Monitor from your backend. Because copy and pasting everything from wordpress to my campaigm monitor account sucked i developed this plugin. 
Now you can create drafts, including your post title ( also used as the Mail subject, with override capability ), your post excerpt ( overrideable ), the post thumbnail and a link as a bulletproof button.
A simple, responsive email template ( in english and german ), which you can easily customize with your logo, button color and text, footer text and your terms, is included. ( see screenshots from [herdzeit.de](https://www.herdzeit.de))  If you need a custom Template dont hesitate to contact me via [poppgerhard.at](https://www.poppgerhard.at/erstgespraech-in-wien/)

== Installation ==
 
This section describes how to install the plugin and get it working.
 
1. Install the Plugin via WordPress.org plugin repository by uploading the files to your server manually.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Navigate to the Campaign Monitor API Keys setting page and enter your API credentials [how to find your API key](https://help.campaignmonitor.com/api-keys)
1. Setup your custom logo, button, footer text and terms at the settings page.
 
== Frequently Asked Questions ==
= Can I use this Plugin without Campaign Monitor? =
No, you need a active Campaign Monitor account to use this Plugin.
= Can i customize the included E-Mail-Template? =
You can alter the template html ( not recomended ) via the Plugin Editor in the WordPress backend. The Template HTML File is located in the templates/default folder.
= Can i use my own E-Mail Templates? =
Yes, the template has to include the following editable content parts:
1. singleline: for the title
1. multiline: for the content
1. multiline: for the button - the html of the button will be send to CM by the plugin to customize the buttons link
1. multinine: for the explanaition text why users receive this email.
1. multiline: with your terms and conditions

Make sure that your template preserves this order. For more information see the [CM Template Language](https://www.campaignmonitor.com/create/)  
After you uploaded your template to Campaign Monitor you need to choose it in the Plugin settings.
 
== Screenshots ==
 
1. Enter your API credentials
2. Customize your Template Logo, Button and Text
3. Prepare a Campaign Draft
4. Sample E-Mail
5. Send or schedule your Campaign
 
== Changelog ==

= 0.1.0 =
* Initial Version
 
