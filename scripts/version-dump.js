var fs = require('fs');
var version = require('../package.json').version;

var versionDumpPath = 'backend/version';
fs.writeFileSync(versionDumpPath, version);
