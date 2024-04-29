gulp = require('gulp')
sass = require('gulp-dart-sass')
config = require('../config')
$ = require('gulp-load-plugins')()

gulp.task 'sass', ->
  pipe = gulp.src(config.sass.src)
  .pipe $.changed(config.sass.dest, extension: '.css')
  .pipe(sass().on('error', sass.logError))
  .pipe($.if(config.production, $.concatCss('styles.css')))
  .pipe($.if(config.production, $.cleanCss()))
  .pipe $.if(config.production, $.rev())
  .pipe(gulp.dest(config.sass.dest))
  if config.browserSync
    pipe.pipe(config.browserSync.stream())
  pipe
