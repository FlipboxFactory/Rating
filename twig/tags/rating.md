---
layout: default
title: TWIG Tags - Rating
permalink: twig/tags/rating/
---

# Twig Tags - Rating

The `craft.rating` variable enables interaction with Rating entries.  Commonly, these tags are used to display information about a particular [Rating][] or [Collection][] of [Ratings][Rating].

## Methods

The following methods are available:

* [`craft.rating.find()`](#find-parameters-)
* [`craft.rating.create()`](#create-parameters-)

---

## `find( parameters )`

Returns an [Rating Query][] object.  In addition to the methods and properties available within the [Element Query][], refer to the [Rating Query][] for additional methods and properties. 

{% raw %}
~~~twig
{% set collectionHandle = 'foo' %}
{% for rating in craft.rating.find({collection: collectionHandle}) %}
    <li>Rating Id: {{ rating.id }}</li>
{% endfor %}
~~~
{% endraw %}

### Usage
The following are common usage examples:

* [`element`](#element)
* [`owner`](#owner)
* [`collection`](#collection)
* [`name`](#name)
* [`email`](#email)
* [`status`](#status)

#### `element`
Only fetch Rating(s) that are associated by an [Element][](s). Accepts either an array or singular reference to: [ElementInterface][], Element ID, Element Uri.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({element: [1,2]}) %}
{% set ratingQuery = craft.rating.find({element: 'foo'}) %}
~~~
{% endraw %}

#### `owner`
Only fetch Rating(s) that are associated to a [Owner][User Element](s). Accepts either an array or singular reference to: [User Element][] object, User ID, Username or Email address.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({owner: [1,2,4]}) %}
{% set ratingQuery = craft.rating.find({owner: currentUser}) %}
~~~
{% endraw %}

#### `collection`
Only fetch Rating(s) that are associated to a [Collection][](s). Accepts either an array or singular reference to: [Collection Model][Collection] object, Collection ID, Collection Handle.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({collection: [1,2]}) %}
{% set ratingQuery = craft.rating.find({collection: 'foo'}) %}
~~~
{% endraw %}

#### `name`
Only fetch Rating(s) with the given name.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({name: 'foo'}) %}
~~~
{% endraw %}


#### `email`
Only fetch Rating(s) with the given email.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({email: 'craft.plugins@flipboxfactory.com'}) %}
~~~
{% endraw %}

#### `status`
Only fetch Rating(s) with the given status.  A `null` value will return all ratings regardless of status.

{% raw %}
~~~twig
{% set ratingQuery = craft.rating.find({status: ['active', 'pending']}) %}
{% set ratingQuery = craft.rating.find({collection: 'active'}) %}
~~~
{% endraw %}

---

## `create( parameters )`

Returns a new [Rating Element][Rating] object.  It is good practice to interact with models throughout your templates as much as possible.

{% raw %}
~~~twig
{% set ratingElement = craft.rating.create({
    collection: 'foo',
    name: 'bar'
}) %}
~~~
{% endraw %}

### Properties
In addition to the properties found within the [Base Element][Element], `craft.rating.create` supports the following additional properties:

* [`element`](#element-default--null-1)
* [`owner`](#owner-default--null-1)
* [`collection`](#collection-default--null-1)
* [`name`](#name-default--null-1)
* [`email`](#email-default--null-1)
* [`status`](#status-default--active-1)

#### `element` *(default = null)*
Identify the [Element][] which the rating is associated to.  Accepts a singular reference to: [ElementInterface][], Element ID, Element Uri.

#### `owner` *(default = null)*
Identify the [Owner][User Element] which the rating is associated to.  Accepts a singular reference to: [User Element][], User ID, Username or Email address.

#### `collection` *(default = null)*
Identify the [Collection][] which the rating should be associated to. Accepts a singular reference to: [Collection][] object, Collection ID, Collection Handle.

#### `name` *(default = null)*
Identify the name of the guest owner/author.

#### `email` *(default = null)*
Identify the email of the guest owner/author.

#### `status` *(default = active)*
Identify the status of the [Rating][].

[ElementInterface]: element_interface_url "Craft Element Interface"
[Element]: element_url "Craft Element"
[Element Query]: element_query_url "Craft Element Query"
[User Element]: user_element_url "Craft User Element"
[Collection]: /models/collection "Rating Collection Model"
[Rating]: /models/element/rating "Rating Element"
[Rating Query]: /queries/rating "Rating Query"