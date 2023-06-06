
# Changelog

## [1.1.2] - 2023-06-06

### Fixed

 - Call getPageLayout hook

## [1.1.1] - 2022-01-24

### Fixed

 - Make sure that the Contao framework is initialized

## [1.1.0] - 2022-01-20

### Changed

 - Bump dependencies to Contao `^4.9` and Symfony `^4.4 || ^5.1`
 - Extract mobile layout handling in separat listener

## [1.0.2] - 2019-11-24

### Fixed

 - Pass `PageRegular` as third parameter to hook `getPageLayout` (See [#18])

## [1.0.1] - 2019-02-19

### Added

 - Handle case that doctype is empty (Contao 4.7 compatibility) 

[1.0.1]: https://github.com/netzmacht/contao-page-context/compare/1.0.1...1.0.2 
[1.0.1]: https://github.com/netzmacht/contao-page-context/compare/1.0.0...1.0.1

[#18]: https://github.com/netzmacht/contao-page-context/issues/18
