# PHP CORS Proxy

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]

*Formerly known as "PHP Cross Domain (AJAX) Proxy"*

PHP CORS Proxy is a simple php script that allows cross domain requests. It can be used to access resources from third party websites when it's not possible to enable CORS on target website i.e. when you don't own that website.

**Note**: Please check whether this solution is indeed necessary by having a look on [how you can enable CORS on your server](http://enable-cors.org/server.html).

## Overview


### Features

* Acts as a reverse proxy: request headers and data are propagated from proxy to server. Similarly, response headers and data are propagated from proxy to client.
* Provides support for all methods GET, POST, PUT, DELETE.
* Provides also support for HTTPS.
* Requests can be filtered against a list of trusted domains or URLs.
* External configuration (Work in progress)
* Error handling i.e. when server is not available (Work in progress)
* Debugging mode (Work in progress)

### Requirements

PHP Cors Proxy works with PHP 5.3+ or above.

### Author

- [Iacovos Constantinou][link-author]  - softius@gmail.com - https://twitter.com/iacons
- See also the list of [contributors][link-contributors] which participated in this project.


### License

PHP CORS Proxy is licensed under GPL-3.0. See `LICENCE.txt` file for further details.


## Installation

**Using composer**

```
composer require softius/cors-proxy
```

**Manual installation**

The proxy is indentionally limited to a single file. All you have to do is to place `proxy.php` under the public folder of your application. 

### Configuration

For security reasons don't forget to define all the trusted domains / URLs into top section of `proxy.php` file:

``` JAVASCRIPT
$valid_requests = array(
    'http://www.domainA.com/',
    'http://www.domainB.com/path-to-services/service-a'
);
```

**Note**: There is currently ongoing work to allow configuration outside the `proxy.php` 

## Usage
It is possible to initiate a cross domain request either by providing the `X-Proxy-URL` header or by passing a special `GET` parameter. The former method is strongly suggested since it doesn't modify the request query. Also, the request looks more clear and easier to understand.

### Using headers

It is possible to specify the target URL by using the `X-Proxy-URL` header, which might be easier to set with your JavaScript library. For example, if you wanted to automatically use the proxy for external URL targets, for GET and POST requests:

``` JAVASCRIPT
$.ajaxPrefilter(function(options, originalOptions, jqXHR) {
    if (options.url.match(/^https?:/)) {
        options.headers['X-Proxy-URL'] = options.url;
        options.url = '/proxy.php';
    }
});
```

The following example uses `curl`

```
curl -v -H "X-Proxy-URL: http://cross-domain.com" http://yourdomain.com/proxy.php
```


### Using query

In order to make a cross domain request, just make a request to http://www.yourdomain.com/proxy.php and specify the target URL by using the `csurl` (GET) parameter. Obviously, you can add more parameters according to your needs; note that the rest of the parameters will be used in the cross domain request. For instance, if you are using jQuery:

``` JAVASCRIPT
$('#target').load(
    'http://www.yourdomain.com/proxy.php', {
        csurl: 'http://www.cross-domain.com/',
        param1: value1,
        param2: value2
    }
);
```

The following example uses `curl`

```
curl -v "http://yourdomain.com/proxy.php?csurl=http://www.cross-domain.com/&param1=value1&param2=value2"
```

[ico-version]: https://img.shields.io/packagist/v/softius/cors-proxy.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/softius/cors-proxy.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/softius/cors-proxy
[link-downloads]: https://packagist.org/packages/softius/cors-proxy
[link-author]: https://github.com/softius
[link-contributors]: ../../contributors
