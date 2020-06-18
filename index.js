/**
 * Created by Bart Decorte
 * Date: 28/03/2020
 * Time: 12:53
 */
const loaderUtils = require('loader-utils');
const validateOptions = require('schema-utils');
const fs = require('fs');
const util = require('util');
const path = require('path');

const schema = {
  type: 'object',
  properties: {
    viewDir: {
      description:
      'The path of the directory where template files are located.',
      type: 'string'
    },
  }
};

const configuration = { name: 'Webpack Blade Native Loader' };
const moduleDir = 'node_modules/webpack-blade-native-loader/';
let options = {};

const getDependencies = (source) => {
  const execBuffer = require('exec-buffer');
  const args = [path.normalize(`${moduleDir}dependencies.php`)];
  args.push('--view-dir', options.viewDir);
  args.push('--source', execBuffer.input);
  args.push('--out', execBuffer.output);
  const buffer = Buffer.from(source, 'utf8');

  return execBuffer({
    args,
    bin: 'php',
    input: buffer,
  }).catch(error => {
    error.message = error.stderr || error.message;
    throw error;
  }).then(json => {
    return JSON.parse(json);
  });
};

const getPathDependencies = (path, options, context) => {
  const func = util.promisify(fs.readFile);
  return func(path)
    .then((data) => getDependencies(data))
    .then((dependencies) => addDependencies(dependencies, options, context))
  ;
};

const addDependencies = (dependencyList, options, context) => {
  const promises = [];
  dependencyList.map(dependency => path.normalize(`${options.viewDir}/${dependency}.blade.php`))
    .forEach((dependency) => {
      context.addDependency(dependency);
      promises.push(getPathDependencies(dependency, options, context));
    });

  return Promise.all(promises);
};

module.exports = function (source) {
  const execBuffer = require('exec-buffer');
  options = loaderUtils.getOptions(this) || {};
  validateOptions(schema, options, configuration);
  const args = [`${moduleDir}index.php`];
  args.push('--view-dir', options.viewDir);
  args.push('--source', execBuffer.input);
  args.push('--out', execBuffer.output);
  const buffer = Buffer.from(source, 'utf8');

  return getDependencies(source)
    .then((dependencies) => addDependencies(dependencies, options, this))
    .then(() => {
      return execBuffer({
        args,
        bin: 'php',
        input: buffer,
      }).catch(error => {
        error.message = error.stderr || error.message;
        throw error;
      });
    });
};
