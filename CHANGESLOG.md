# Changelog
All notable changes to this project will be documented in this file.


## [2.0.1] - 2021-01-12
### Fixed
- Stoped tracking changes to cache files in the unit test folder, 
  so as to stop Github from labeling my project as hacked. 
  Thanks Github.


## [2.0.0] - 2020-12-30
### Fixed
- An error was preventing from overwriting cached items.

### Removed
- Cache::lock() 
- Cache::unlock() 
- File::$locked
- File::lock() 
- File::unlock()
- File::open()
- File::close()


## [1.0.2] - 2019-10-18
### Fixed
- Fixing errors in the composer.json file and contact information.
- Tidying up the documentation.


## [1.0.1] - 2019-03-24
### Changed
- Comments and README.md tidied up to fix documentation errors
