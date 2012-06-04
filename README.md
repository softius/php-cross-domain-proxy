PHP Cross Domain (AJAX) Proxy
==============

An application proxy that can be used to transparently transfer any request ( including of course XMLHTTPRequest ) to any third part domain. It is possible to define a list of acceptable third party domains and you are encouraged to do so. Otherwise the proxy is open to any kind of requests.

Installation
--------------

The proxy is indentionally limited to a single file. All you have to do is to place `proxy.php` under your application

Whenever you want to make a cross domain request, just make a request to http://www.yourdomain.com/ajax-proxy.php and specify the cross domain URL by using `csurl` parameter. Obviously, you can add more parameters according to your needs; note that the rest of the parameters will be used in the cross domain request. For instance, if you are using jQuery:

	$('#target').load(
		'http://www.yourdomain.com/ajax-proxy.php', {
			csurl: 'http://www.cross-domain.com/',
			param1: value1, 
			param2: value2
		}
	);

It’s worth mentioning that both POST and GET methods work and headers are taken into consideration. That is to say, headers sent from browser to proxy are used in the cross domain request and vice versa. 

Configuration
--------------

For security reasons don't forget to define all the valid requests into top section of `proxy.php` file:
	
	$valid_requests = array(
  		'http://www.domainA.com/',
  		'http://www.domainB.com/path-to-services/service-a'
	);


 