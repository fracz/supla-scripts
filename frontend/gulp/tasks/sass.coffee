gulp = require('gulp')
sass = require('gulp-sass')
config = require('../config')
$ = require('gulp-load-plugins')()

swallowError = (error) -> console.log(error.toString()); this.emit('end')

gulp.task 'sass', ->
  pipe = gulp.src(config.sass.src)
  .pipe $.changed(config.sass.dest, extension: '.css')
  .pipe($.sass())
  .on('error', swallowError)
  .pipe($.if(config.production, $.concatCss('styles.css')))
  .pipe($.if(config.production, $.cleanCss()))
  .pipe $.if(config.production, $.rev())
  .pipe(gulp.dest(config.sass.dest))
  if config.browserSync
    pipe.pipe(config.browserSync.stream())
  pipe
