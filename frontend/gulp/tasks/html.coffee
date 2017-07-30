gulp = require 'gulp'
config = require '../config'
$ = require('gulp-load-plugins')()

swallowError = (error) -> console.log(error.toString()); this.emit('end')

gulp.task 'html', ->
  gulp.src(config.html.src)
  .pipe $.changed(config.html.dest)
  .pipe $.htmlmin(collapseWhitespace: yes, removeComments: yes)
  .on('error', swallowError)
  .pipe $.if(config.production, $.angularTemplatecache(module: 'supla-scripts', root: 'app'), $.changed(config.html.dest))
  .pipe $.if(config.production, $.rev())
  .pipe gulp.dest(config.html.dest)
