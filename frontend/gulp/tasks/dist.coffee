gulp = require('gulp')
config = require('../config')
runSequence = require('run-sequence')

gulp.task 'dist', (done) ->
  config.production = true
  runSequence('clean', 'build', done)
