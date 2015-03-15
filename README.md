# Addendum

DocBlock/JavaDoc annotations support for PHP5. Supporting single and multi valued annotations accessible through extended Reflection API.

Example annotations:
```php
@SimpleAnnotation
@SingleValuedAnnotation(true)
@SingleValuedAnnotation(-3.141592)
@SingleValuedAnnotation('Hello World!')
@SingleValuedAnnotationWithArray({1, 2, 3})
@MultiValuedAnnotation(key = 'value', anotherKey = false, andMore = 1234)
```

Annotate classes, methods, properties.

Works also if `--preserve-docs` is disabled.

Checkout ShortTutorialByExample for a [quick introduction](https://github.com/jsuchal/addendum/wiki/Short-Tutorial-By-Example) to annotations using Addendum.
