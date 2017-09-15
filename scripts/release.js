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
        'docker/',
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
        'var/config',
        'var/mysql',
        'var/logs',
        'var/system'
    ].forEach(function (dirname) {
        fs.mkdirsSync('release/' + dirname);
    });
}

function copySingleRequiredFiles() {
    fs.copySync('var/config/config.sample.json', 'release/var/config/config.sample.json');
    fs.copySync('var/config/docker-config.env.sample', 'release/var/config/docker-config.env.sample');
    fs.copySync('var/ssl/generate-self-signed-certs.sh', 'release/var/ssl/generate-self-signed-certs.sh');
    fs.copySync('var/system/version', 'release/var/system/version');
    fs.copySync('supla-scripts', 'release/supla-scripts');
}

function clearLocalConfigFiles() {
    del.sync([
        'release/**/.gitignore',
        'release/backend/composer.*'
    ]);
}

function deleteUnwantedSources() {
    var spinner = ora({text: 'Deleting unneeded sources.', color: 'yellow'}).start();
    del([
        'release/backend/database/drop.php'
    ])
        .then(() => {
            spinner.succeed('Unneeded sources deleted.');
            createZipArchive();
        })
        .catch((err) => {
            console.log(err);
            spinner.fail();
        })
}

function createZipArchive() {
    var spinner = ora({text: 'Creating release archive.', color: 'yellow'}).start();
    exec('tar -czf ' + releasePackageName + ' release --transform=\'s/release\\/\\{0,1\\}//g\'', function (err) {
        if (err) {
            spinner.fail();
            console.log(err);
        } else {
            spinner.succeed('Release archive created.');
            console.log('');
            console.log("Package: " + chalk.green(releasePackageName));
        }
    });
}

start();
