const path = require('path');

module.exports = {
  entry: {
    bundle: './src/index.jsx',
  },
  externals: {
    react: 'React',
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader',
            options: {
              compact: false,
              presets: [
                ['@babel/preset-env', {
                  modules: false,
                  targets: '> 5%',
                }],
                '@babel/preset-react',
              ],
              cacheDirectory: false,
            },
          },
        ],
      },
    ],
  },
  resolve: {
    extensions: ['.js', '.jsx'],
  },
  output: {
    filename: 'ffw-divi5-display-faqs.js',
    path: path.resolve(__dirname, 'build'),
  },
};
