var project = require("./logo");
var version = require('../package.json').version;
var chalk = require('chalk');
const ora = require('ora');
var fs = require('fs-extra');
var async = require('async');
var del = require('del');
var exec = require('child_process').exec;

var releasePackageName = 'supla-scripts-' + version + '.tar.gz';

project.printAsciiLogoAndVersion();

console.log('');
console.log("Preparing release package.");
console.log('');

function start() {
    clearVendorDirectory();
}

function clearVendorDirectory() {
    var spinner = ora({text: 'Cleaning vendor directory.', color: 'yellow'}).start();
    del('backend/vendor/**/.git')
        .then(() => {
            spinner.succeed('Vendor directory cleaned.');
            clearReleaseDirectory();
        })
        .catch((err) => {
            console.log(err);
            spinner.fail();
        });
}

function clearReleaseDirectory() {
    var spinner = ora({text: 'Deleting release directory.', color: 'yellow'}).start();
    fs.remove('release/', function (err) {
        if (err) {
            spinner.fail();
            console.error(err);
        } else {
            spinner.succeed('Release directory deleted.');
            copyToReleaseDirectory();
        }
    });
}

function copyToReleaseDirectory() {
    var spinner = ora({text: 'Copying application files.', color: 'yellow'}).start();
    var calls = [];
    [
        'backend/',
        'public',
    ].forEach(function (filename) {
        calls.push(function (callback) {
            fs.mkdirsSync('release/' + filename);
            fs.copy(filename, 'release/' + filename, function (err) {
                if (!err) {
                    callback(err);
                } else {
                    callback(null, filename);
                }
            });
        });
    });
    async.series(calls, function (err) {
        if (err) {
            spinner.fail();
            console.error(err);
        } else {
            createRequiredDirectories();
            copySingleRequiredFiles();
            clearLocalConfigFiles();
            spinner.succeed('Application files copied.');
            deleteUnwantedSources();
        }
    });
}

function createRequiredDirectories() {
    [
        'var/backups',
        'var/cache',
        'var/config',
        'var/mysql',
        'var/logs',
        'var/system',
    ].forEach(function (dirname) {
        fs.mkdirsSync('release/' + dirname);
    });
}

function copySingleRequiredFiles() {
    fs.copySync('var/config/config.sample.json', 'release/var/config/config.sample.json');
    fs.copySync('var/config/crontab', 'release/var/config/crontab');
    fs.copySync('var/config/install-crontab.sh', 'release/var/config/install-crontab.sh');
    fs.copySync('var/config/supla-scripts.vhost.sample.conf', 'release/var/config/supla-scripts.vhost.sample.conf');
    fs.copySync('var/config/docker-config.env.sample', 'release/var/config/docker-config.env.sample');
    fs.copySync('var/ssl/generate-self-signed-certs.sh', 'release/var/ssl/generate-self-signed-certs.sh');
    fs.copySync('scripts/logo.txt', 'release/backend/logo.txt');
    fs.copySync('supla-scripts', 'release/supla-scripts');
    fs.copySync('README.md', 'release/README.md');
}

function clearLocalConfigFiles() {
    del.sync([
        'release/**/.gitignore',
        'release/backend/composer.*',
    ]);
}

function deleteUnwantedSources() {
    var spinner = ora({text: 'Deleting unneeded sources.', color: 'yellow'}).start();
    del([
        'release/backend/database/drop.php',
        'release/backend/vendor/**/test/**',
        'release/backend/vendor/**/tests/**',
        'release/backend/vendor/**/doc/**',
        'release/backend/vendor/**/docs/**',
        'release/backend/vendor/**/.idea/**',
        'release/backend/vendor/**/img/**',
        'release/backend/vendor/**/composer.json',
        'release/backend/vendor/**/composer.lock',
        'release/backend/vendor/**/*.md',
        'release/backend/vendor/**/LICENSE',
        'release/backend/vendor/**/*.dist',
        'release/public/v1.0',
    ])
        .then(() => {
            spinner.succeed('Unneeded sources deleted.');
            createTarArchive();
        })
        .catch((err) => {
            console.log(err);
            spinner.fail();
        })
}

function createTarArchive() {
    var spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
    exec('tar -czf ' + releasePackageName + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
        if (err) {
            spinner.fail();
            console.log(err);
        } else {
            spinner.succeed('Release archive created.');
            console.log('');
            console.log("Package: " + chalk.green(releasePackageName));
            var fileSizeInBytes = fs.statSync(releasePackageName).size;
            console.log('Size: ' + Math.round(fileSizeInBytes / 1024) + 'kB (' + Math.round(fileSizeInBytes * 10 / 1024 / 1024) / 10 + 'MB)');
        }
    });
}

start();
