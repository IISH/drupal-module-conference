; The intranet build
includes[global] = "global.make"

; Append or override any modules here.
projects[iish_blocks][version] = "1.0"
projects[iish_blocks][type] = "module"
projects[iish_blocks][download][type] = "get"
projects[iish_blocks][download][url] = "https://github.com/IISH/drupal-module-conference/archive/master.tar.gz"
projects[iish_blocks][subdir] = "custom"