<div align="center">
  <a href="https://github.com/webpack/webpack">
    <img width="200" height="200" src="https://webpack.js.org/assets/icon-square-big.svg">
  </a>
</div>

# webpack-blade-native-loader

A webpack loader for blade template engine using [Laravel's Illuminate/View](https://github.com/illuminate/view) 
component itself.

## How does it work?

This loader passes on the contents of your blade views to a PHP script. The PHP script sets up a ViewFactory to render
the blade code to HTML. This means that your views are rendered entirely using PHP. The resulting HTML is passed back
into the Webpack flow.

## Requirements

As we're using PHP behind the scenes, you should have PHP available on your system.
Please see [the Laravel Docs](https://laravel.com/docs/7.x/installation#server-requirements) for the minimal supported
version.

Upon installing the loader, Illuminate/View is being pulled in using composer. To install composer on your system,
please see [getcomposer.org](https://getcomposer.org/).

## Getting Started

To begin, you'll need to install `webpack-blade-native-loader`:

```console
$ npm install webpack-blade-native-loader --save-dev
```

### Basic setup

**webpack.config.js**

```js
module.exports = {
  module: {
    rules: [
      {
        test: /\.blade\.php$/,
        use: [
          {
            loader: 'webpack-blade-native-loader',
            options: {
              viewDir: 'resources/views'
            }
          }
        ],
      },
    ],
  },
};
```

### HtmlWebpackPlugin

This loader works great combined with [HtmlWebpackPlugin](https://webpack.js.org/plugins/html-webpack-plugin/). Here's
an example how to set that up.

**webpack.config.js**

```js
const pages = glob.sync('**/*.blade.php', {
  cwd: path.join(process.cwd(), `resources/views/`),
  root: '/',
})
.map((page) => new HtmlWebpackPlugin({
  filename: page.replace('.blade.php', '.html'),
  template: `resources/views/${page}`,
}));

module.exports.plugins.push(...pages);
```

This  will render all of the blade files in the resources/views directory to HTML. If you're using partials,
this might not be what you want. The updated example below filters out any views that contain an underscore in their 
paths. You can specify your own rules by modifying the filter statement or add more to suit your needs.

**webpack.config.js**

```js
const pages = glob.sync('**/*.blade.php', {
  cwd: path.join(process.cwd(), `resources/views/`),
  root: '/',
})

// Filter out views that contain an underscore in their paths
.filter(filename => filename.indexOf('_') === -1)

.map((page) => new HtmlWebpackPlugin({
  filename: page.replace('.blade.php', '.html'),
  template: `resources/views/${page}`,
}));

module.exports.plugins.push(...pages);
```

## Options

### `viewDir`

Type: `String`
Default: `''`

The path of the directory where template files are located.

## License

The MIT License (MIT). Please see 
[License File](LICENSE.md) for more information.

## Credits

This package is being maintained by [Esign](https://www.esign.eu). We're a creative digital agency located in 
Ghent, Belgium.
