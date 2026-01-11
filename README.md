# Template Select plugin for Craft CMS

A fieldtype that allows you to select a template from the site templates folder using Craft's autosuggest component.

![Screenshot](resources/img/field-with-friendly.png)

## Requirements

This plugin requires Craft CMS 5.0 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require superbig/craft3-templateselect

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Template Select.

## Configuring Template Select

Create a new field and choose field type Template Select.

In the field settings, you can limit the list of available templates to a subfolder of the Craft templates folder. The path is relative, i.e. _subfolder/anotherfolder_. This may also be set to an ENV variable.

![Screenshot](resources/img/field-settings.png)

The field uses Craft's built-in autosuggest component, providing an improved UI with autocomplete and search functionality for template selection.

## Using Template Select

### Output the chosen template name:

```twig
{{ entry.fieldHandle }}
```

### Include/Embed the chosen template:

```twig
{% include entry.fieldHandle %}
```

### Include the template including subfolder if set:

```twig
{% include entry.fieldHandle.withSubfolder() %}
```

This is a alias for the following:

```twig
{{ entry.templateWithSubfolder.template(true) }}
```

### Output the subfolder name:

```twig
{{ entry.fieldHandle.subfolder() }}
```

### Output the filename without path:

```twig
{{ entry.fieldHandle.filename() }}
```

Brought to you by [Superbig](https://superbig.co)
