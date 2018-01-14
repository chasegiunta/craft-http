# HTTP plugin for Craft CMS 3.x

Simply return the HTTP status of an url

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require chasegiunta/craft-http

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for http.

## Using HTTP

Ensure a URL is accessible - use `http.ok`.

```
{% if craft.http.ok('http://localhost:8080/devserver/asset/live.js') %}
```

Output the status of a URL, use `http.status` & `true` for second (echo http status) argument.

```
{{ craft.http.status('https://api.somewebsite.com', true) }}
{# echos "HTTP/1.0 301 Moved Permanently" #}

{% if 200 in craft.http.status('https://unpkg.com/vue@2.5.13', true) %}
    <script src="https://unpkg.com/vue@2.5.13">
{% else %}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.13/vue.js">
{% endif %}
```

HTTP will follow 301/302 redirects until it hits 200.

NOTE: It's not recommended to use this in production as a fallback detect, as a slow loading URL will block your page from loading. This is mainly for *local development* use where a live dev server hosting your assets may or may not be running. Consider using a javascript fallback solution, like [Fallback.js](http://fallback.io/) in production.

Brought to you by [Chase Giunta](chasegiunta.com)
