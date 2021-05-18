const path = require('path');
require('dotenv').config();
const webpack = require('webpack');

module.exports = (env, argv) => {
  /**
   * Check if we are in development mode.
   *
   * @return {boolean}
   *   True if in dev mode.
   */
  function isDevelopment() {
    return argv.mode === 'development';
  }

  var config = {
    mode: 'development', //TODO: configure this for dev & prod mode.
    entry: './src/index.ts', // The source file that we will compile.
    devtool: isDevelopment() && 'eval-source-map', // Enable source map in dev environment. Also needs to be configured in tsconfig.json
    module: {
      rules: [
        {
          test: /\.ts$/,
          use: 'ts-loader', // TypeScript compiler.
          include: [path.resolve(__dirname, 'src')],
          exclude: /node_modules/,
        },
        {
          test: /\.s[ac]ss$/i,
          use: [
            // Creates `style` nodes from JS strings.
            'style-loader',
            // Translates CSS into CommonJS.
            'css-loader',
            // Compiles Sass to CSS.
            'sass-loader',
          ],
        },
      ],
    },
    resolve: {
      extensions: ['.ts', '.js'], // Necessary so that we can use ES6 import statements.
    },
    plugins: [
      new webpack.DefinePlugin({
        // Define the api host url for development and production.
        'process.env.API_HOST': isDevelopment()
          ? JSON.stringify(process.env.API_URL_DEV)
          : JSON.stringify(process.env.API_URL_PROD),
      }),
    ],
    output: {
      publicPath: '/dist',
      filename: 'feedbeggar-widget.js',
      path: path.resolve(__dirname, 'dist'), // Output the compiled file to the dist directory.
    },
  };
  return config;
};
