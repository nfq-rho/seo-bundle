NfqSeoBundle
=============================

NfqSeoBundle adds SEO URI support for symfony2 routes.

## Installation

### Step 1: Download NfqSeoBundle using composer

Add NfqSeoBundle in to your composer.json:

	{
		"repositories": [
 			{
	            "type": "vcs",
	            "url": "git@github.com:nfq-rho/seo-bundle.git"
	        },
		],
    	"require": {
        	"nfq-rho/seo-bundle": "dev-master"
    	}
	}

### Step 2: Enable the bundle

**Note**: This bundle depends on `tedivm/stash-bundle`

Enable the bundle in the kernel.:

	<?php
	// app/AppKernel.php

	public function registerBundles()
	{
	    $bundles = array(
        	// ...
			new Tedivm\StashBundle\TedivmStashBundle(),
        	new Nfq\SeoBundle\NfqSeoBundle(),
    	);
	}

## Step 3: Configuration
###config.yml
Add following config to your `config.yml`:

	nfq_seo:
	    default_locale: %locale%
	    slug_separator: -
	    path_separator: /
	    invalid_url_exception_message: core.exception.404
	    missing_url_strategy: empty_host
	    page:
	        rel_options:
				#This parameter defines, which query paramters are allowed in canonical URLs
	            allowed_canonical_parameters:
	                - page
            head:
	            xmlns:              http://www.w3.org/1999/xhtml
	            'xmlns:og':           http://opengraphprotocol.org/schema/
	            'xmlns:fb':           "http://www.facebook.com/2008/fbml"
	        title:            website.welcome.title
	        metas:
	            name:
	                keywords:             product keywords that you need for better SEO
	                description:          website.welcome.description
	                robots:               { value: 'index, follow', translatable: false }
	                viewport:             { value: 'width=device-width, initial-scale=1.0', translatable: false }
	            property:
	                # Facebook application settings
	                'fb:app_id':          XXXXXX
	                'fb:admins':          admin1, admin2

	                # Open Graph information
	                # see http://developers.facebook.com/docs/opengraphprotocol/#types or http://ogp.me/
	                'og:site_name':       ~
	                'og:description':     ~
	            charset:
	                 'UTF-8':     ''

This bundle also depends on `TedivmStashBundle`, so add following configuration for caching:

    stash:
        default_cache: seo
        caches:
            seo:
                drivers: [ Ephemeral ]

###Generators

One of the services which you have to define is a SEO *generator* service. This service is responsible for providing data for url generator. The SEO uri is created only when:

* `url` or `path` method is called in Twig
* `generateUrl` method is called


To define such service you have to use following service configuration in your `services.yml` file:

	# app/config/config.yml
	# Be sure to set different names for different generators
	foo_seo_slug_generator_service:
		#It's a good practice to put SEO related classes under separate namespace
        class: AcmeDemoBundle\Service\Seo\SlugGenerator
        calls:
            - [setEntityManager, ["@doctrine.orm.entity_manager"]]
		#	- Add any other service dependencies. But be aware that setter injection must be used
        tags:
			#Every property of this tag is required
			#name - must be set to seo.generator
			#route_name - route name for which this generator will generate SEO URIs, if you want to make single generator for
			#more than one route, you can achieve that by tagging same service multiple times with different route name
            - { name: seo.generator, route_name: route_name_1 }
            - { name: seo.generator, route_name: route_name_2 }
            - ...

The service class must extend `AbstractSlugGenerator`.

###Route translations

This bundle can be used with `LexikTranslationBundle` for route prefix translations. In order to translate your route prefixes, create `routes.[LOCALE].yml` translation file. A basic example of such file:

	#routes.en_US.yml
	foo_list_route_name: /foo-list-en
	foo_details_route_name: /foo-details-en/{id}

	#routes.lt_LT.yml
	foo_list_route_name: /foo-list-lt
	foo_details_route_name: /foo-details-lt/{id}

Other translations can be set via translation GUI which is available for Lexik.
