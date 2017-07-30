path = require('path')
src = 'app'
dest = '../public'

module.exports =
  production: no
  browserSync: undefined
  app:
    src: src
    dest: dest
  coffeescript:
    src: [path.join(src, '**/*.coffee')]
    dest: path.join(dest, 'app')
  html:
    src: path.join(src, '*/**/*.html')
    dest: path.join(dest, 'app')
  sass:
    src: path.join(src, '**/*.{sass,scss}')
    dest: path.join(dest, 'styles')
