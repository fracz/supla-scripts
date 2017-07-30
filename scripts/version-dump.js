var fs = require('fs');
var version = require('../package.json').version;

var versionDumpPath = 'backend/var/system/version';
fs.writeFileSync(versionDumpPath, version);
