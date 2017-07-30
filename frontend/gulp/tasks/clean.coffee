gulp = require('gulp')
path = require('path')
del = require('del')
config = require('../config')

gulp.task 'clean', (done) ->
  del [
    config.coffeescript.dest
    config.sass.dest
    path.join(config.app.dest, 'vendor')
    path.join(config.app.dest, 'index.html')
  ], force: yes, done
