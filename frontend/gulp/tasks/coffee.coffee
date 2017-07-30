gulp = require('gulp')
config = require('../config')
$ = require('gulp-load-plugins')()
path = require('path')

swallowError = (error) -> console.log(error.toString()); this.emit('end')

gulp.task 'coffee', ->
  configFilter = $.filter('**/*.json', restore: on)
  gulp.src(config.coffeescript.src.concat([path.join(config.app.src, '*.json')]))
  .pipe $.changed(config.coffeescript.dest, extension: '.js')
  .pipe configFilter
  .pipe $.ngConfig 'supla-scripts',
    createModule: no
    constants:
      ANGULAR_DEBUG_DATA_ENABLED: !config.production
      APP_VERSION: require('../../package.json').version
  .pipe configFilter.restore
  .pipe $.coffee(bare: yes)
  .on('error', swallowError)
  .pipe $.ngAnnotate()
  .pipe $.if(config.production, $.angularFilesort())
  .pipe $.if(config.production, $.concat('app.min.js'))
  .pipe $.if(config.production, $.uglify())
  .pipe $.if(config.production, $.rev())
  .pipe gulp.dest(config.coffeescript.dest)

gulp.task 'lint', ->
  gulp.src(config.coffeescript.src)
  .pipe($.coffeelint('gulp/coffeelint.json'))
  .pipe($.coffeelint.reporter())
  .pipe($.coffeelint.reporter('fail'))
  .on('error', -> 1)
