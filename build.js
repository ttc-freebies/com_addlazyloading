const { copy, move, existsSync, mkdirSync, readFileSync, writeFileSync, createWriteStream } = require('fs-extra');
const {execSync} = require('child_process');
const {promisify} = require("es6-promisify");
const rimraf = require('rimraf')
const PromiseRimraf = promisify(rimraf);
const Archiver = require('archiver');
const package = require('./package.json');
const root = process.cwd();

PromiseRimraf('./package').then(() => {
    execSync('npx rollup --config');

    copy('./src/admin', './package/admin/')
.then(()=> {
    move('./package/admin/addlazyloading.xml', './package/addlazyloading.xml')
    .then(() => {
        if (!existsSync('./dist')) {
            mkdirSync('./dist')
        }

        let xml = readFileSync('./package/addlazyloading.xml', { encoding: 'utf8' });
        xml = xml.replace('{{version}}', package.version)
      
        writeFileSync('./package/addlazyloading.xml', xml, { encoding: 'utf8' });

        makePackage();
    })
});
});

const makePackage = () => {
  // Package it
  const output = createWriteStream(`${root}/dist/com_addlazyloading_${package.version}.zip`);
  let archive = Archiver('zip', {
    zlib: { level: 9 }
  });

  output.on('close', function () {
    console.log(`dist/com_addlazyloading_${package.version}.zip created: ${archive.pointer()} total bytes`);
  });

  output.on('end', function () {
    console.log('Data has been drained');
  });

  // good practice to catch warnings (ie stat failures and other non-blocking errors)
  archive.on('warning', function (err) {
    if (err.code === 'ENOENT') {
      // log warning
    } else {
      // throw error
      throw err;
    }
  });

  archive.on('error', function (err) {
    throw err;
  });

  archive.pipe(output);

  archive.directory('package/', false);

  archive.finalize();
};


