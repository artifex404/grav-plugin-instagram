# Grav Instagram Plugin

`Instagram` is a simple [Grav](https://getgrav.org) Plugin that includes your Instagram feed to your Grav website.

# Installation

Installing the Instagram plugin can be done in one of two ways. Using GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file.

## GPM Installation (Preferred)

The simplest way to install this plugin is via the [Grav Package Manager (GPM)](http://learn.getgrav.org/advanced/grav-gpm) through your system's Terminal (also called the command line).  From the root of your Grav install type:

    bin/gpm install instagram

This will install the Instagram plugin into your `/user/plugins` directory within Grav. Its files can be found under `/your/site/grav/user/plugins/instagram`.

## Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `instagram`. You can find these files either on [GitHub](https://github.com/artifex404/grav-plugin-instagram) or via [GetGrav.org](http://getgrav.org/downloads/plugins#extras).

You should now have all the plugin files under

    /your/site/grav/user/plugins/instagram

# Configuration

You need to provide few configurations in order for the feed show up. In your Grav Administration panel, go to Plugins > Instagram to view the plugin configuration page.

Enter the Instagram *user_id* whose feed you want to show, and your Instagram API *access_token*.

Note, that your access token needs to have the public_content scope authorized.

For more information how to get user_id or access_token, see the [Instagram API documentation](https://www.instagram.com/developer/).

# Customization

To customize how the your feed looks like, you might want to customize the generated markup. To do that, copy the template file [instagram.html.twig](templates/partials/instagram.html.twig) to your `templates/partials` folder of your theme. For example:

```
/your/site/grav/user/themes/custom-theme/templates/partials/instagram.html.twig
```

It will now override the default markup of the feed. You can tweak it however you like.

# Config Defaults

If you need to override some plugin default values, the best practise is to copy the [instagram.yaml](instagram.yaml) file into your `users/config/plugins/` folder (create it if it doesn't exist), and then modify there. This will override the default settings.

# Usage

To use this plugin you simply need to include a function your template file such as:

```
{{ instagram_feed() }}
```

This will be converted into your Instagram feed as follows:

```
<ul>
    <li><a href="{{ post.link }}" target="_blank"><img src="{{ post.image }}" alt=""></a></li>
    <li><a href="{{ post.link }}" target="_blank"><img src="{{ post.image }}" alt=""></a></li>
    ...
</ul>
```

You can also pass in twig variables, such as a custom class.

```
{{ instagram_feed({custom_class: 'someclass'}) }}
```

This will be accessible in the `partials/instagram.html.twig` as a property of a `params` variable. For example:

```
{% for post in feed|slice(0, count)  %}
    <li class="{{ params.custom_class }}"><a href="{{ post.link }}" target="_blank"><img src="{{ post.image }}" alt=""></a></li>
{% endfor %}
```
