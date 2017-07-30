var version = require('../package.json').version;
var chalk = require('chalk');
var fs = require('fs');
var path = require('path');

var ASCII_LOGO_WIDTH = 37;
var LOGO = fs.readFileSync(path.join(__dirname, 'logo.txt')).toString().replace(/\s*$/, '');

var printAsciiLogoAndVersion = function () {
  var versionWithV = 'v' + version;
  var versionLine = Array(ASCII_LOGO_WIDTH - versionWithV.length).join(' ') + versionWithV;
  console.log(chalk.cyan(LOGO));
  console.log(chalk.green(versionLine));
};

module.exports = {
  printAsciiLogoAndVersion: printAsciiLogoAndVersion
};

var runningAsScript = require.main === module;
if (runningAsScript) {
  printAsciiLogoAndVersion();
}
