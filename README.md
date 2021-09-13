# Comparing Capsule To Other Containers

When I started this autowiring dependency injection (AWDI) container comparison,
I presumed that aside from ancillary and additional features, the differences
between the systems would not be in the core of *what they do*. I expected the
differences would be in *how they do it*.

As it turns out, I was wrong. What I considered to be fundamental capabilities
were just not available in some AWDI systems -- at least, not without some
indirection, extra effort, or workarounds.

## The Scenario

The scenario of fundamental features I imagined, thinking it would be easy to
work through in every AWDI system, was this:

- Define an instance of a [_Foo_](./setup.php) class, to be retrieved later via
  container.

- Override a single constructor argument to replace one of originally configured
  values for _Foo_ with a different value. (This simulates the loading of
  multiple config files.)

- Lazy-resolve one or more values in a dependent object for _Foo_, e.g. from the
  environment. (This shows off lazy resolution features.)

But what I thought would be easy turned out to be difficult or impossible in
some AWDI containers.

Below you will find code for the scenario using Auryn, League Container,
Illuminate Container, and PHP-DI. Each container example uses the same
[setup](./setup.php) with an `output()` function for inspecting the results.

### Capsule

Since Capsule is the focal point for these comparisons, it makes sense to start
with it. You can find the Capsule code at <https://github.com/capsulephp/di>.
The example code for the scenario is [here](./capsule.php).

Some notes:

1. Each definition is a dynamic property on the container, addressed using the
`{}` notation.

2. The _PDO_ arguments are lazy-resolved from the environment using the `env()`
method on the class definition.

3. The explicit _Foo_ arguments are literal; the $bar argument is purposely set
to "wrong" so that we can see later if it is overridden properly.

4. The _Foo_ $bar argument is re-defined, to simulate a new configuration being
loaded with override values.

The `output()` is correct:

```
PDO
bar-right
baz-right
```

### Auryn

You can find the Auryn code at <https://github.com/rdlowrey/auryn>. The example
code for the scenario is [here](./auryn.php).

Auryn **does not** complete the scenario:

1. There appears to be no lazy-loading facility to read environment variables,
or anything else. Thus, the _PDO_ arguments cannot be specified directly on the
container. Instead, the injector requires a delegate factory closure, which
does mean the _PDO_ arguments get lazy-loaded in a second-hand sort of way.

2. The explicit _Foo_ argument names have to be prefixed with a `:` to indicate
they are literals; the $bar argument is purposely set to "wrong" so that we
can see later if it is overridden properly.

3. As there appears to be no way to address an individual argument, `define()`
is called again to redefine the $bar argument. Unforturnately, `define()`
overwrites the entire _Foo_ definition, not just the one argument.

As a result, the `output()` fails, showing the default $baz value instead of the
explicitly configured one:

```
PDO
bar-right
baz-wrong
```

### Illuminate Container

You can find the Illuminate Container code (a Laravel component) at
<https://github.com/illuminate/container>. The example code for the scenario
is [here](./illuminate.php).

Illuminate Container **does** complete the scenario, but only with some extra
effort:

1. The _PDO_ arguments have to be specified individually, using a `$` prefix on
the parameter names, via then `when()->needs()` idiom.

2. The _PDO_ arguments have to be drawn from a configuration source via
`giveConfig()`, not from the environment per se. (See point 5 below.)

3. The explicit _Foo_ arguments likewise have to be specified individually; the
$bar argument is purposely set to "wrong" so that we can see later if it is
overridden properly.

4. The _Foo_ $bar argument is re-defined, to simulate a new configuration being
loaded with override values.

5. The `giveConfig()` method expects a container entry object called `'config'`
to be present, with a `get()` method on it. To honor this, a config container
factory closure is created and bound to the main container; the necessary
config values are retrieved from the environment. Thus, the environment values
are lazy-loaded, but indirectly and in a second-hand sort of way.

The `output()` is correct:

```
PDO
bar-right
baz-right
```

### League Container

You can find the League Container code at
<https://github.com/thephpleague/container>. The example code for the scenario
is [here](./league.php).

The League Container **does not** complete the scenario, even with extra effort:

1. The container itself will not autowire unless you set up a _ReflectionContainer_
as a fallback delegate.

2. The _PDO_ arguments are specified as lazy-resolvable, though that resolution
must be via the container, not directly from the environment. (See point 6
below.)

3. The _Foo_ $pdo argument is specified as lazy-resolvable, but this isn't
necessary in any of the other containers presented here; this seems at odds
with the advertised autowiring capability.

4. The _Foo_ $bar argument is specified as lazy-resolvable, because there is no
way to override an individual constructor argument. As a workaround, the value
is lazy-resolved out of the container, in hopes that it can be defined and then
re-defined later.

5. The _Foo_ $baz argument is specified as a literal string object, to tell the
container not to try to resolve it any further.

6. The container then gets set with values: the initial value for _Foo_ $bar,
and the _PDO_ arguments lazy-loaded as closures using `getenv()`.

7. The container then gets re-set with a new value for _Foo_ $bar, to simulate
the loading of multiple configs, some with overrides.

Unfortunately, because of the way the League container works internally, the
later setting in point 7 cannot override the earlier one in point 4, so the
`output()` fails:

```
PDO
bar-wrong
baz-right
```

Specifically, it's because the `DefinitionAggregate::getDefinition()` loop stops
after finding the first matching key; the override value comes later, so it is
never encountered:

```php
public function getDefinition(string $id): DefinitionInterface
{
    foreach ($this->getIterator() as $definition) {
        if ($id === $definition->getAlias()) {
            return $definition->setContainer($this->getContainer());
        }
    }

    // ...
}
```

### PHP-DI

You can find the PHP-DI code at <https://github.com/PHP-DI/PHP-DI>. The example
code for the scenario is [here](./php-di.php).

PHP-DI **does** complete the scenario, but with a little extra effort.

1. The container itself needs to be told to use autowiring, and not to use annotations.

2. The _PDO_ arguments are lazy-loaded directly from the environment.

3. The _Foo_ $bar argument is specified as lazy-resolvable, because there is no
way to override an individual constructor argument. As a workaround, the value
is lazy-resolved out of the container, in hopes that it can be defined and then
re-defined later.

4. The _Foo_ $baz argument is specified as a literal string.

5. A `Foo:bar` entry is added with the initial _Foo_ $bar value.

6. The container then gets re-set with a new value for _Foo_ $bar, to simulate
the loading of multiple configs, some with overrides.

The `output()` is correct:

```
PDO
bar-right
baz-right
```

## Summary

Thus, the final tally of which container systems completed the scenario:

- Capsule: yes
- Auryn: no
- League: no
- Illuminate: yes, with workarounds
- PHP-DI: yes, with workarounds

Does this mean the containers that could not complete the scenario are
somehow "bad" or "wrong"? No -- but it does mean I was wrong to think that the
features highlighted by the scenario are commonplace. Different scenarios might
show off these containers in a better or worse light.

Regardless, this exercise does show some of the differences between the example
container systems using a practical example, which was the only point of doing
the comparison in the first place.

## Appendix: Running The Scenario

You can run the comparison code for yourself.

First, install the packages being compared ...

```sh
cd psr-11-v1; composer install
cd psr-11-v2; composer install
```

... then run the example code of your choice:

```sh
php capsule.php
php php-di.php
# etc
```
