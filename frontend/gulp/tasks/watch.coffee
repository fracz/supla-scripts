gulp = require('gulp')
browserSync = require('browser-sync').create()
config = require('../config')
path = require('path')
$ = require('gulp-load-plugins')()

gulp.task 'watch', ['browser-sync'], ->
  gulp.watch(config.coffeescript.src, ['coffee'])
  gulp.watch(config.sass.src, ['sass'])
  gulp.watch(config.html.src, ['html'])
  gulp.watch(path.join(config.app.src, 'index.html'), ['wiredep'])
  gulp.watch(path.join(config.app.dest, 'index.html')).on('change', -> injectBrowserSyncSnippet() && browserSync.reload())
  gulp.watch([path.join(config.coffeescript.dest, '**/*.js'), path.join(config.html.dest, '**/*.html')], interval: 250).on('change', browserSync.reload);

injectBrowserSyncSnippet = ->
  snippet = browserSync.getOption('snippet')
  gulp.src(path.join(config.app.dest, 'index.html'))
    .pipe($.replace('</body>', snippet + '</body>'))
    .pipe(gulp.dest(config.app.dest))

gulp.task 'browser-sync', ['build'], (done) ->
  browserSync.init
    reloadOnRestart: true,
    reloadDelay: 300,
    online: false,
    logSnippet: false
  ,
    ->
      config.browserSync = browserSync
      injectBrowserSyncSnippet()
      done()
