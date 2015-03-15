# Advanced features #

## Multiple instances of annotations ##

You can place **multiple instances of same class annotations** on one target.

```
/** 
 @Title(value='Welcome', lang='en')
 @Title(value='Wilkommen', lang='de')
 @Title(value='Vitajte', lang='sk')
 @Snippet
 */
class WelcomeScreen {}
```

And then access them using
```
$reflection = new ReflectionAnnotatedClass('WelcomeScreen');
$annotations = $reflection->getAllAnnotations(); // array of all 4 annotations
$just_titles = $reflection->getAllAnnotations('Title'); // array of those 3 Title annotations
```


## Constrained annotations ##

### Meta-annotation `@Target` ###

Using `@Target` meta-annotation you can easily annotate annotations (yes, it's kind of scary!) to prevent annotations to be misplaced.

For example you might want a `@Route` annotation that can be only used to annotate methods.  It's easy:

```
/** @Target("method") */
class Route extends Annotation {}
```

Besides `"method"` target definition you can also use `"class"`, `"property"` and `"nested"`.


### Implementing custom constraints ###

Sometimes `@Target` meta-annotation does not provide enough power to check for additional constraints. You can override `Annotation::checkConstraints` method to add additional rules for validation, etc.

```
class Route extends Annotation {
   protected function checkConstraints($target) {
      if($this->value == /* something bad */) {
         // trigger some error
      } else {
         // do something else
      }
      // variable $target holds reflection class of annotation target (class, method, property)
   }
}
```

## Nested annotations ##

As if it were not enough, even annotations can be nested inside each other. Look at this:

```
/** @Mapping(inheritance = @SingleTableInheritance, columns = {@ColumnMapping('id'), @ColumnMapping('name')}) */
class Person {}

$reflection = new ReflectionAnnotatedClass('Person');
$annotation = $reflection->getAnnotation('Mapping');
$annotation->inheritance; // SingleTableInheritance annotation
$annotation->columns; // array of ColumnMapping annotations
$annotation->columns[0]->value; // 'id'
```

If you want to ensure that your `@ColumnMapping` annotation should be only used in nested contexts, add `@Target` meta-annotation with `"nested"`.

```
/** @Target("nested") */
class ColumnMapping extends Annotation {}
```

## Annotation class name resolving ##

If you use prefixed annotation class names like `MyDoomdayProject_God`. You can save yourself a few keystrokes by using:


```
class MyDoomsdayProject_God extends Annotation {}

/** @God */
class Me {}

$reflection = new ReflectionAnnotatedClass('Me');
$reflection->getAnnotation('God'); // returns MyDoomsdayProject_God
```

Did you notice that Addendum automatically resolves `@God` annotation into `@MyDoomsdayProject_God` annotation? And of course you can also use the full class name.