/**
 * Created by Bart Decorte
 * Date: 28/03/2020
 * Time: 12:53
 */
const execBuffer = require('exec-buffer');
const execBuffer2 = require('exec-buffer');
const loaderUtils = require('loader-utils');
const validateOptions = require('schema-utils');

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

const getDependencies = (source) => {
  const args = [`${moduleDir}dependencies.php`];
  args.push('--source', execBuffer2.input);
  args.push('--out', execBuffer2.output);
  const buffer = Buffer.from(source, 'utf8');

  return execBuffer2({
    args,
    bin: 'php',
    input: buffer,
  }).catch(error => {
    error.message = error.stderr || error.message;
    return '[]';
  }).then(json => {
    return JSON.parse(json);
  });
};

const addDependencies = (dependencyList, options, context) => {
  dependencyList.map(dependency => `${options.viewDir}/${dependency}.blade.php`)
    .forEach((dependency) => { context.addDependency(dependency); console.log(dependency); });

  return true;
};

module.exports = function (source) {
  const options = loaderUtils.getOptions(this) || {};
  validateOptions(schema, options, configuration);
  const args = [`${moduleDir}index.php`];
  args.push('--view-dir', options.viewDir);
  args.push('--source', execBuffer.input);
  args.push('--out', execBuffer.output);
  const buffer = Buffer.from(source, 'utf8');

  // todo add dependencies recursive

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
