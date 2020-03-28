/**
 * Created by Bart Decorte
 * Date: 28/03/2020
 * Time: 12:53
 */
const execBuffer = require('exec-buffer');
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

module.exports = function (source) {
  const options = loaderUtils.getOptions(this) || {};
  validateOptions(schema, options, configuration);
  const args = ['node_modules/webpack-blade-native-loader/index.php'];
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
  });
};
