gulp = require('gulp')
runSequence = require('run-sequence')
config = require('../config')
chalk = require('chalk')

gulp.task 'build', (done) ->
  runSequence(
    'clean'
    ['sass', 'coffee', 'html']
    'wiredep'
    done
  )
