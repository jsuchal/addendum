Extended Reflection API contains only three classes: `ReflectionAnnotatedClass`, `ReflectionAnnotatedMethod`, `ReflectionAnnotatedProperty`.

| **Method** | **Description** |
|:-----------|:----------------|
| `hasAnnotation($annotationName)` | Returns true if target object is annotated with `$annotationName` |
| `getAnnotation($annotationName)` | Returns annotation instance or `false` if annotation does not exists on target object |
| `getAnnotations()` | Returns an array of all **unique** annotations on target object |
| `getAllAnnotations()` | Returns an array of all annotations on target object |
| `getAllAnnotations($class)` | Returns an array of all annotations of target `$class` on target object |