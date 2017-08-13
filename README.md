<img src="https://fluidtypo3.org/logo.svgz" width="100%" />

Fluidcontent: Fluid Content Elements
====================================

[![Build Status](https://img.shields.io/travis/FluidTYPO3/fluidcontent.svg?style=flat-square&label=package)](https://travis-ci.org/FluidTYPO3/fluidcontent) [![Coverage Status](https://img.shields.io/coveralls/FluidTYPO3/fluidcontent/development.svg?style=flat-square)](https://coveralls.io/r/FluidTYPO3/fluidcontent) [![Build status](http://img.shields.io/badge/documentation-online-blue.svg?style=flat-square)](https://fluidtypo3.org/documentation/templating-manual/introduction.html) [![Build Status](https://img.shields.io/travis/FluidTYPO3/fluidtypo3-testing.svg?style=flat-square&label=framework)](https://travis-ci.org/FluidTYPO3/fluidtypo3-testing) [![Coverage Status](https://img.shields.io/coveralls/FluidTYPO3/fluidtypo3-testing/master.svg?style=flat-square)](https://coveralls.io/r/FluidTYPO3/fluidtypo3-testing)


## OBSOLETE!

*This extension is considered obsolete since Flux (the engine used by this extension) now provides a similar but better method
of using templates as content element types. The most recent release of this extension, version 6.0, should only be used if you
must upgrade an existing site and must avoid migrating templates (at the current time of writing this, automatic migration is not
possible).*

For new projects you can simply skip installing `fluidcontent` and use the exact same `registerProviderExtensionKey` function
to register your templates. Flux detects that `fluidcontent` isn't installed and takes over.

Please note the following key differences between `fluidcontent` and `flux` in how they register templates:

* `fluidcontent` uses a wrapping `fluidcontent_content` CType which behaves like plugins in that it has a sub-type where you
  select the Fluid template to be used as content template.
* `flux` directly registers your template as a new, unique CType which behaves like a true content type (but still allows you
  to use a ContentController)
* Without `fluidcontent` installed, access restrictions for allowed content types are now configured directly from the standard
  content types list. And subsequently you need to target the individual CTypes when configuring `allowedContentTypes`
  or `deniedContentTypes` in Flux grid columns and when customising the TCA/TS for your content types.
  
The parts that cannot be migrated (yet) are exactly those parts: converting the existing content records' type values and scanning
for possible references to the converted content types (and either reporting or fixing by rewriting those cases). Apart from that
the solutions are compatible in that they consume the exact same type of templates with the same configuration.

## What does it do?

EXT:fluidcontent lets you write custom content elements based on Fluid templates. Each content element and its possible settings
are contained in a single Fluid template file. Whole sets of files can be registered and placed in its own tab in the new content
element wizard, letting you group your content elements. The template files are placed in a very basic extension. The _Nested
Content Elements_ support that Flux enables is utilized to make content elements which can contain other content elements.

## Why use it?

**Fluid Content** is a fast, dynamic and extremely flexible way to create content elements. Not only can you use Fluid, you can
also create dynamic configuration options for each content element using Flux - in the exact same way as done in the Fluid Pages
extension; see https://github.com/FluidTYPO3/fluidpages.

## How does it work?

Fluid Content Elements are registered through TypoScript. The template files are then processed to read various information about
each template, which is then made available for use just as any other content element type is used.

When editing the content element, Flux is used to generate the form section which lets content editors configure variables which
become available in the template. This allows completely dynamic variables (as opposed to adding extra fields on the `tt_content`
table and configuring TCA for each added column).

View the [online templating manual](https://fluidtypo3.org/documentation/templating-manual/introduction.html) for more information.
