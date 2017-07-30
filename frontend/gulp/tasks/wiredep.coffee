gulp = require 'gulp'
config = require '../config'
$ = require('gulp-load-plugins')()
path = require('path')
bowerFiles = require("main-bower-files")

gulp.task 'wiredep', ['bowerdeps'], ->
  gulp.src(path.join(config.app.src, 'index.html'))
    .pipe $.inject(gulp.src(path.join(config.coffeescript.dest, '**/*.js')).pipe($.angularFilesort()), ignorePath: '../public')
    .pipe $.inject(gulp.src(path.join(config.sass.dest, "**")), ignorePath: '../public')
    .pipe $.if(!config.production,
    $.inject(gulp.src(bowerFiles(), read: false), name: 'vendor', ignorePath: '/bower_components', addPrefix: '/vendor'))
    .pipe $.if(config.production, $.inject(gulp.src(path.join(config.app.dest, 'vendor/**')), name: 'vendor', ignorePath: '../public'))
    .pipe $.if(config.production, $.htmlmin(collapseWhitespace: yes, removeComments: yes))
    .pipe gulp.dest(config.app.dest)

gulp.task 'bowerdeps', ->
  vendor = gulp.src(bowerFiles(), base: 'bower_components')
  if config.production
    jsFilter = $.filter('**/*.js', restore: yes)
    cssFilter = $.filter('**/*.css', restore: yes)
    vendor = vendor
      .pipe(jsFilter)
      .pipe($.concat('vendor.js'))
      .pipe($.rev())
      .pipe($.uglify())
      .pipe(jsFilter.restore)
      .pipe(cssFilter)
      .pipe($.concatCss('vendor.css'))
      .pipe($.cleanCss())
      .pipe($.rev())
      .pipe(cssFilter.restore)
  vendor.pipe(gulp.dest(path.join(config.app.dest, 'vendor')))
