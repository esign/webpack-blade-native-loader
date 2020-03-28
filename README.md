<div align="center">
  <a href="https://github.com/webpack/webpack">
    <img width="200" height="200" src="https://webpack.js.org/assets/icon-square-big.svg">
  </a>
</div>

# webpack-blade-loader

A webpack loader for blade template engine using illuminate/view itself.

## Getting Started

To begin, you'll need to install `webpack-blade-native-loader`:

```console
$ npm install webpack-blade-native-loader --save-dev
```

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

And run `webpack` via your preferred method.

## Options

### `viewDir`

Type: `String`
Default: `''`

The path of the directory where template files are located.
