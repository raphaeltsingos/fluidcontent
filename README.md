<img src="https://fluidtypo3.org/logo.svgz" width="100%" />

Fluidcontent: Fluid Content Elements
====================================

OBSOLETE!
---------

#### Issues and pull requests have been disabled. The extension is no longer supported or maintained.

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
