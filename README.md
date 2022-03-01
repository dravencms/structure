# Dravencms Structure module

This is a Structure module for dravencms

## Instalation

The best way to install dravencms/structure is using  [Composer](http://getcomposer.org/):


```sh
$ composer require dravencms/structure
```

Then you have to register extension in `config.neon`.

```yaml
extensions:
	structure: Dravencms\Structure\DI\StructureExtension
```
