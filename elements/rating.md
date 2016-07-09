---
layout: default
title: Rating Element
permalink: elements/rating/
---

# Rating Element
The Rating Element represents an individual rating record.  Multiple Rating entries make up a [Collection][] which can be analyzed through the [Stats Model][].

---

### Properties
In addition to the properties found within the [ElementQuery][], the Rating query has the following additional parameters:

* [`elementId`](#elementid)
* [`ownerId`](#ownerid)
* [`collectionId`](#collectionid)
* [`name`](#name)
* [`email`](#email)
* [`status`](#status)

#### elementId
The Id of the associated element.

| - | - |
| **Type** | `integer` |
| **Required** | `true` |
| **Default** | `null` |


#### ownerId
The Id of the associated owner/author.

| - | - |
| **Type** | `integer` |
| **Required** | `true` |
| **Default** | `null` |


#### collectionId 
The Id of the associated [collection](/models/collection)

| - | - |
| **Type** | `integer` |
| **Required** | `true` |
| **Default** | `null` |


#### name
The name of a guest owner/author.

| - | - |
| **Type** | `string` |
| **Required** | `false` |
| **Default** | `null` |


#### email
The email of a guest owner/author.

| - | - |
| **Type** | `string` |
| **Required** | `false` |
| **Default** | `null` |


#### status
The status of the rating.

| - | - |
| **Type** | `string` |
| **Required** | `false` |
| **Default** | `null` |

---

### Methods
In addition to the methods found within the base [Element][], the Rating element has the following additional methods:

* [`hasElement`](#haselement)
* [`getElement`](#getelement-strict--true-)
* [`setElement`](#setelement-element-)
* [`hasCollection`](#hascollection)
* [`getCollection`](#getcollection-strict--true-)
* [`setCollection`](#setcollection-collection-)
* [`hasOwner`](#hasowner)
* [`getOwner`](#getowner-strict--true-)
* [`setOwner`](#setowner-owner-)
* [`getRatingFieldValues`](#getratingfieldvalues-fieldhandles--null-except---)
* [`setRatingFieldValues`](setratingfieldvalues-values-)
* [`getRatingFieldValue`](#getratingfieldvalue-fieldhandle-)
* [`setRatingFieldValue`](#setratingfieldvalue-fieldhandle-value-)
* [`setRatingFieldValuesFromBody`](#setratingfieldvaluesfrombody-values--ratings-)
* [`setRawBodyValueForRatingField`](#setrawbodyvalueforratingfield-handle-value-)
* [`getRatingContentFromBody`](#getratingcontentfrombody)
* [`getRatingFields`](#getratingfields-indexby--null-)
* [`getRatingField`](#getratingfield-identifier-strict--false-)

#### `hasElement()`
Indicates whether an associated [Element][] is set.

| - | - | - |
| **Return** | `boolean` | Whether or not an associated [Element][] is set. |


#### `getElement( $strict = true )`
Get the element that is associated to this rating element.

| - | - | - |
| **$strict** | `boolean` | Identify whether exceptions should be thrown if an [Element][] does not exist. |
| **Return** | [ElementInterface][] &#124; `null` | The associated ElementInterface |


#### `setElement( $element )`
Associates an element.  Accepts a singular reference to: [ElementInterface][], Element ID, Element Uri.

| - | - | - |
| **$element** | [ElementInterface][] &#124; `integer` &#124; `string` | A singular reference to an [ElementInterface][], Element ID, Element Uri. |
| **Return** | $this | The current rating element |


#### `hasCollection()`
Indicates whether an associated [Collection][] is set.

| - | - | - |
| **Return** | `boolean` | Whether or not an associated [Collection][] is set |


#### `getCollection( $strict = true )`
Get the [Collection][] that is associated to this rating element.

| - | - | - |
| **$strict** | `boolean` | Identify whether exceptions should be thrown if a [Collection][] does not exist. |
| **Return** | [Collection][] &#124; `null` | The associated [Collection][] |


#### `setCollection( $collection )`
Associates a collection model.  Accepts a singular reference to: [Collection][] object, Collection ID, Collection Handle.

| - | - | - |
| **$collection** | [Collection][] &#124; `integer` &#124; `string` | A singular reference to a [Collection][] object, Collection ID, Collection Handle. |
| **Return** | $this | The current rating element |


#### `hasOwner()`
Indicates whether an associated [Owner][User Element] is set.

| - | - | - |
| **Return** | `boolean` | Whether or not an associated [Owner][User Element] is set. |


#### `getOwner( $strict = true )`
Returns an associated [Owner][User Element] element.  Optionally, you can use the `strict` argument to indicate whether exceptions should be thrown if a owner does not exist.

| - | - | - |
| **$strict** | `boolean` | Identify whether exceptions should be thrown if an [Owner][User Element] does not exist. |
| **Return** | [Owner][User Element] &#124; `null` | The associated [Owner][User Element] |


#### `setOwner( $owner )`
Associates a owner element.  Accepts a singular reference to: [User Element][], User ID, Username or Email address.

| - | - | - |
| **$owner** | [Owner][User Element] &#124; `integer` &#124; `string` | A singular reference to a [Owner][User Element] object, User ID, Username or Email address. |
| **Return** | $this | The current rating element |


#### `getRatingFieldValues( $fieldHandles = null, $except = [] )`
Get an array of Field Model objects which are associated to this Rating element.

| - | - | - |
| **$fieldHandles** | `null` &#124; `array` | The field handles in which to return.  Null will return all. |
| **$except** | `array` | The field handles in which to not return. |
| **Return** | [Field Model][][] | An array of [Field Model][] objects |

#### `setRatingFieldValues( $values )`
Set rating field values.

| - | - | - |
| **$values** | `array` | An array of attribute values. |
| **Return** | $this | The current rating element |


#### `getRatingFieldValue( $fieldHandle )`
Get a singular [Rating Field][Field Model] value.

| - | - | - |
| **$fieldHandle** | `string` | The field handle in which to return. |
| **Return** | `mixed` | The parsed rating field value. |


#### `setRatingFieldValue( $fieldHandle, $value )`
Set a singular [Rating Field][Field Model] value.

| - | - | - |
| **$fieldHandle** | `string` | The field handle in which to set. |
| **$value** | `string` | The field value in which to set. |
| **Return** | $this | The current rating element |


#### `setRatingFieldValuesFromBody( $values = 'ratings' )`
Set rating field values from an HTTP request.

| - | - | - |
| **$values** | `string` &#124; `array` | The array identifier or attributes from post. |
| **Return** | $this | The current rating element |


#### `setRawBodyValueForRatingField( $handle, $value )`
Set a single rating field as request body value.

| - | - | - |
| **$handle** | `string` | The rating field handle. |
| **$value** | `string` | The rating field value. |
| **Return** | $this | The current rating element |


#### `getRatingContentFromBody()`
Get an array of raw rating field data from body request.

| - | - | - |
| **Return** | `array` | An array of raw rating request body data. |


#### `getRatingFields( $indexBy = null )`
Get an array of [Rating Field][Field Model] objects.

| - | - | - |
| **$indexBy** | `string` | Return an array with the keys indexed via an attribute. |
| **Return** | `array` | An array of r[Rating Field][Field Model] objects. |


#### `getRatingField( $identifier, $strict = false )`
Get an array of [Rating Field][Field Model] objects.

| - | - | - |
| **$identifier** | `string` &#124; `integer` | An identifier of the field in which to return. |
| **$strict** | `boolean` | Identify whether exceptions should be thrown if a [Field Model][] does not exist. |
| **Return** | [Field Model][]  &#124; `null` | An singular [Rating Field][Field Model] object. |


[Field Model]: /models/field "Rating Field Model"
[Collection]: /models/collection "Rating Collection Model"
[ElementInterface]: element_interface_url "Craft Element Interface"
[Element]: element_url "Craft Element"
[ElementQuery]: element_query_url "Craft Element Query"
[User Element]: user_element_url "Craft User Element"
[Stats Model]: /models/stats "Rating Stats"